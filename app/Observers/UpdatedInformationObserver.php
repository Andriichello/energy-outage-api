<?php

namespace App\Observers;

use App\Helpers\BotHelper;
use App\Helpers\DiffHelper;
use App\Helpers\GroupHelper;
use App\Helpers\MessageComposer;
use App\Models\Chat;
use App\Models\UpdatedInformation;
use App\Models\User;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramResponseException;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Message as TelegramMessage;

class UpdatedInformationObserver
{
    /**
     * Telegram Bot API instance to respond via.
     *
     * @var Api
     */
    protected Api $api;

    /**
     * Handle the UpdatedInformation "created" event.
     */
    public function created(UpdatedInformation $info): void
    {
        Log::info('created: ', ['info' => $info->toArray()]);

        if (empty($info->content)) {
            return;
        }

        // Find the previous record to compare with
        $previous = $info->previous();

        Log::info('previous: ', ['previous' => $previous?->toArray()]);

        if (!$previous || $info->differs($previous)) {
            // Log that information has been changed
            Log::info('Information changed for provider', [
                'provider' => $info->provider,
                'fetched_at' => $info->fetched_at,
                'content_hash' => $info->content_hash,
            ]);

            // Compose a message with added paragraphs
            $message = MessageComposer::added($info, $previous);
            $content = MessageComposer::unescape($message['text']);

            // Log that information has been changed
            Log::info('Message', $message);

            // Notify users that are subscribed to this provider
            User::query()
                ->whereNotNull('users.unique_id')
                ->join('chats', 'users.unique_id', '=', 'chats.user_id')
                ->orderByDesc('chats.created_at')
                ->select('users.*')
                ->each(function (User $user) use ($info, $previous, $message, $content) {
                    /** @var Chat|null $chat */
                    $chat = $user->chats()->latest()->first();

                    if ($chat) {
                        $this->send([
                            ...$message,
                            'chat_id' => $chat->unique_id,
                            // Disable notification (silent) if user has interested groups
                            // but none of them are mentioned in the new paragraphs
                            'disable_notification' => !empty($user->interested_groups) &&
                                !GroupHelper::containsInterestedGroups($content, $user->interested_groups)
                        ]);
                    }
                });
        }
    }

    /**
     * Resolve Telegram Bot API instance to respond via.
     *
     * @return Api
     * @throws TelegramSDKException
     */
    protected function api(): Api
    {
        if (isset($this->api)) {
            return $this->api;
        }

        return $this->api = Telegram::bot(array_key_first(BotHelper::botConfigs()));
    }

    /**
     * Send the given message via Telegram Bot API.
     *
     * @param array $message
     *
     * @return TelegramMessage|null
     * @throws TelegramSDKException
     */
    protected function send(array $message): ?TelegramMessage
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
}
