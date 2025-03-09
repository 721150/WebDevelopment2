<?php
namespace App\Models;

class Blog {
    private int $id;
    private string $dateTime;
    private Institution $institution;
    private Education $education;
    private Subject $subject;
    private TypeOfLaw $typeOfLaw;
    private string $description;
    private string $content;
    private array $reacties;

    public function __construct(int $id, string $dateTime, Institution $institution, Education $education, Subject $subject, TypeOfLaw $typeOfLaw, string $description, string $content, array $reacties = []) {
        $this->id = $id;
        $this->dateTime = $dateTime;
        $this->institution = $institution;
        $this->education = $education;
        $this->subject = $subject;
        $this->typeOfLaw = $typeOfLaw;
        $this->description = $description;
        $this->content = $content;
        $this->reacties = $reacties;
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

    public function getInstitution() {
        return $this->institution;
    }

    public function setInstitution(Institution $institution) {
        $this->institution = $institution;
    }

    public function getEducation() {
        return $this->education;
    }

    public function setEducation(Education $education) {
        $this->education = $education;
    }

    public function getSubject() {
        return $this->subject;
    }

    public function setSubject(Subject $subject) {
        $this->subject = $subject;
    }

    public function getTypeOfLaw() {
        return $this->typeOfLaw;
    }

    public function setTypeOfLaw(TypeOfLaw $typeOfLaw) {
        $this->typeOfLaw = $typeOfLaw;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription(string $description) {
        $this->description = $description;
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent(string $content) {
        $this->content = $content;
    }

    public function getReacties() {
        return $this->reacties;
    }

    public function addReactie(Reactie $reactie) {
        $this->reacties[] = $reactie;
    }
}
?>