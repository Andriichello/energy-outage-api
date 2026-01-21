<?php

namespace App\Helpers;

use Jfcherng\Diff\Differ;
use Jfcherng\Diff\SequenceMatcher;

/**
 * Class DiffHelper.
 */
class DiffHelper
{
    /**
     * Generate a GitHub-style diff for Telegram (MarkdownV2 format).
     *
     * @param string $oldText
     * @param string $newText
     *
     * @return string
     */
    public static function compare(string $oldText, string $newText): string
    {
        // Use Differ to get raw diff operations
        $differ = new Differ(explode("\n", $oldText), explode("\n", $newText));
        $diffResult = $differ->getGroupedOpcodes();

        return self::formatForTelegram($diffResult, $oldText, $newText);
    }

    /**
     * Format diff output for Telegram MarkdownV2.
     * Shows the new version with highlights for added/changed content.
     *
     * @param array $diffResult
     * @param string $oldText
     * @param string $newText
     *
     * @return string
     */
    private static function formatForTelegram(array $diffResult, string $oldText, string $newText): string
    {
        $oldLines = explode("\n", $oldText);
        $newLines = explode("\n", $newText);
        $output = [];

        foreach ($diffResult as $group) {
            foreach ($group as $opcode) {
                [$tag, $i1, $i2, $j1, $j2] = $opcode;

                switch ($tag) {
                    case SequenceMatcher::OP_REP:
                        // Show new lines with bold (changed content)
                        for ($j = $j1; $j < $j2; $j++) {
                            $output[] = '*' . MessageComposer::escape($newLines[$j]) . '*';
                        }
                        break;

                    case SequenceMatcher::OP_DEL:
                        // Skip deleted lines - we only show the new version
                        break;

                    case SequenceMatcher::OP_INS:
                        // Show added lines with bold
                        for ($j = $j1; $j < $j2; $j++) {
                            $output[] = '*' . MessageComposer::escape($newLines[$j]) . '*';
                        }
                        break;

                    case SequenceMatcher::OP_EQ:
                        // Show unchanged lines as is
                        for ($j = $j1; $j < $j2; $j++) {
                            $output[] = MessageComposer::escape($newLines[$j]);
                        }
                        break;
                }
            }
        }

        return implode("\n", $output);
    }

    /**
     * Generate a simple highlight of changes (alternative approach).
     *
     * @param string $oldText
     * @param string $newText
     *
     * @return string
     */
    public static function highlightChanges(string $oldText, string $newText): string
    {
        if ($oldText === $newText) {
            return MessageComposer::escape($newText);
        }

        return self::compare($oldText, $newText);
    }
}
