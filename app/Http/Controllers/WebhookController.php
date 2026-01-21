<?php

namespace App\Http\Controllers;

use App\Helpers\BotFinder;
use App\Helpers\BotRecorder;
use App\Helpers\BotResolver;
use App\Models\Message;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\ConsoleOutput;
use Telegram\Bot\Exceptions\TelegramSDKException;
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

        Log::debug('Webhook from Telegram', ['update' => $upd->toArray()]);

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
     * Perform actions after processing the webhook update.
     *
     * @param Message $message
     *
     * @return void
     * @throws TelegramSDKException
     */
    protected function after(Message $message): void
    {
        // Respond to a message from user...
    }
}
