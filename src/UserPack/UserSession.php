<?php

namespace Tivins\UserPack;

class UserSession
{
    protected static string $sessionKey = 'uid';

    public static function isAuthenticated(): bool
    {
        return self::getID() > 0;
    }

    public static function getID(): int
    {
        return (int)($_SESSION[self::$sessionKey] ?? 0);
    }

    public static function setID(int $id): void
    {
        $_SESSION[self::$sessionKey] = $id;
    }
}
