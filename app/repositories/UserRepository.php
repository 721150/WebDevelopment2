<?php
namespace App\Repositories;

use PDO;
use App\Models\User;

class UserRepository extends Repository { // TODO deze class werkzaam maken

    public function getAll() {
        $stmt = $this->connection->prepare("SELECT * FROM `user`");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, User::class);
    }

    public function getOne(int $id) {
        $stmt = $this->connection->prepare("SELECT * FROM `user` WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetchObject(User::class);
    }

    public function create(User $user) {
        $stmt = $this->connection->prepare("INSERT INTO `user` (firstname, lastname, email, institutionId, image, phone) VALUES (:firstname, :lastname, :email, :institutionId, :image, :phone)");

        $firstname = $user->getFirstname();
        $lastname = $user->getLastname();
        $email = $user->getEmail();
        $institutionId = $user->getInstitution()->getId();
        $image = $user->getImage();
        $phone = $user->getPhone();

        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':lastname', $lastname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':institutionId', $institutionId);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':phone', $phone);

        $stmt->execute();
        return $this->getOne($this->connection->lastInsertId());
    }

    public function update(User $user) {
        $stmt = $this->connection->prepare("UPDATE `user` SET firstname = :firstname, lastname = :lastname, email = :email, institutionId = :institutionId, image = :image, phone = :phone WHERE id = :id");

        $firstname = $user->getFirstname();
        $lastname = $user->getLastname();
        $email = $user->getEmail();
        $institutionId = $user->getInstitution()->getId();
        $image = $user->getImage();
        $phone = $user->getPhone();
        $id = $user->getId();

        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':lastname', $lastname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':institutionId', $institutionId);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':id', $id);

        $stmt->execute();
        return $this->getOne($id);
    }

    public function delete(int $id) {
        $stmt = $this->connection->prepare("DELETE FROM `user` WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}