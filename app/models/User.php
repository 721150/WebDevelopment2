<?php
namespace App\Models;

class User {
    protected int $id;
    protected string $firstname;
    protected string $lastname;
    protected string $email;
    protected Institution $institution;
    protected ?string $image;
    protected string $phone;

    public function __construct(int $id, string $firstname, string $lastname, string $email, Institution $institution, string $image, string $phone) {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->institution = $institution;
        $this->image = $image;
        $this->phone = $phone;
    }

    public function getId() {
        return $this->id;
    }

    public function getFirstname() {
        return $this->firstname;
    }

    public function setFirstname(string $firstname) {
        $this->firstname = $firstname;
    }

    public function getLastname() {
        return $this->lastname;
    }

    public function setLastname(string $lastname) {
        $this->lastname = $lastname;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail(string $email) {
        $this->email = $email;
    }

    public function getInstitution() {
        return $this->institution;
    }

    public function setInstitution(Institution $institution) {
        $this->institution = $institution;
    }

    public function getImage() {
        return $this->image;
    }

    public function setImage(string $image) {
        $this->image = $image;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function setPhone(string $phone) {
        $this->phone = $phone;
    }
}
?>