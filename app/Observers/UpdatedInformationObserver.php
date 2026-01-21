<?php

namespace App\Observers;

use App\Helpers\BotHelper;
use App\Helpers\MessageComposer;
use App\Models\Chat;
use App\Models\UpdatedInformation;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
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
        // Find the previous record to compare with
        $previous = $info->previous();

        if (!$previous || $info->differs($previous)) {
            // Log that information has been changed
            Log::info('Information changed for provider', [
                'provider' => $info->provider,
                'fetched_at' => $info->fetched_at,
                'content_hash' => $info->content_hash,
            ]);

            // Compose a message with content differences highlighted
            $message = MessageComposer::changed($info, $previous);

            // Notify users that are subscribed to this provider
            User::query()
                ->whereNotNull('unique_id')
                ->join('chats', 'users.id', '=', 'chats.user_id')
                ->orderByDesc('chats.created_at')
                ->select('users.*')
                ->each(function (User $user) use ($info, $previous, $message) {
                    /** @var Chat|null $chat */
                    $chat = $user->chats()->latest()->first();

                    if ($chat) {
                        $this->send([...$message, 'chat_id' => $chat->unique_id]);
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
     * @return TelegramMessage
     * @throws TelegramSDKException
     */
    protected function send(array $message): TelegramMessage
    {
        return $this->api()->sendMessage($message);
    }
}
