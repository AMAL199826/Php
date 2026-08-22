<?php

namespace App\Libraries;

class ApiAuth
{
    protected static ?array $user = null;

    public static function setUser(array $user): void
    {
        self::$user = $user;
    }

    public static function user(): ?array
    {
        return self::$user;
    }
}