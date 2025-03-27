<?php
namespace App\Repositories;

use App\Models\Applicant;
use App\Models\Education;
use App\Models\Enums\TypeOfLow;
use App\Models\Handler;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\TypeOfLaw;
use PDO;
use App\Models\User;
use PDOException;

class UserRepository extends Repository {

    public function getAll(): array {
        $users = [];

        try {
            $stmt = $this->connection->prepare("SELECT u.id, u.firstname, u.lastname, u.email, u.password, u.image, u.phone, i.id AS institutionId, i.name AS institutionName FROM `user` u JOIN `institution` i ON u.institutionId = i.id");
            $stmt->execute();
            $userRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($userRows as $row) {
                $users[] = $this->fetchUserDetails($row);
            }
        } catch (PDOException $exception) {
            throw $exception;
        }

        return $users;
    }

    public function getOne(int $id): Applicant|User|Handler|null {
        $user = null;

        try {
            $stmt = $this->connection->prepare("SELECT u.id, u.firstname, u.lastname, u.email, u.password, u.institutionId, u.image, u.phone, i.id as institution_id, i.name as institution_name, h.id as handler_id, a.id as applicant_id FROM `user` u JOIN `institution` i ON u.institutionId = i.id LEFT JOIN `handler` h ON u.id = h.userId LEFT JOIN `applicant` a ON u.id = a.userId WHERE u.id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return $user;
            }

            $institution = new Institution($row['institution_id'], $row['institution_name']);
            $image = $row['image'] ?? null;

            $user = new User($row['id'], $row['firstname'], $row['lastname'], $row['email'], null, $institution, $image, $row['phone']);

            if ($row['handler_id']) {
                $details = $this->fetchHandlerDetails($row['handler_id']);
                $typeOfLawObjects = array_map(fn($typeOfLaw) => new TypeOfLaw($typeOfLaw['typeOfLaw_id'], TypeOfLow::fromDatabase($typeOfLaw['typeOfLaw_description'])), $details['typeOfLaws']);
                $subjectObjects = array_map(fn($subject) => new Subject($subject['subject_id'], $subject['subject_description']), $details['subjects']);

                $user = new Handler($row['id'], $row['firstname'], $row['lastname'], $row['email'], null, $institution, $image, $row['phone'], $row['handler_id'], $typeOfLawObjects, $subjectObjects);
            } elseif ($row['applicant_id']) {
                $educationRow = $this->fetchApplicantDetails($row['applicant_id']);
                $education = new Education($educationRow['education_id'], $educationRow['education_name']);

                $user = new Applicant($row['id'], $row['firstname'], $row['lastname'], $row['email'], null, $institution, $image, $row['phone'], $row['applicant_id'], $education);
            }
        } catch (PDOException $exception) {
            throw $exception;
        }

        return $user;
    }

    public function createAdmin(User $user): Applicant|User|Handler|null {
        try {
            $userId = $this->insertUser($user);
        } catch (PDOException $exception) {
            throw $exception;
        }

        return $this->getOne($userId);
    }

