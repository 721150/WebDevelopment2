<?php
namespace App\Models;

use App\Models\Enums\Status;
use JsonSerializable;

class CaseModel implements JsonSerializable {
    private ?int $id;
    private Applicant $user;
    private Subject $subject;
    private TypeOfLaw $typeOfLaw;
    private string $content;
    private Status $status;
    private Institution $institution;
    private Education $education;
    private array $documents;

    public function __construct(?int $id, Applicant $user, Subject $subject, TypeOfLaw $typeOfLaw, string $content, Status $status, Institution $institution, Education $education, array $documents = []) {
        $this->id = $id;
        $this->user = $user;
        $this->subject = $subject;
        $this->typeOfLaw = $typeOfLaw;
        $this->content = $content;
        $this->status = $status;
        $this->institution = $institution;
        $this->education = $education;
        $this->documents = $documents;
    }

    public function getId() {
        return $this->id;
    }

    public function getUser() {
        return $this->user;
    }

    public function setUser(Applicant $user) {
        $this->user = $user;
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

    public function getContent() {
        return $this->content;
    }

    public function setContent(string $content) {
        $this->content = $content;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus(Status $status) {
        $this->status = $status;
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

    public function getDocuments() {
        return $this->documents;
    }

    public function addDocument(Document $document) {
        $this->documents[] = $document;
    }

    public function jsonSerialize(): array {
        $vars = get_object_vars($this);
        return $vars;
    }
}
?>