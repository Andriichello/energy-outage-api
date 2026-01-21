<?php

namespace App\Helpers;

use App\Models\Chat;
use App\Models\UpdatedInformation;

/**
 * Class MessageComposer.
 */
class MessageComposer
{
    /**
     * Escape special characters with a backslash.
     *
     * @param string|null $string
     * @param array|null $search
     * @param array|null $replace
     *
     * @return string|null
     */
    public static function escape(
        ?string $string,
        ?array $search = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'],
        ?array $replace = ['\_', '\*', '\[', '\]', '\(', '\)', '\~', '\`', '\>', '\#', '\+', '\-', '\=', '\|', '\{', '\}', '\.', '\!']
    ): ?string {
        if (empty($string)) {
            return $string;
        }

        return (string) str_replace($search, $replace, $string);
    }

    /**
     * Compose a welcome message for the new user.
     *
     * @param Chat $chat
     *
     * @return array{chat_id: int, text: string, parse_mode: string}
     */
    public static function welcome(Chat $chat): array
    {
        $message = "\n*Привіт*\n" .
            "Цей бот надсилатиме повідомлення про *оновлення графіку відключень* " .
            "на сайті *Закарпаттяобленерго*.";

        return [
            'chat_id' => $chat->unique_id,
            'text' => $message,
            'parse_mode' => 'MarkdownV2',
        ];
    }

    /**
     * Compose a message about updated information being changed.
     *
     * @param UpdatedInformation $current
     * @param UpdatedInformation|null $previous
     *
     * @return array{text: string, parse_mode: string}
     */
    public static function changed(UpdatedInformation $current, ?UpdatedInformation $previous = null): array
    {
        $message = "\n*Оновлення:*\n" .
            self::escape($current->content);

        return [
            'text' => $message,
            'parse_mode' => 'MarkdownV2',
        ];
    }
}
