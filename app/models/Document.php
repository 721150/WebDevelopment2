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

    public function getId(): ?int {
        return $this->id;
    }

    public function getDocument(): string {
        return $this->document;
    }

    public function setDocument(string $document): void {
        $this->document = $document;
    }

    public function jsonSerialize(): array {
        return get_object_vars($this);
    }
}
?>