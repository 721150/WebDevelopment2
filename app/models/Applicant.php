<?php
namespace App\Models;

use JsonSerializable;

class Applicant extends User implements JsonSerializable {
    private ?int $userId;
    private Education $education;

    public function __construct(?int $id, string $firstname, string $lastname, string $email, ?string $password, Institution $institution, ?string $image, string $phone, ?int $userId, Education $education) {
        parent::__construct($id, $firstname, $lastname, $email, $password, $institution, $image, $phone);
        $this->userId = $userId;
        $this->education = $education;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getEducation() {
        return $this->education;
    }

    public function setEducation(Education $education) {
        $this->education = $education;
    }

    public function jsonSerialize(): array {
        $vars = get_object_vars($this);
        return $vars;
    }
}
?>