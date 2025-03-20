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
        $stmt = $this->connection->prepare("SELECT c.id, u.id AS userId, u.firstname, u.lastname, u.email, u.institutionId, u.image, u.phone, a.id AS applicantId, a.educationId, s.id AS subjectId, s.description AS subject, t.id AS typeOfLawId, t.description AS typeOfLaw, c.content, st.description AS status, i.name AS institution, e.name AS education, d.document FROM `case` c JOIN `user` u ON c.userId = u.id JOIN `applicant` a ON u.id = a.userId JOIN `subject` s ON c.subjectId = s.id JOIN `typeOfLaw` t ON c.typeOfLawId = t.id JOIN `status` st ON c.statusId = st.id JOIN `institution` i ON c.institutionId = i.id JOIN `education` e ON c.educationId = e.id LEFT JOIN `document` d ON c.id = d.caseId;");
        $stmt->execute();

        $cases = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $documentsStmt = $this->connection->prepare("SELECT id, document FROM document WHERE caseId = :caseId");
            $documentsStmt->execute(['caseId' => $row['id']]);
            $documents = [];
            while ($documentRow = $documentsStmt->fetch(PDO::FETCH_ASSOC)) {
                $document = new Document(
                    $documentRow['id'],
                    base64_encode($row['document'])
                );
                $documents[] = $document;
            }

            $case = new CaseModel(
                $row['id'],
                new Applicant(
                    $row['userId'],
                    $row['firstname'],
                    $row['lastname'],
                    $row['email'],
                    new Institution($row['institutionId'], $row['institution']),
                    base64_encode($row['image']),
                    $row['phone'],
                    $row['applicantId'],
                    new Education($row['educationId'], $row['education'])
                ),
                new Subject($row['subjectId'], $row['subject']),
                new TypeOfLaw($row['typeOfLawId'], TypeOfLow::fromDatabase($row['typeOfLaw'])),
                $row['content'],
                Status::fromDatabase($row['status']),
                new Institution($row['institutionId'], $row['institution']),
                new Education($row['educationId'], $row['education']),
                $documents
            );
            $cases[] = $case;
        }

        return $cases;
    }

    public function getOne(int $id) {
        $stmt = $this->connection->prepare("SELECT c.id, u.id AS userId, u.firstname, u.lastname, u.email, u.institutionId, u.image, u.phone, a.id AS applicantId, a.educationId, s.id AS subjectId, s.description AS subject, t.id AS typeOfLawId, t.description AS typeOfLaw, c.content, st.description AS status, i.name AS institution, e.name AS education, d.document FROM `case` c JOIN `user` u ON c.userId = u.id JOIN `applicant` a ON u.id = a.userId JOIN `subject` s ON c.subjectId = s.id JOIN `typeOfLaw` t ON c.typeOfLawId = t.id JOIN `status` st ON c.statusId = st.id JOIN `institution` i ON c.institutionId = i.id JOIN `education` e ON c.educationId = e.id LEFT JOIN `document` d ON c.id = d.caseId WHERE c.id = :id");
        $stmt->execute(['id' => $id]);

        $case = null;
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $documentsStmt = $this->connection->prepare("SELECT id, document FROM document WHERE caseId = :caseId");
            $documentsStmt->execute(['caseId' => $row['id']]);
            $documents = [];
            while ($documentRow = $documentsStmt->fetch(PDO::FETCH_ASSOC)) {
                $document = new Document(
                    $documentRow['id'],
                    base64_encode($documentRow['document'])
                );
                $documents[] = $document;
            }

            $image = $row['image'] ? base64_encode($row['image']) : null;

            $case = new CaseModel(
                $row['id'],
                new Applicant(
                    $row['userId'],
                    $row['firstname'],
                    $row['lastname'],
                    $row['email'],
                    new Institution($row['institutionId'], $row['institution']),
                    $image,
                    $row['phone'],
                    $row['applicantId'],
                    new Education($row['educationId'], $row['education'])
                ),
                new Subject($row['subjectId'], $row['subject']),
                new TypeOfLaw($row['typeOfLawId'], TypeOfLow::fromDatabase($row['typeOfLaw'])),
                $row['content'],
                Status::fromDatabase($row['status']),
                new Institution($row['institutionId'], $row['institution']),
                new Education($row['educationId'], $row['education']),
                $documents
            );
        }

        return $case;
    }

    public function create(CaseModel $case) {
        $newCase = null;

        $this->connection->beginTransaction();

        try {

            $stmt = $this->connection->prepare("INSERT INTO `case` (userId, subjectId, typeOfLawId, content, statusId, institutionId, educationId) VALUES (:userId, :subjectId, :typeOfLawId, :content, :statusId, :institutionId, :educationId)");

            $userId = $case->getUser()->getUserId();
            $subjectId = $case->getSubject()->getId();
            $typeOfLawId = $case->getTypeOfLaw()->getId();
            $content = $case->getContent();
            $statusId = $this->getStatusIdByDescription($case->getStatus()->value);
            $institutionId = $case->getInstitution()->getId();
            $educationId = $case->getEducation()->getId();

            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':subjectId', $subjectId);
            $stmt->bindParam(':typeOfLawId', $typeOfLawId);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':statusId', $statusId);
            $stmt->bindParam(':institutionId', $institutionId);
            $stmt->bindParam(':educationId', $educationId);

            $stmt->execute();

            $caseId = $this->connection->lastInsertId();

            $documents = [];
            foreach ($case->getDocuments() as $document) {
                $stmt = $this->connection->prepare("INSERT INTO document (caseId, document) VALUES (:caseId, :document)");
                $docContent = $document->getDocument();

                $stmt->bindParam(':caseId', $caseId);
                $stmt->bindParam(':document', $docContent);

                $stmt->execute();

                $documentId = $this->connection->lastInsertId();
                $documents[] = new Document($documentId, $docContent);
            }

            $this->connection->commit();

            $newCase = new CaseModel($caseId, $case->getUser(), $case->getSubject(), $case->getTypeOfLaw(), $case->getContent(), $case->getStatus(), $case->getInstitution(), $case->getEducation(), $documents);
        } catch (PDOException $e) {
            $this->connection->rollBack();
            throw $e;
        }
        return $newCase;
    }

    private function getStatusIdByDescription(string $description): ?int
    {
        $stmt = $this->connection->prepare("SELECT id FROM status WHERE description = :description");
        $stmt->bindParam(':description', $description);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : null;
    }

    public function update(CaseModel $case) {
        $this->connection->beginTransaction();

        try {
            $stmt = $this->connection->prepare("UPDATE `case` SET userId = :userId, subjectId = :subjectId, typeOfLawId = :typeOfLawId, content = :content, statusId = :statusId, institutionId = :institutionId, educationId = :educationId WHERE id = :id");

            $userId = $case->getUser()->getUserId();
            $subjectId = $case->getSubject()->getId();
            $typeOfLawId = $case->getTypeOfLaw()->getId();
            $content = $case->getContent();
            $statusId = $this->getStatusIdByDescription($case->getStatus()->value);
            $institutionId = $case->getInstitution()->getId();
            $educationId = $case->getEducation()->getId();
            $id = $case->getId();

            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':subjectId', $subjectId);
            $stmt->bindParam(':typeOfLawId', $typeOfLawId);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':statusId', $statusId);
            $stmt->bindParam(':institutionId', $institutionId);
            $stmt->bindParam(':educationId', $educationId);
            $stmt->bindParam(':id', $id);

            $stmt->execute();

            $existingDocStmt = $this->connection->prepare("SELECT id FROM document WHERE caseId = :caseId");
            $existingDocStmt->execute(['caseId' => $id]);
            $existingDocIds = $existingDocStmt->fetchAll(PDO::FETCH_COLUMN, 0);

            $newDocIds = [];
            foreach ($case->getDocuments() as $document) {
                if ($document->getId() === null) {
                    $stmt = $this->connection->prepare("INSERT INTO document (caseId, document) VALUES (:caseId, :document)");
                    $docContent = $document->getDocument();
                    $stmt->bindParam(':caseId', $id);
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

            $docsToDelete = array_diff($existingDocIds, $newDocIds);
            if (!empty($docsToDelete)) {
                $deleteStmt = $this->connection->prepare("DELETE FROM document WHERE id = :id");
                foreach ($docsToDelete as $docId) {
                    $deleteStmt->execute(['id' => $docId]);
                }
            }

            $this->connection->commit();

            return $this->getOne($id);
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return null;
        }
    }
}