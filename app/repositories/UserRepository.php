<?php
namespace App\Repositories;

use App\Models\Applicant;
use App\Models\Education;
use App\Models\Enums\TypeOfLow;
use App\Models\Handler;
use App\Models\Institution;
use App\Models\TypeOfLaw;
use Exception;
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

    public function createAdmin(User $user) {
        $stmt = $this->connection->prepare("INSERT INTO `user` (firstname, lastname, email, password, institutionId, image, phone) VALUES (:firstname, :lastname, :email, :password, :institutionId, :image, :phone)");

        $firstname = $user->getFirstname();
        $lastname = $user->getLastname();
        $email = $user->getEmail();
        $password = $user->getPassword();
        $institutionId = $user->getInstitution()->getId();
        $image = $user->getImageString();
        $phone = $user->getPhone();

        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':lastname', $lastname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':institutionId', $institutionId);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':phone', $phone);

        $stmt->execute();
        return $user;
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

    public function login(string $username, string $password) {
        $user = null;
        try {
            $stmt = $this->connection->prepare("SELECT user.id, firstname, lastname, email, password, image, phone, institution.id AS institutionId, institution.name AS institutionName, education.id AS educationId, education.name AS educationName, applicant.id AS applicantId FROM `applicant` JOIN `user` ON user.id = applicant.userId JOIN `institution` ON user.institutionId = institution.id JOIN `education` ON applicant.educationId = education.id WHERE email = :email");
            $stmt->execute([':email' => $username]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && password_verify($password, $result['password'])) {
                $institution = new Institution($result['institutionId'], $result['institutionName']);
                $education = new Education($result['educationId'], $result['educationName']);
                $user = new Applicant($result['id'], $result['firstname'], $result['lastname'], $result['email'], $institution, $result['image'], $result['phone'], $result['applicantId'], $education);
            }

            if ($user == null) {
                $stmt = $this->connection->prepare("SELECT user.id, firstname, lastname, email, password, image, phone, institution.id AS institutionId, institution.name AS institutionName, handler.id AS handlerId, GROUP_CONCAT(DISTINCT CONCAT(typeOfLaw.id, ':', typeOfLaw.description)) AS typeOfLaws, GROUP_CONCAT(DISTINCT CONCAT(subject.id, ':', subject.description)) AS subjects FROM `handler` JOIN `user` ON user.id = handler.userId JOIN `institution` ON user.institutionId = institution.id LEFT JOIN `handlerTypeOfLow` ON handler.id = handlerTypeOfLow.handlerId LEFT JOIN `typeOfLaw` ON handlerTypeOfLow.typeOfLawId = typeOfLaw.id LEFT JOIN `handlerSubject` ON handler.id = handlerSubject.handlerId LEFT JOIN `subject` ON handlerSubject.subjectId = subject.id WHERE email = :email GROUP BY user.id");
                $stmt->execute([':email' => $username]);

                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result && password_verify($password, $result['password'])) {
                    $institution = new Institution($result['institutionId'], $result['institutionName']);
                    $typeOfLaws = [];
                    foreach (explode(',', $result['typeOfLaws']) as $typeOfLaw) {
                        list($id, $description) = explode(':', $typeOfLaw);
                        $typeOfLaws[] = new TypeOfLaw($id, TypeOfLow::fromDatabase($description));
                    }
                    $subjects = explode(',', $result['subjects']);
                    $user = new Handler($result['id'], $result['firstname'], $result['lastname'], $result['email'], $institution, $result['image'], $result['phone'], $result['handlerId'], $typeOfLaws, $subjects);
                }
            }

            if ($user == null) {
                $stmt = $this->connection->prepare("SELECT user.id, firstname, lastname, email, password, image, phone, institution.id AS institutionId, institution.name AS institutionName FROM `user` JOIN `institution` ON user.institutionId = institution.id WHERE email = :email AND user.id NOT IN (SELECT userId FROM `handler` UNION SELECT userId FROM `applicant`)");
                $stmt->execute([':email' => $username]);

                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result && password_verify($password, $result['password'])) {
                    $institution = new Institution($result['institutionId'], $result['institutionName']);
                    $user = new User($result['id'], $result['firstname'], $result['lastname'], $result['email'], $institution, $result['image'], $result['phone']);
                }
            }
        } catch (Exception $e) {
            throw $e;
        }

        return $user;
    }
}