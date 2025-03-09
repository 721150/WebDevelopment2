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

    }
}