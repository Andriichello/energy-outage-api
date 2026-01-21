<?php

namespace App\Helpers;

use App\Models\Chat;
use App\Models\File;
use App\Models\Message;
use App\Models\User;
use App\Repositories\FileRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

/**
 * Class BotResolver.
 */
class BotResolver
{
    /**
     * Resolves user from the given Telegram message.
     * Will create a new record if such user doesn't exist yet.
     * Will update an existing record if something differs
     * (`username`, `first_name`, `language_code`, `is_bot`, `is_premium`)
     *
     * @param \Telegram\Bot\Objects\Message $msg
     *
     * @return User|null
     */
    public static function user(\Telegram\Bot\Objects\Message $msg): ?User
    {
        if (!$msg->from) {
            return null;
        }

        $user = User::query()
            ->withTelegram()
            ->where('unique_id', $msg->from->id)
            ->firstOrNew();

        $user->unique_id = $msg->from->id;
        $user->username = $msg->from->username;
        $user->email = $msg->from->id . '@telegram';
        $user->name = $msg->from->firstName ?? $msg->from->lastName ?? $user->email;
        $user->password = Str::random();
        $user->is_bot = $msg->from->isBot;
        $user->is_premium = (bool) data_get($msg->from, 'is_premium');

        $user->save();

        return $user;
    }

    /**
     * Resolves chat from the given Telegram message and user.
     * Will create a new record if such chat doesn't exist yet.
     *
     * @param \Telegram\Bot\Objects\Message $msg
     * @param User|null $from
     *
     * @return Chat|null
     */
    public static function chat(\Telegram\Bot\Objects\Message $msg, ?User $from = null): ?Chat
    {
        if ($from === null && $msg->chat->username) {
            $from = User::query()
                ->withTelegram()
                ->where('username', $msg->chat->username)
                ->first();
        }

        $chat = Chat::query()
            ->where('unique_id', $msg->chat->id)
            ->firstOrNew();

        $chat->unique_id = $msg->chat->id;
        $chat->user_id = $from?->unique_id ?? $msg->from?->id;
        $chat->username = $msg->chat->username ?? $from?->username ?? $msg->from?->username;
        $chat->type = $msg->chat->type;

        $chat->save();

        return $chat;
    }

    /**
     * Resolves message from the given Telegram message and chat.
     * Will create a new record if such message doesn't exist yet.
     *
     * @param \Telegram\Bot\Objects\Message $msg
     * @param Chat|null $chat
     *
     * @return Message|null
     */
    public static function message(\Telegram\Bot\Objects\Message $msg, ?Chat $chat = null): ?Message
    {
        $text = $msg->text ?? $msg->caption;
        $metadata = null;

        if ($text) {
            $hasCommand = (bool) $msg->get('entities', collect())
                ->contains('type', 'bot_command');

            $type = $hasCommand ? 'command' : 'text';

            $chatId = $chat?->unique_id ?? $msg->chat->id;

            $message = Message::query()
                ->where('unique_id', $msg->messageId)
                ->where('chat_id', $chatId)
                ->firstOrNew();

            $message->unique_id = $msg->messageId;
            $message->chat_id = $chatId;
            $message->type = $type;
            $message->text = $text;
            $message->sent_at = Carbon::createFromTimestamp($msg->date);
            $message->edited_at = $msg->editDate
                ? Carbon::createFromTimestamp($msg->editDate) : null;

            $message->metadata = $metadata ? (array) $metadata : null;

            $message->save();

            return $message;
        }

        return null;
    }


    /**
     * Stores downloaded file and uploads it to
     * Google Cloud Storage bucket (`uploads`).
     *
     * @param array{
     *      file_id: string,
     *      file_unique_id: string,
     * } $params
     * @param Api $bot
     * @param Message $message
     * @param Chat $chat
     *
     * @return File|null
     * @throws TelegramSDKException
     * @throws Exception
     */
    protected static function downloadFile(array $params, Api $bot, Message $message, Chat $chat): ?File
    {
        $file = $bot->getFile($params);

        if (empty($file)) {
            return null;
        }

        $tempPath = Str::of(sys_get_temp_dir())
            ->finish('/')
            ->append(Str::random(6))
            ->finish('/')
            ->value();

        if ($file->filePath) {
            $fileName = Str::of($file->filePath)
                ->afterLast('/')
                ->value();

            if (!empty($fileName)) {
                $extension = Str::of($fileName)
                    ->after('.')
                    ->value();

                $tempPath .= $fileName;
            }
        }

        $resultPath = $bot->downloadFile($file, $tempPath);
        $attributes = [
            'file' => fopen($resultPath, 'r'),
            'context' => $message,
            'disk' => 'uploads',
            'disk_path' => '/' . File::slugFor($chat)
                . '/' . $chat->unique_id,
            'disk_name' => $fileName ?? null,
            'file_id' => $file->fileId,
            'unique_id' => $file->fileUniqueId,
            'path' => $file->filePath,
            'type' => mime_content_type($resultPath),
            'extension' => $extension ?? null,
            'size' => $file->fileSize,
        ];

        return (new FileRepository())
            ->create($attributes, true);
    }

    /**
     * Downloads photo (from the given message) using Telegram Bot API
     * and uploads it to Google Cloud Storage bucket (`uploads`).
     *
     * @param Api $bot
     * @param Message $message
     * @param Chat $chat
     *
     * @return File|null
     * @throws Exception
     */
    public static function photo(Api $bot, Message $message, Chat $chat): ?File
    {
        $variants = $message->photoVariants();

        if (empty($variants)) {
            return null;
        }

        $variant = (array) end($variants);
        return static::downloadFile($variant, $bot, $message, $chat);
    }

    /**
     * Downloads video (from the given message) using Telegram Bot API
     * and uploads it to Google Cloud Storage bucket (`uploads`).
     *
     * @param Api $bot
     * @param Message $message
     * @param Chat $chat
     *
     * @return File|null
     * @throws Exception
     */
    public static function video(Api $bot, Message $message, Chat $chat): ?File
    {
        $variant = $message->videoVariant();

        if (empty($variant)) {
            return null;
        }

        return static::downloadFile($variant, $bot, $message, $chat);
    }

    /**
     * Downloads document (from the given message) using Telegram Bot API
     * and uploads it to Google Cloud Storage bucket (`uploads`).
     *
     * @param Api $bot
     * @param Message $message
     * @param Chat $chat
     *
     * @return File|null
     * @throws Exception
     */
    public static function document(Api $bot, Message $message, Chat $chat): ?File
    {
        $variant = $message->documentVariant();

        if (empty($variant)) {
            return null;
        }

        return static::downloadFile($variant, $bot, $message, $chat);
    }
}
