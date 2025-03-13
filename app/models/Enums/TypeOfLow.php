<?php
namespace App\Models\Enums;

enum TypeOfLow: string {
    case Petition = "Verzoekschrift";
    case Appeal = "Beroep";
    case Objection = "Bezwaar";
    case Complaint = "Klacht";

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