    public function createHandler(Handler $user): Applicant|User|Handler|null {
        $this->connection->beginTransaction();

        try {
            $userId = $this->insertUser($user);

            $stmt = $this->connection->prepare("INSERT INTO `handler` (userId) VALUES (:userId)");
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();

            $handlerId = $this->connection->lastInsertId();

            $this->insertHandlerDetails($user, $handlerId);

            $this->connection->commit();

            return $this->getOne($userId);
        } catch (PDOException $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }

    public function createApplicant(Applicant $user): Applicant|User|Handler|null {
        $this->connection->beginTransaction();

        try {
            $userId = $this->insertUser($user);

            $stmt = $this->connection->prepare("INSERT INTO `applicant` (userId, educationId) VALUES (:userId, :educationId)");
            $educationId = $user->getEducation()->getId();
            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':educationId', $educationId);
            $stmt->execute();

            $this->connection->commit();

            return $this->getOne($userId);
        } catch (PDOException $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }

    public function update($user): Applicant|User|Handler|null {
        $this->connection->beginTransaction();

        try {
            $this->updateUserDetails($user);

            if ($user instanceof Handler) {
                $userId = $user->getUserId();
                $this->deleteHandlerDetails($userId);
                $this->insertHandlerDetails($user, $userId);
            }

            if ($user instanceof Applicant) {
                $stmt = $this->connection->prepare("UPDATE `applicant` SET educationId = :educationId WHERE userId = :userId");
                $educationId = $user->getEducation()->getId();
                $userId = $user->getId();
                $stmt->bindParam(':educationId', $educationId);
                $stmt->bindParam(':userId', $userId);
                $stmt->execute();
            }

            $this->connection->commit();
            return $this->getOne($user->getId());
        } catch (PDOException $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }

    public function delete(int $id): bool {
        try {
            $stmt = $this->connection->prepare("DELETE FROM `user` WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $exception) {
            throw $exception;
        }
    }

    public function login(string $username, string $password): Applicant|User|Handler|null {
        $user = null;
        try {
            $result = $this->fetchUserByRole($username, 'applicant');

            if ($result && password_verify($password, $result['password'])) {
                $institution = new Institution($result['institutionId'], $result['institutionName']);
                $education = new Education($result['educationId'], $result['educationName']);
                $user = new Applicant($result['id'], $result['firstname'], $result['lastname'], $result['email'], null, $institution, $result['image'], $result['phone'], $result['applicantId'], $education);
            }

            if ($user == null) {
                $result = $this->fetchUserByRole($username, 'handler');

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
                $result = $this->fetchUserByRole($username, 'user');

                if ($result && password_verify($password, $result['password'])) {
                    $institution = new Institution($result['institutionId'], $result['institutionName']);
                    $user = new User($result['id'], $result['firstname'], $result['lastname'], $result['email'], null, $institution, $result['image'], $result['phone']);
                }
            }
        } catch (PDOException $exception) {
            throw $exception;
        }

        return $user;
    }

    /**
     * @param User $user
     * @return int
     */
    private function insertUser(User $user): int
    {
        $stmt = $this->connection->prepare("INSERT INTO `user` (firstname, lastname, email, password, institutionId, image, phone) VALUES (:firstname, :lastname, :email, :password, :institutionId, :image, :phone)");

        $firstname = $user->getFirstname();
        $lastname = $user->getLastname();
        $email = $user->getEmail();
        $password = password_hash($user->getPassword(), PASSWORD_DEFAULT);
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

        return $this->connection->lastInsertId();
    }

    /**
     * @param Handler $user
     * @param bool|string $handlerId
     */
    private function insertHandlerDetails(Handler $user, bool|string $handlerId): void
    {
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
    }

    private function fetchHandlerDetails(int $handlerId): array {
        $stmt = $this->connection->prepare("SELECT t.id as typeOfLaw_id, t.description as typeOfLaw_description FROM `handlerTypeOfLow` htl JOIN `typeOfLaw` t ON htl.typeOfLawId = t.id WHERE htl.handlerId = :handlerId");
        $stmt->bindParam(':handlerId', $handlerId);
        $stmt->execute();
        $typeOfLaws = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->connection->prepare("SELECT s.id as subject_id, s.description as subject_description FROM `handlerSubject` hs JOIN `subject` s ON hs.subjectId = s.id WHERE hs.handlerId = :handlerId");
        $stmt->bindParam(':handlerId', $handlerId);
        $stmt->execute();
        $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['typeOfLaws' => $typeOfLaws, 'subjects' => $subjects];
    }

    private function fetchApplicantDetails(int $applicantId): array {
        $stmt = $this->connection->prepare("SELECT e.id as education_id, e.name as education_name FROM `applicant` a JOIN `education` e ON a.educationId = e.id WHERE a.id = :applicantId");
        $stmt->bindParam(':applicantId', $applicantId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function updateUserDetails($user): void {
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
    }

    private function deleteHandlerDetails(int $handlerId): void {
        $stmt = $this->connection->prepare("DELETE FROM `handlerTypeOfLow` WHERE handlerId = :handlerId");
        $stmt->bindParam(':handlerId', $handlerId);
        $stmt->execute();

        $stmt = $this->connection->prepare("DELETE FROM `handlerSubject` WHERE handlerId = :handlerId");
        $stmt->bindParam(':handlerId', $handlerId);
        $stmt->execute();
    }

    private function fetchUserByRole(string $username, string $role): ?array {
        $query = match ($role) {
            'applicant' => "SELECT user.id, firstname, lastname, email, password, image, phone, institution.id AS institutionId, institution.name AS institutionName, education.id AS educationId, education.name AS educationName, applicant.id AS applicantId FROM `applicant` JOIN `user` ON user.id = applicant.userId JOIN `institution` ON user.institutionId = institution.id JOIN `education` ON applicant.educationId = education.id WHERE email = :email",
            'handler' => "SELECT user.id, firstname, lastname, email, password, image, phone, institution.id AS institutionId, institution.name AS institutionName, handler.id AS handlerId, GROUP_CONCAT(DISTINCT CONCAT(typeOfLaw.id, ':', typeOfLaw.description)) AS typeOfLaws, GROUP_CONCAT(DISTINCT CONCAT(subject.id, ':', subject.description)) AS subjects FROM `handler` JOIN `user` ON user.id = handler.userId JOIN `institution` ON user.institutionId = institution.id LEFT JOIN `handlerTypeOfLow` ON handler.id = handlerTypeOfLow.handlerId LEFT JOIN `typeOfLaw` ON handlerTypeOfLow.typeOfLawId = typeOfLaw.id LEFT JOIN `handlerSubject` ON handler.id = handlerSubject.handlerId LEFT JOIN `subject` ON handlerSubject.subjectId = subject.id WHERE email = :email GROUP BY user.id",
            default => "SELECT user.id, firstname, lastname, email, password, image, phone, institution.id AS institutionId, institution.name AS institutionName FROM `user` JOIN `institution` ON user.institutionId = institution.id WHERE email = :email AND user.id NOT IN (SELECT userId FROM `handler` UNION SELECT userId FROM `applicant`)"
        };

        $stmt = $this->connection->prepare($query);
        $stmt->execute([':email' => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result !== false ? $result : null;
    }

    private function fetchUserDetails(array $row): User|Applicant|Handler {
        $institution = new Institution($row['institutionId'], $row['institutionName']);
        $image = $row['image'] ?? null;

        $stmt = $this->connection->prepare("SELECT a.id AS applicantId, e.id AS educationId, e.name AS educationName FROM `applicant` a JOIN `education` e ON a.educationId = e.id WHERE a.userId = :userId");
        $stmt->bindParam(':userId', $row['id']);
        $stmt->execute();
        $applicantRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($applicantRow) {
            $education = new Education($applicantRow['educationId'], $applicantRow['educationName']);
            return new Applicant($row['id'], $row['firstname'], $row['lastname'], $row['email'], null, $institution, $image, $row['phone'], $applicantRow['applicantId'], $education);
        }

        $stmt = $this->connection->prepare("SELECT h.id AS handlerId, GROUP_CONCAT(DISTINCT CONCAT(t.id, ':', t.description)) AS typeOfLaws, GROUP_CONCAT(DISTINCT CONCAT(s.id, ':', s.description)) AS subjects FROM `handler` h LEFT JOIN `handlerTypeOfLow` htl ON h.id = htl.handlerId LEFT JOIN `typeOfLaw` t ON htl.typeOfLawId = t.id LEFT JOIN `handlerSubject` hs ON h.id = hs.handlerId LEFT JOIN `subject` s ON hs.subjectId = s.id WHERE h.userId = :userId GROUP BY h.id");
        $stmt->bindParam(':userId', $row['id']);
        $stmt->execute();
        $handlerRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($handlerRow) {
            $typeOfLaws = [];
            foreach (explode(',', $handlerRow['typeOfLaws']) as $typeOfLaw) {
                list($id, $description) = explode(':', $typeOfLaw);
                $typeOfLaws[] = new TypeOfLaw($id, TypeOfLow::fromDatabase($description));
            }
            $subjects = [];
            foreach (explode(',', $handlerRow['subjects']) as $subject) {
                list($id, $description) = explode(':', $subject);
                $subjects[] = new Subject($id, $description);
            }
            return new Handler($row['id'], $row['firstname'], $row['lastname'], $row['email'], null, $institution, $image, $row['phone'], $handlerRow['handlerId'], $typeOfLaws, $subjects);
        }

        return new User($row['id'], $row['firstname'], $row['lastname'], $row['email'], null, $institution, $image, $row['phone']);
    }
}