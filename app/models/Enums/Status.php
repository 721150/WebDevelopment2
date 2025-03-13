<?php
namespace App\Models\Enums;

enum Status: string {
    case Open = "Open";
    case Closed = "Gesloten";
    case InProgress = "In behandeling";

    public static function fromDatabase(string $status): ?self {
        return match ($status) {
            self::Open->value => self::Open,
            self::Closed->value => self::Closed,
            self::InProgress->value => self::InProgress,
            default => null,
        };
    }
}
?>