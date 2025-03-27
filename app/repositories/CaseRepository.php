<?php
namespace App\Repositories;

use PDO;
use App\Models\Enums\TypeOfLow;
use App\Models\Applicant;
use App\Models\CaseModel;
use App\Models\Document;
use App\Models\Enums\Status;
use App\Models\Education;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\TypeOfLaw;
use PDOException;

class CaseRepository extends Repository {
    public function getAll() {
        $cases = [];

        try {
            $stmt = $this->connection->prepare("SELECT c.id, u.id AS userId, u.firstname, u.lastname, u.email, u.institutionId, u.image, u.phone, a.id AS applicantId, a.educationId, s.id AS subjectId, s.description AS subject, t.id AS typeOfLawId, t.description AS typeOfLaw, c.content, st.description AS status, i.name AS institution, e.name AS education, d.document FROM `case` c JOIN `user` u ON c.userId = u.id JOIN `applicant` a ON u.id = a.userId JOIN `subject` s ON c.subjectId = s.id JOIN `typeOfLaw` t ON c.typeOfLawId = t.id JOIN `status` st ON c.statusId = st.id JOIN `institution` i ON c.institutionId = i.id JOIN `education` e ON c.educationId = e.id LEFT JOIN `document` d ON c.id = d.caseId");
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $documentsStmt = $this->connection->prepare("SELECT id, document FROM document WHERE caseId = :caseId");
                $documentsStmt->execute(['caseId' => $row['id']]);
                $documents = [];
                while ($documentRow = $documentsStmt->fetch(PDO::FETCH_ASSOC)) {
                    $document = new Document($documentRow['id'], $documentRow['document']);
                    $documents[] = $document;
                }

                $image = $row['image'] ? base64_encode($row['image']) : null;

                $case = new CaseModel($row['id'], new Applicant($row['userId'], $row['firstname'], $row['lastname'], $row['email'], null, new Institution($row['institutionId'], $row['institution']), $image, $row['phone'], $row['applicantId'], new Education($row['educationId'], $row['education'])), new Subject($row['subjectId'], $row['subject']), new TypeOfLaw($row['typeOfLawId'], TypeOfLow::fromDatabase($row['typeOfLaw'])), $row['content'], Status::fromDatabase($row['status']), new Institution($row['institutionId'], $row['institution']), new Education($row['educationId'], $row['education']), $documents);
                $cases[] = $case;
            }
        } catch (PDOException $exception) {
            throw $exception;
        }

