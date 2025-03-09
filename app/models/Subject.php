<?php
namespace App\Models;

use JsonSerializable;

class Subject implements JsonSerializable {
    private int $id;
    private string $description;

    public function __construct(int $id, string $description) {
        $this->id = $id;
        $this->description = $description;
    }

    public function getId() {
        return $this->id;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription(string $description) {
        $this->description = $description;
    }

    public function jsonSerialize(): array {
        $vars = get_object_vars($this);
        return $vars;
    }
}
?>