<?php

namespace App\Helpers;

use App\Models\Chat;
use App\Models\UpdatedInformation;
use Illuminate\Support\Facades\Log;

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
     * Unescape special characters by removing backslashes.
     *
     * @param string|null $string
     * @param array|null $search
     * @param array|null $replace
     *
     * @return string|null
     */
    public static function unescape(
        ?string $string,
        ?array $search = ['\_', '\*', '\[', '\]', '\(', '\)', '\~', '\`', '\>', '\#', '\+', '\-', '\=', '\|', '\{', '\}', '\.', '\!'],
        ?array $replace = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!']
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
            "на сайті *Закарпаттяобленерго*";

        return [
            'chat_id' => $chat->unique_id,
            'text' => $message,
            'parse_mode' => 'MarkdownV2',
        ];
    }

    /**
     * Compose a message about new paragraphs being added to updated information.
     *
     * @param UpdatedInformation $current
     * @param UpdatedInformation|null $previous
     * @param Chat|null $chat
     * @param bool $disableNotification
     *
     * @return array{text: string, parse_mode: string, disable_notification?: bool}
     */
    public static function added(
        UpdatedInformation $current,
        ?UpdatedInformation $previous = null,
        ?Chat $chat = null,
        bool $disableNotification = false
    ): array {
        $added = DiffHelper::added($current, $previous);
        $content = join("\n\n", $added);

        Log::info('added: ', ['added' => $added]);

        $message = empty($previous) || count($current->paragraphs) === count($added)
            ? "\n*Актуальна інформація:*\n\n"
            : "";

        $message .= self::escape($content);

        $result = [
            'chat_id' => $chat?->unique_id,
            'text' => $message,
            'parse_mode' => 'MarkdownV2',
        ];

        if ($disableNotification) {
            $result['disable_notification'] = true;
        }

        return $result;
    }

    /**
     * Compose a message about updated information being changed.
     *
     * @param UpdatedInformation $current
     * @param UpdatedInformation|null $previous
     * @param Chat|null $chat
     *
     * @return array{text: string, parse_mode: string}
     */
    public static function changed(
        UpdatedInformation $current,
        ?UpdatedInformation $previous = null,
        ?Chat $chat = null
    ): array {
        $message = empty($previous)
            ? "\n*Актуальна інформація:*\n\n"
            : "\n*Оновлена інформація:*\n\n";

        $message .= self::escape($current->content);

//        if ($previous !== null) {
//            // Show a diff between previous and current
//            $diff = DiffHelper::highlightChanges($previous->content, $current->content);
//            $message .= $diff;
//        } else {
//            // No previous version, just show current content
//            $message .= self::escape($current->content);
//        }

        return [
            'chat_id' => $chat?->unique_id,
            'text' => $message,
            'parse_mode' => 'MarkdownV2',
        ];
    }
}