        return $cases;
    }

    public function getOne(int $id): ?CaseModel {
        try {
            $stmt = $this->connection->prepare("SELECT c.id, u.id AS userId, u.firstname, u.lastname, u.email, u.institutionId, u.image, u.phone, a.id AS applicantId, a.educationId, s.id AS subjectId, s.description AS subject, t.id AS typeOfLawId, t.description AS typeOfLaw, c.content, st.description AS status, i.name AS institution, e.name AS education, d.document FROM `case` c JOIN `user` u ON c.userId = u.id JOIN `applicant` a ON u.id = a.userId JOIN `subject` s ON c.subjectId = s.id JOIN `typeOfLaw` t ON c.typeOfLawId = t.id JOIN `status` st ON c.statusId = st.id JOIN `institution` i ON c.institutionId = i.id JOIN `education` e ON c.educationId = e.id LEFT JOIN `document` d ON c.id = d.caseId WHERE c.id = :id");
            $stmt->execute(['id' => $id]);

            $case = null;
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $documentsStmt = $this->connection->prepare("SELECT id, document FROM document WHERE caseId = :caseId");
                $documentsStmt->execute(['caseId' => $row['id']]);
                $documents = [];
                while ($documentRow = $documentsStmt->fetch(PDO::FETCH_ASSOC)) {
                    $document = new Document($documentRow['id'], $documentRow['document']);
                    $documents[] = $document;
                }

                $image = $row['image'] ? base64_encode($row['image']) : null;

                $case = new CaseModel($row['id'], new Applicant($row['userId'], $row['firstname'], $row['lastname'], $row['email'], null, new Institution($row['institutionId'], $row['institution']), $image, $row['phone'], $row['applicantId'], new Education($row['educationId'], $row['education'])), new Subject($row['subjectId'], $row['subject']), new TypeOfLaw($row['typeOfLawId'], TypeOfLow::fromDatabase($row['typeOfLaw'])), $row['content'], Status::fromDatabase($row['status']), new Institution($row['institutionId'], $row['institution']), new Education($row['educationId'], $row['education']), $documents);
            }
        } catch (PDOException $exception) {
            throw $exception;
        }

        return $case;
    }

    public function create(CaseModel $case): CaseModel {
        $newCase = null;

        try {
            $this->executeTransaction(function () use ($case, &$newCase) {
                $stmt = $this->connection->prepare("INSERT INTO `case` (userId, subjectId, typeOfLawId, content, statusId, institutionId, educationId) VALUES (:userId, :subjectId, :typeOfLawId, :content, :statusId, :institutionId, :educationId)");

                list($userId, $subjectId, $typeOfLawId, $content, $statusId, $institutionId, $educationId) = $this->takeData($case);
                $this->bindCaseParams($stmt, compact('userId', 'subjectId', 'typeOfLawId', 'content', 'statusId', 'institutionId', 'educationId'));

                $stmt->execute();

                $caseId = $this->connection->lastInsertId();
                $documents = $this->processDocuments($caseId, $case->getDocuments());

                $newCase = new CaseModel($caseId, $case->getUser(), $case->getSubject(), $case->getTypeOfLaw(), $case->getContent(), $case->getStatus(), $case->getInstitution(), $case->getEducation(), $documents);
            });
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            throw $exception;
        }

        return $newCase;
    }

    private function getStatusIdByDescription(string $description): ?int {
        try {
            $stmt = $this->connection->prepare("SELECT id FROM status WHERE description = :description");
            $stmt->bindParam(':description', $description);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['id'] : null;
        } catch (PDOException $exception) {
            throw $exception;
        }
    }

    public function update(CaseModel $case): CaseModel {
        try {
            $this->executeTransaction(function () use ($case) {
                $stmt = $this->connection->prepare("UPDATE `case` SET userId = :userId, subjectId = :subjectId, typeOfLawId = :typeOfLawId, content = :content, statusId = :statusId, institutionId = :institutionId, educationId = :educationId WHERE id = :id");

                list($userId, $subjectId, $typeOfLawId, $content, $statusId, $institutionId, $educationId) = $this->takeData($case);
                $id = $case->getId();
                $this->bindCaseParams($stmt, compact('userId', 'subjectId', 'typeOfLawId', 'content', 'statusId', 'institutionId', 'educationId'));
                $stmt->bindParam(':id', $id);

                $stmt->execute();

                $existingDocStmt = $this->connection->prepare("SELECT id FROM document WHERE caseId = :caseId");
                $existingDocStmt->execute(['caseId' => $id]);
                $existingDocIds = $existingDocStmt->fetchAll(PDO::FETCH_COLUMN, 0);

                $newDocIds = $this->processNewAndExistingDocuments($id, $case->getDocuments());

                $docsToDelete = array_diff($existingDocIds, $newDocIds);
                $this->deleteDocuments($docsToDelete);
            });
        } catch (PDOException $exception) {
            throw $exception;
        }

        return $this->getOne($case->getId());
    }

    public function getByUser(int $userId): array {
        try {
            $stmt = $this->connection->prepare("SELECT c.id, u.id AS userId, u.firstname, u.lastname, u.email, u.institutionId, u.image, u.phone, a.id AS applicantId, a.educationId, s.id AS subjectId, s.description AS subject, t.id AS typeOfLawId, t.description AS typeOfLaw, c.content, st.description AS status, i.name AS institution, e.name AS education FROM `case` c JOIN `user` u ON c.userId = u.id JOIN `applicant` a ON u.id = a.userId JOIN `subject` s ON c.subjectId = s.id JOIN `typeOfLaw` t ON c.typeOfLawId = t.id JOIN `status` st ON c.statusId = st.id JOIN `institution` i ON c.institutionId = i.id JOIN `education` e ON c.educationId = e.id WHERE c.userId = :userId");
            $stmt->execute(['userId' => $userId]);

            $cases = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $documentsStmt = $this->connection->prepare("SELECT id, document FROM document WHERE caseId = :caseId");
                $documentsStmt->execute(['caseId' => $row['id']]);
                $documents = [];
                while ($documentRow = $documentsStmt->fetch(PDO::FETCH_ASSOC)) {
                    $document = new Document($documentRow['id'], $documentRow['document']);
                    $documents[] = $document;
                }

                $image = $row['image'] ? base64_encode($row['image']) : null;

                $case = new CaseModel($row['id'], new Applicant($row['userId'], $row['firstname'], $row['lastname'], $row['email'], null, new Institution($row['institutionId'], $row['institution']), $image, $row['phone'], $row['applicantId'], new Education($row['educationId'], $row['education'])), new Subject($row['subjectId'], $row['subject']), new TypeOfLaw($row['typeOfLawId'], TypeOfLow::fromDatabase($row['typeOfLaw'])), $row['content'], Status::fromDatabase($row['status']), new Institution($row['institutionId'], $row['institution']), new Education($row['educationId'], $row['education']), $documents);

                $cases[] = $case;
            }
        } catch (PDOException $exception) {
            throw $exception;
        }

        return $cases;
    }

    /**
     * @param CaseModel $case
     * @return array
     */
    public function takeData(CaseModel $case): array
    {
        $userId = $case->getUser()->getId();
        $subjectId = $case->getSubject()->getId();
        $typeOfLawId = $case->getTypeOfLaw()->getId();
        $content = $case->getContent();
        $statusId = $this->getStatusIdByDescription($case->getStatus()->value);
        $institutionId = $case->getInstitution()->getId();
        $educationId = $case->getEducation()->getId();
        return array($userId, $subjectId, $typeOfLawId, $content, $statusId, $institutionId, $educationId);
    }

    private function bindCaseParams($stmt, $data): void {
        $stmt->bindParam(':userId', $data['userId']);
        $stmt->bindParam(':subjectId', $data['subjectId']);
        $stmt->bindParam(':typeOfLawId', $data['typeOfLawId']);
        $stmt->bindParam(':content', $data['content']);
        $stmt->bindParam(':statusId', $data['statusId']);
        $stmt->bindParam(':institutionId', $data['institutionId']);
        $stmt->bindParam(':educationId', $data['educationId']);
    }

    private function processDocuments($caseId, $documents): array {
        $processedDocuments = [];
        try {
            foreach ($documents as $document) {
                $stmt = $this->connection->prepare("INSERT INTO document (caseId, document) VALUES (:caseId, :document)");
                $docContent = $document->getDocument();

                $stmt->bindParam(':caseId', $caseId);
                $stmt->bindParam(':document', $docContent);

                $stmt->execute();

                $documentId = $this->connection->lastInsertId();
                $processedDocuments[] = new Document($documentId, $docContent);
            }
        } catch (PDOException $exception) {
            throw $exception;
        }
        return $processedDocuments;
    }

    private function executeTransaction(callable $transaction): void {
        $this->connection->beginTransaction();
        try {
            $transaction();
            $this->connection->commit();
        } catch (PDOException $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }

    private function processNewAndExistingDocuments($caseId, $documents): array {
        $newDocIds = [];
        try {
            foreach ($documents as $document) {
                if ($document->getId() === null) {
                    $stmt = $this->connection->prepare("INSERT INTO document (caseId, document) VALUES (:caseId, :document)");
                    $docContent = $document->getDocument();
                    $stmt->bindParam(':caseId', $caseId);
                    $stmt->bindParam(':document', $docContent);
                    $stmt->execute();
                    $newDocIds[] = $this->connection->lastInsertId();
                } else {
                    $stmt = $this->connection->prepare("UPDATE document SET document = :document WHERE id = :id");
                    $docContent = $document->getDocument();
                    $docId = $document->getId();
                    $stmt->bindParam(':document', $docContent);
                    $stmt->bindParam(':id', $docId);
                    $stmt->execute();
                    $newDocIds[] = $docId;
                }
            }
        } catch (PDOException $exception) {
            throw $exception;
        }
        return $newDocIds;
    }

    private function deleteDocuments($docsToDelete): void {
        if (!empty($docsToDelete)) {
            try {
                $deleteStmt = $this->connection->prepare("DELETE FROM document WHERE id = :id");
                foreach ($docsToDelete as $docId) {
                    $deleteStmt->execute(['id' => $docId]);
                }
            } catch (PDOException $exception) {
                throw $exception;
            }
        }
    }
}