<?php
namespace App\Models\Enums;

enum Status: string {
    case Open = "Open";
    case Closed = "Closed";
    case InProgress = "In Progress";
}
?>