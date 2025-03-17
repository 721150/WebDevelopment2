<?php
namespace App\Models;

use JsonSerializable;

class Handler extends User implements JsonSerializable {
    private ?int $userId;
    private array $typeOfLaws = [];
    private array $subjects = [];

    public function __construct(?int $id, string $firstname, string $lastname, string $email, ?string $password, Institution $institution, ?string $image, string $phone, ?int $userId, array $typeOfLaws = [], array $subjects = []) {
        parent::__construct($id, $firstname, $lastname, $email, $password, $institution, $image, $phone);
        $this->userId = $userId;
        $this->typeOfLaws = $typeOfLaws;
        $this->subjects = $subjects;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getTypeOfLaws() {
        return $this->typeOfLaws;
    }

    public function addTypeOfLaw(TypeOfLaw $typeOfLaw) {
        $this->typeOfLaws[] = $typeOfLaw;
    }

    public function getSubjects() {
        return $this->subjects;
    }

    public function addSubject(Subject $subject) {
        $this->subjects[] = $subject;
    }

    public function jsonSerialize(): array {
        $vars = parent::jsonSerialize();
        $vars['userId'] = $this->userId;
        $vars['typeOfLaws'] = $this->typeOfLaws;
        $vars['subjects'] = $this->subjects;
        return $vars;
    }
}
?>