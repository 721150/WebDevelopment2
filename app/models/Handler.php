<?php
namespace App\Models;

class Handler extends User {
    private int $userId;
    private array $typeOfLaws = [];
    private array $subjects = [];

    public function __construct(int $id, string $firstname, string $lastname, string $email, Institution $institution, string $image, string $phone, int $userId, array $typeOfLaws = [], array $subjects = []) {
        parent::__construct($id, $firstname, $lastname, $email, $institution, $image, $phone);
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
}
?>