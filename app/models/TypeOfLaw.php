<?php
namespace App\Models;

use App\Models\Enums\TypeOfLow;
use JsonSerializable;

class TypeOfLaw implements JsonSerializable {
    private int $id;
    private TypeOfLow $description;

    public function __construct(int $id, TypeOfLow $description) {
        $this->id = $id;
        $this->description = $description;
    }

    public function getId() {
        return $this->id;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription(TypeOfLow $description) {
        $this->description = $description;
    }

    public function jsonSerialize(): array {
        $vars = get_object_vars($this);
        return $vars;
    }
}
?>