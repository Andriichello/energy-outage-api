<?php

namespace App\Helpers;

/**
 * Class BotHelper.
 */
class BotHelper
{
    /**
     * Returns bot configs from `telegram.php` config.
     *
     * @return array<string, array{
     *     token: string,
     *     certificate_path?: ?string,
     *     webhook_url?: ?string,
     *     allowed_updates?: ?bool,
     *     commands?: string[],
     * }>
     */
    public static function botConfigs(): array
    {
        return config('telegram.bots', []);
    }

    /**
     * Returns bot config by given name from `telegram.php` config.
     *
     * @param string $name
     *
     * @return null|array{
     *      token: string,
     *      certificate_path?: ?string,
     *      webhook_url?: ?string,
     *      allowed_updates?: ?bool,
     *      commands?: string[],
     *  }
     */
    public static function botConfig(string $name): ?array
    {
        return static::botConfigs()[$name] ?? null;
    }

    /**
     * Searches for bots (in `telegram.php` config)
     * by the token and returns the name.
     *
     * @param string $token
     *
     * @return string|null
     */
    public static function tokenToName(string $token): ?string
    {
        foreach (static::botConfigs() as $name => $bot) {
            if ($bot['token'] === $token) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Searches for bots (in `telegram.php` config)
     * by the name and returns the token.
     *
     * @param string $name
     *
     * @return string|null
     */
    public static function nameToToken(string $name): ?string
    {
        $bot = static::botConfig($name);

        return $bot['token'] ?? null;
    }
}
