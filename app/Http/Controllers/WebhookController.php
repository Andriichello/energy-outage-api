<?php

namespace App\Http\Controllers;

use App\Helpers\BotFinder;
use App\Helpers\BotHelper;
use App\Helpers\BotRecorder;
use App\Helpers\BotResolver;
use App\Helpers\GroupHelper;
use App\Helpers\MessageComposer;
use App\Models\Chat;
use App\Models\Message;
use App\Models\UpdatedInformation;
use App\Models\User;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\ConsoleOutput;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramResponseException;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\CallbackQuery;
use Throwable;

/**
 * Class WebhookController.
 */
class WebhookController
{
    /**
     * Receives and handles Telegram Bot webhooks.
     *
     * @param string $token
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function __invoke(string $token): JsonResponse
    {
        $bot = BotFinder::byTokenOrFail($token);

        $upd = $bot->getWebhookUpdate();
        $msg = $upd->getMessage();
        $callbackQuery = $upd->getCallbackQuery();

        Log::debug('Webhook from Telegram', ['update' => $upd->toArray()]);

        // Handle callback queries (button clicks)
        if ($callbackQuery) {
            try {
                $this->handleCallback($callbackQuery);
            } catch (Throwable $e) {
                (new ConsoleOutput())->writeln('Callback query failed: ' . $e->getMessage());
                (new ConsoleOutput())->writeln('Failure trace: ' . $e->getTraceAsString());

                Bugsnag::leaveBreadcrumb('Callback query from Telegram', 'manual', [
                    'callback_query' => $callbackQuery->toArray(),
                ]);
                Bugsnag::notifyException($e);
            }

            return response()->json(['message' => 'OK']);
        }

        // Handle regular messages
        if ($msg instanceof \Telegram\Bot\Objects\Message) {
            try {
                $from = BotResolver::user($msg);
                $chat = BotResolver::chat($msg, $from);
                $message = BotResolver::message($msg, $chat);

                if (!$message) {
                    throw new Exception('Failed to process message.');
                }

                $this->after($message);
            } catch (Throwable $e) {
                (new ConsoleOutput())->writeln('Webhook from Telegram failed: ' . $e->getMessage());
                (new ConsoleOutput())->writeln('Failure trace: ' . $e->getTraceAsString());

                $context = [
                    'from' => ($from ?? null)?->toArray(),
                    'chat' => ($chat ?? null)?->toArray(),
                    'message' => ($message ?? null)?->toArray(),
                ];

                Bugsnag::leaveBreadcrumb('Webhook from Telegram', 'manual', $context);
                Bugsnag::notifyException($e);

                BotRecorder::update(
                    $upd,
                    'failed',
                    $user ?? null,
                    $chat ?? null,
                    $message ?? null,
                );
            }

            return response()->json(['message' => 'OK']);
        }

        BotRecorder::update($upd, 'skipped');

        return response()->json(['message' => 'OK']);
    }

    /**
     * Resolve Telegram Bot API instance to respond via.
     *
     * @return Api
     * @throws TelegramSDKException
     */
    protected function api(): Api
    {
        return Telegram::bot(array_key_first(BotHelper::botConfigs()));
    }

    /**
     * Send the given message via Telegram Bot API.
     *
     * @param array $message
     *
     * @return \Telegram\Bot\Objects\Message|null
     * @throws TelegramSDKException
     */
    protected function send(array $message): ?\Telegram\Bot\Objects\Message
    {
        try {
            return $this->api()->sendMessage($message);
        } catch (TelegramResponseException $e) {
            Bugsnag::leaveBreadcrumb('Sending Telegram message', 'manual', $message);
            Bugsnag::notifyException($e);

            if ($e->getMessage() === 'Forbidden: bot was blocked by the user') {
                Chat::query()
                    ->where('unique_id', $message['chat_id'])
                    ->delete();
            }
        }

        return null;
    }

    /**
     * Handle callback queries from inline keyboard buttons.
     *
     * @param CallbackQuery $query
     *
     * @return void
     * @throws TelegramSDKException
     */
    protected function handleCallback(CallbackQuery $query): void
    {
        $data = $query->getData();
        $from = $query->getFrom();

        // Find the user
        $user = User::query()
            ->where('unique_id', $from->getId())
            ->first();

        if (!$user) {
            $this->api()->answerCallbackQuery([
                'callback_query_id' => $query->getId(),
                'text' => 'Користувача не знайдено',
            ]);
            return;
        }

        // Handle "toggle_group:X-Y" buttons
        if (str_starts_with($data, 'toggle_group:')) {
            $groupName = str_replace('toggle_group:', '', $data);

            // Validate the group name
            if (!in_array($groupName, GroupHelper::AVAILABLE_GROUPS)) {
                $this->api()->answerCallbackQuery([
                    'callback_query_id' => $query->getId(),
                    'text' => 'Невірна черга',
                ]);
                return;
            }

            // Toggle the group
            $groups = $user->interested_groups ?? [];
            if (in_array($groupName, $groups)) {
                $groups = array_diff($groups, [$groupName]);
            } else {
                $groups[] = $groupName;
            }

            $user->update(['interested_groups' => array_values($groups)]);

            // Update the keyboard to show the new state
            $this->api()->editMessageText([
                'chat_id' => $query->getMessage()->getChat()->getId(),
                'message_id' => $query->getMessage()->getMessageId(),
                ...MessageComposer::groupsMenuUpdate($user),
            ]);

            // Answer the callback to stop the loading spinner
            $this->api()->answerCallbackQuery([
                'callback_query_id' => $query->getId(),
                'text' => '✅ Оновлено!',
            ]);
        }
    }

    /**
     * Perform actions after processing the webhook update.
     *
     * @param Message $message
     *
     * @return void
     * @throws TelegramSDKException
     */
    protected function after(Message $message): void
    {
        // Respond to messages from users...
        if ($message->type === 'command') {
            if ($message->text === '/start') {
                $this->send(MessageComposer::welcome($message->chat));
            }

            if ($message->text === '/groups') {
                $user = User::query()
                    ->where('unique_id', $message->chat->user_id)
                    ->first();

                if ($user) {
                    $this->send(MessageComposer::groupsMenu($message->chat, $user));
                }
            }

            if ($message->text === '/latest') {
                $latest = UpdatedInformation::query()
                    ->where('provider', 'Zakarpattia')
                    ->latest()
                    ->first();

                $this->send(MessageComposer::added($latest, chat: $message->chat));
            }
        }

        // Handle reply keyboard button presses (as text messages)
        if ($message->type === 'text') {
            if ($message->text === '⚙️ Обрати чергу') {
                $user = User::query()
                    ->where('unique_id', $message->chat->user_id)
                    ->first();

                if ($user) {
                    $this->send(MessageComposer::groupsMenu($message->chat, $user));
                }
            }

            if ($message->text === '📋 Актуальна інформація') {
                $latest = UpdatedInformation::query()
                    ->where('provider', 'Zakarpattia')
                    ->latest()
                    ->first();

                // Send with previous = null to show full content
                $this->send(MessageComposer::added($latest, null, $message->chat));
            }
        }
    }
}
