<?php

namespace App\Http\Controllers;

use App\Helpers\BotFinder;
use App\Helpers\BotRecorder;
use App\Helpers\BotResolver;
use App\Models\Message;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
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
                // report to BugSnag
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
