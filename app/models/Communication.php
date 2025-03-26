<?php
namespace App\Models;

use JsonSerializable;

class Communication implements JsonSerializable {
    private int $id;
    private Handler  $handler;
    private string $content;

    public function __construct(int $id, Handler $handler, string $content) {
        $this->id = $id;
        $this->handler = $handler;
        $this->content = $content;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getHandler(): Handler {
        return $this->handler;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function setHandler(Handler $handler): void {
        $this->handler = $handler;
    }

    public function setContent(string $content): void {
        $this->content = $content;
    }

    public function jsonSerialize(): array {
        return get_object_vars($this);
    }
}
?>