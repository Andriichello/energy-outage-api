<?php

namespace App\Helpers;

/**
 * Class GroupHelper.
 */
class GroupHelper
{
    /**
     * Available electricity groups for users to subscribe to.
     *
     * @var array
     */
    public const AVAILABLE_GROUPS = [
        '1-1', '1-2',
        '2-1', '2-2',
        '3-1', '3-2',
        '4-1', '4-2',
        '5-1', '5-2',
        '6-1', '6-2',
    ];
    /**
     * Extract electricity groups from a text paragraph.
     *
     * Groups are formatted as "X-Y" where X is the main group number
     * and Y is the subgroup number (e.g., "3-1", "5-2").
     *
     * @param string $text
     *
     * @return array
     */
    public static function extractGroups(string $text): array
    {
        // Match patterns like "3-1", "5-2", "10-3", etc.
        preg_match_all('/\b(\d+)-(\d+)\b/', $text, $matches);

        if (empty($matches[0])) {
            return [];
        }

        // Return unique groups found in the text
        return array_unique($matches[0]);
    }

    /**
     * Check if any of the user's interested groups are mentioned in the text.
     *
     * @param string $text
     * @param array|null $interestedGroups
     *
     * @return bool
     */
    public static function containsInterestedGroups(string $text, ?array $interestedGroups): bool
    {
        if (empty($interestedGroups)) {
            return false;
        }

        $foundGroups = self::extractGroups($text);

        if (empty($foundGroups)) {
            return false;
        }

        // Check if any of the user's interested groups are in the found groups
        return !empty(array_intersect($interestedGroups, $foundGroups));
    }

    /**
     * Check if any paragraph in the array contains the user's interested groups.
     *
     * @param array $paragraphs
     * @param array|null $interestedGroups
     *
     * @return bool
     */
    public static function paragraphsContainInterestedGroups(array $paragraphs, ?array $interestedGroups): bool
    {
        if (empty($interestedGroups) || empty($paragraphs)) {
            return false;
        }

        foreach ($paragraphs as $paragraph) {
            if (self::containsInterestedGroups($paragraph, $interestedGroups)) {
                return true;
            }
        }

        return false;
    }
}
