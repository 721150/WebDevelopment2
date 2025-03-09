<?php
namespace App\Models\Enums;

enum TypeOfLow: string {
    case Petition = "Petition";
    case Appeal = "Appeal";
    case Objection = "Objection";
    case Complaint = "Complaint";

    public static function fromDatabase(string $type): ?self {
        return match($type) {
            self::Petition->value => self::Petition,
            self::Appeal->value => self::Appeal,
            self::Complaint->value => self::Objection,
            self::Objection->value => self::Complaint,
            default => null,
        };
    }
}
?>