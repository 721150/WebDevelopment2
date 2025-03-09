<?php
namespace App\Models;

use JsonSerializable;

class Institution implements JsonSerializable {
    private int $id;
    private string $name;

    public function __construct(int $id, string $name) {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    public function jsonSerialize(): array {
        $vars = get_object_vars($this);
        return $vars;
    }
}
?>