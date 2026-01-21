<?php

namespace App\Helpers;

use Exception;
use Telegram\Bot\Api;
use Telegram\Bot\Laravel\Facades\Telegram;

/**
 * Class BotFinder.
 */
class BotFinder
{
    /**
     * Finds bot by token.
     *
     * @param string $token
     *
     * @return Api|null
     */
    public static function byToken(string $token): ?Api
    {
        $name = BotHelper::tokenToName($token);

        return $name ? Telegram::bot($name) : null;
    }

    /**
     * Finds bot by token or throws exception if there is none.
     *
     * @param string $token
     *
     * @return Api
     * @throws Exception
     */
    public static function byTokenOrFail(string $token): Api
    {
        $bot = static::byToken($token);

        if ($bot === null) {
            throw new Exception('Failed to find bot by given token.');
        }

        return $bot;
    }

    /**
     * Finds bot by name.
     *
     * @param string $name
     *
     * @return Api|null
     */
    public static function byName(string $name): ?Api
    {
        $token = BotHelper::nameToToken($name);

        return $token ? Telegram::bot($name) : null;
    }

    /**
     * Finds bot by name or throws an exception if there is none.
     *
     * @param string $name
     *
     * @return Api
     * @throws Exception
     */
    public static function byNameOrFail(string $name): Api
    {
        $bot = static::byName($name);

        if ($bot === null) {
            throw new Exception('Failed to find bot by given name.');
        }

        return $bot;
    }
}
