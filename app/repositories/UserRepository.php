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
        $stmt = $this->connection->prepare("SELECT u.id, u.firstname, u.lastname, u.email, u.password, u.institutionId, u.image, u.phone, i.id as institution_id, i.name as institution_name FROM `user` u JOIN `institution` i ON u.institutionId = i.id WHERE u.id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $institution = new Institution($row['institution_id'], $row['institution_name']);
        $image = $row['image'] ?? null;

        $user = new User($row['id'], $row['firstname'], $row['lastname'], $row['email'], null, $institution, $image, $row['phone']);

        return $user;
    }

    public function createAdmin(User $user) {
        $stmt = $this->connection->prepare("INSERT INTO `user` (firstname, lastname, email, password, institutionId, image, phone) VALUES (:firstname, :lastname, :email, :password, :institutionId, :image, :phone)");

        $firstname = $user->getFirstname();
        $lastname = $user->getLastname();
        $email = $user->getEmail();
        $password = $user->getPassword();
        $institutionId = $user->getInstitution()->getId();
        $image = $user->getImage();
        $phone = $user->getPhone();

        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':lastname', $lastname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':institutionId', $institutionId);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':phone', $phone);

        $stmt->execute();
        return true; // TODO dit aanpassen voor ophalen uit database via methode getOne
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
                $user = new Applicant($result['id'], $result['firstname'], $result['lastname'], $result['email'], null, $institution, $result['image'], $result['phone'], $result['applicantId'], $education);
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
                    $user = new Handler($result['id'], $result['firstname'], $result['lastname'], $result['email'], null, $institution, $result['image'], $result['phone'], $result['handlerId'], $typeOfLaws, $subjects);
                }
            }

            if ($user == null) {
                $stmt = $this->connection->prepare("SELECT user.id, firstname, lastname, email, password, image, phone, institution.id AS institutionId, institution.name AS institutionName FROM `user` JOIN `institution` ON user.institutionId = institution.id WHERE email = :email AND user.id NOT IN (SELECT userId FROM `handler` UNION SELECT userId FROM `applicant`)");
                $stmt->execute([':email' => $username]);

                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result && password_verify($password, $result['password'])) {
                    $institution = new Institution($result['institutionId'], $result['institutionName']);
                    $user = new User($result['id'], $result['firstname'], $result['lastname'], $result['email'], null, $institution, $result['image'], $result['phone']);
                }
            }
        } catch (Exception $e) {
            throw $e;
        }

        return $user;
    }

    public function createHandler(Handler $user) {
        $this->connection->beginTransaction();

        try {
            $stmt = $this->connection->prepare("INSERT INTO `user` (firstname, lastname, email, password, institutionId, image, phone) VALUES (:firstname, :lastname, :email, :password, :institutionId, :image, :phone)");

            $firstname = $user->getFirstname();
            $lastname = $user->getLastname();
            $email = $user->getEmail();
            $password = $user->getPassword();
            $institutionId = $user->getInstitution()->getId();
            $image = $user->getImage();
            $phone = $user->getPhone();

            $stmt->bindParam(':firstname', $firstname);
            $stmt->bindParam(':lastname', $lastname);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':institutionId', $institutionId);
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':phone', $phone);

            $stmt->execute();

            $userId = $this->connection->lastInsertId();

            $stmt = $this->connection->prepare("INSERT INTO `handler` (userId) VALUES (:userId)");
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();

            $handlerId = $this->connection->lastInsertId();

            foreach ($user->getTypeOfLaws() as $typeOfLaw) {
                $stmt = $this->connection->prepare("INSERT INTO `handlerTypeOfLow` (handlerId, typeOfLawId) VALUES (:handlerId, :typeOfLawId)");
                $typeOfLawId = $typeOfLaw->getId();
                $stmt->bindParam(':handlerId', $handlerId);
                $stmt->bindParam(':typeOfLawId', $typeOfLawId);
                $stmt->execute();
            }

            foreach ($user->getSubjects() as $subject) {
                $stmt = $this->connection->prepare("INSERT INTO `handlerSubject` (handlerId, subjectId) VALUES (:handlerId, :subjectId)");
                $subjectId = $subject->getId();
                $stmt->bindParam(':handlerId', $handlerId);
                $stmt->bindParam(':subjectId', $subjectId);
                $stmt->execute();
            }

            $this->connection->commit();

            return true; // TODO dit aanpassen voor ophalen uit database via methode getOne
        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function createApplicant(Applicant $user) {
        $this->connection->beginTransaction();

        try {
            $stmt = $this->connection->prepare("INSERT INTO `user` (firstname, lastname, email, password, institutionId, image, phone) VALUES (:firstname, :lastname, :email, :password, :institutionId, :image, :phone)");

            $firstname = $user->getFirstname();
            $lastname = $user->getLastname();
            $email = $user->getEmail();
            $password = $user->getPassword();
            $institutionId = $user->getInstitution()->getId();
            $image = $user->getImage();
            $phone = $user->getPhone();

            $stmt->bindParam(':firstname', $firstname);
            $stmt->bindParam(':lastname', $lastname);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':institutionId', $institutionId);
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':phone', $phone);

            $stmt->execute();

            $userId = $this->connection->lastInsertId();

            $stmt = $this->connection->prepare("INSERT INTO `applicant` (userId, educationId) VALUES (:userId, :educationId)");
            $educationId = $user->getEducation()->getId();
            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':educationId', $educationId);
            $stmt->execute();

            $this->connection->commit();

            return true; // TODO dit aanpassen voor ophalen uit database via methode getOne
        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }
}