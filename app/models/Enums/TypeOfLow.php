<?php
namespace App\Models\Enums;

enum TypeOfLow: string {
    case Petition = "Petition";
    case Appeal = "Appeal";
    case Objection = "Objection";
    case Complaint = "Complaint";

    public static function fromDatabase(string $type): ?self {
        return match($type) {
            'Petition' => self::Petition,
            'Appeal' => self::Appeal,
            'Objection' => self::Objection,
            'Complaint' => self::Complaint,
            default => null,
        };
    }
}
?>