<?php
namespace App\Models;

use JsonSerializable;

class Reactie implements JsonSerializable {
    private ?int $id;
    private string $dateTime;
    private string $content;

    public function __construct(?int $id, string $dateTime, string $content) {
        $this->id = $id;
        $this->dateTime = $dateTime;
        $this->content = $content;
    }

    public function getId() {
        return $this->id;
    }

    public function getDateTime() {
        return $this->dateTime;
    }

    public function setDateTime(string $dateTime) {
        $this->dateTime = $dateTime;
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent(string $content) {
        $this->content = $content;
    }

    public function jsonSerialize(): array {
        return get_object_vars($this);
    }
}
?>