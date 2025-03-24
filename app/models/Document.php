<?php
namespace App\Models;

use JsonSerializable;

class Document implements JsonSerializable {
    private ?int $id;
    private string $document;

    public function __construct(?int $id, string $document) {
        $this->id = $id;
        $this->document = $document;
    }

    public function getId() {
        return $this->id;
    }

    public function getDocument() {
        return $this->document;
    }

    public function setDocument(string $document) {
        $this->document = $document;
    }

    public function jsonSerialize(): array {
        return get_object_vars($this);
    }
}

?>