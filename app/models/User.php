<?php
namespace App\Models;

class User {
    protected ?int $id;
    protected string $firstname;
    protected string $lastname;
    protected string $email;
    protected ?string $password;
    protected Institution $institution;
    protected ?string $image;
    protected string $phone;

    public function __construct(?int $id, string $firstname, string $lastname, string $email, ?string $password, Institution $institution, ?string $image, string $phone) {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->password = $password;
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

    public function getPassword() {
        return $this->password;
    }

    public function setPassword(string $password) {
        $this->password = $password;
    }

    public function getInstitution() {
        return $this->institution;
    }

    public function setInstitution(Institution $institution) {
        $this->institution = $institution;
    }

    public function getImage() {
        if ($this->image !== null) {
            return 'data:image/jpeg;base64,' . base64_encode($this->image);
        }
        return null;
    }

    public function getImageString() {
        return $this->image;
    }

    public function setImage(string $image) {
        if (is_array($image) && isset($image['tmp_name']) && !empty($image['tmp_name'])) {
            $imageData = file_get_contents($image['tmp_name']);
            $this->image = $imageData;
        }
    }

    public function getPhone() {
        return $this->phone;
    }

    public function setPhone(string $phone) {
        $this->phone = $phone;
    }
}
?>