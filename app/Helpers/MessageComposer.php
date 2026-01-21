<?php

namespace App\Helpers;

use App\Models\Chat;

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
        $message = "\n*Привіт*\n\n"
            . static::escape("Цей бот надсилатиме тобі повідомлення про *оновлення графіку відключень* " .
                "на сайті *Закарпаттяобленерго*.");

        return [
            'chat_id' => $chat->unique_id,
            'text' => $message,
            'parse_mode' => 'MarkdownV2',
        ];
    }
}
