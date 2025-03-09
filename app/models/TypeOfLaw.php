<?php
namespace App\Models;

use App\Models\Enums\TypeOfLow;

class TypeOfLaw {
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
}
?>