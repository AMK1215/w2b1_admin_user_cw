<?php

namespace App\Enums;

enum UserType: int
{
    case Owner = 10;
    case Player = 20;
    case SystemWallet = 30;

    public static function usernameLength(UserType $type): int
    {
        return match ($type) {
            self::Owner => 1,
            self::Player => 2,
            self::SystemWallet => 3,
        };
    }

    public static function childUserType(UserType $type): UserType
    {
        return match ($type) {
            self::Owner => self::Player,
            self::Player => self::SystemWallet,
            self::SystemWallet => self::SystemWallet,
        };
    }

    public static function canHaveChild(UserType $parent, UserType $child): bool
    {
        return match ($parent) {
            self::Owner => $child === self::Player || $child === self::SystemWallet,
            self::Player => false,
            self::SystemWallet => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Player => 'Player',
            self::SystemWallet => 'System Wallet',
        };
    }
}
