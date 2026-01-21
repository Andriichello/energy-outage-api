<?php

namespace App\Helpers;

use App\Models\Chat;
use App\Models\Message;
use App\Models\Update;
use App\Models\User;

/**
 * Class BotRecorder.
 */
class BotRecorder
{
    /**
     * Records update object to keep the history.
     *
     * @param \Telegram\Bot\Objects\Update $upd
     * @param string|null $status
     * @param User|null $from
     * @param Chat|null $chat
     * @param Message|null $message
     *
     * @return User|null
     */
    public static function update(
        \Telegram\Bot\Objects\Update $upd,
        ?string $status = null,
        ?User $from = null,
        ?Chat $chat = null,
        ?Message $message = null,
    ): ?Update {
        $msg = $upd->getMessage();

        if (!($msg instanceof \Telegram\Bot\Objects\Message)) {
            $msg = null;
        }

        $update = Update::query()
            ->where('unique_id', $upd->updateId)
            ->firstOrNew();

        $update->unique_id = $upd->updateId;
        $update->user_id = $from?->unique_id ?? $msg?->from?->id;
        $update->chat_id = $chat?->unique_id ?? $msg?->chat?->id;
        $update->message_id = $message?->unique_id ?? $msg?->messageId;
        $update->type = $msg?->objectType();
        $update->status = $status;
        $update->metadata = (object) $upd->toArray();

        $update->save();

        BotResolver::user($msg);

        return $update;
    }
}
