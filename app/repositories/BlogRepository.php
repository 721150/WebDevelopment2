<?php
namespace App\Repositories;

use Exception;
use PDO;
use App\Models\Education;
use App\Models\Enums\TypeOfLow;
use App\Models\Institution;
use App\Models\Reactie;
use App\Models\Subject;
use App\Models\TypeOfLaw;
use App\Models\Blog;

class BlogRepository extends Repository {

    public function getAll() {
        $stmt = $this->connection->prepare("SELECT b.id, b.dateTime, b.institutionId, b.educationId, b.subjectId, b.typeOfLawId, b.description, b.content, i.name as institutionName, e.name as educationName, s.description as subjectDescription, t.description as typeOfLawDescription FROM blog b JOIN institution i ON b.institutionId = i.id JOIN education e ON b.educationId = e.id JOIN subject s ON b.subjectId = s.id JOIN typeOfLaw t ON b.typeOfLawId = t.id");
        $stmt->execute();

        $blogs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $reactiesStmt = $this->connection->prepare("SELECT id, dateTime, content FROM reactie WHERE blogId = :blogId");
            $reactiesStmt->execute(['blogId' => $row['id']]);
            $reacties = [];
            while ($reactieRow = $reactiesStmt->fetch(PDO::FETCH_ASSOC)) {
                $reactie = new Reactie(
                    $reactieRow['id'],
                    $reactieRow['dateTime'],
                    $reactieRow['content']
                );
                $reacties[] = $reactie;
            }

            $blog = new Blog(
                $row['id'],
                $row['dateTime'],
                new Institution($row['institutionId'], $row['institutionName']),
                new Education($row['educationId'], $row['educationName']),
                new Subject($row['subjectId'], $row['subjectDescription']),
                new TypeOfLaw($row['typeOfLawId'], TypeOfLow::fromDatabase($row['typeOfLawDescription'])),
                $row['description'],
                $row['content'],
                $reacties
            );
            $blogs[] = $blog;
        }

        return $blogs;
    }

    public function getOne(int $id) {
        $stmt = $this->connection->prepare("SELECT b.id, b.dateTime, b.institutionId, b.educationId, b.subjectId, b.typeOfLawId, b.description, b.content, i.name as institutionName, e.name as educationName, s.description as subjectDescription, t.description as typeOfLawDescription FROM blog b JOIN institution i ON b.institutionId = i.id JOIN education e ON b.educationId = e.id JOIN subject s ON b.subjectId = s.id JOIN typeOfLaw t ON b.typeOfLawId = t.id WHERE b.id = :id");
        $stmt->execute(['id' => $id]);

        $blog = null;
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $reactiesStmt = $this->connection->prepare("SELECT id, dateTime, content FROM reactie WHERE blogId = :blogId");
            $reactiesStmt->execute(['blogId' => $row['id']]);
            $reacties = [];
            while ($reactieRow = $reactiesStmt->fetch(PDO::FETCH_ASSOC)) {
                $reactie = new Reactie(
                    $reactieRow['id'],
                    $reactieRow['dateTime'],
                    $reactieRow['content']
                );
                $reacties[] = $reactie;
            }

            $blog = new Blog(
                $row['id'],
                $row['dateTime'],
                new Institution($row['institutionId'], $row['institutionName']),
                new Education($row['educationId'], $row['educationName']),
                new Subject($row['subjectId'], $row['subjectDescription']),
                new TypeOfLaw($row['typeOfLawId'], TypeOfLow::fromDatabase($row['typeOfLawDescription'])),
                $row['description'],
                $row['content'],
                $reacties
            );
        }

        return $blog;
    }

    public function create(Blog $blog) {
        $stmt = $this->connection->prepare("INSERT INTO blog (dateTime, institutionId, educationId, subjectId, typeOfLawId, description, content) VALUES (:dateTime, :institutionId, :educationId, :subjectId, :typeOfLawId, :description, :content)");

        $dateTime = $blog->getDateTime();
        $institutionId = $blog->getInstitution()->getId();
        $educationId = $blog->getEducation()->getId();
        $subjectId = $blog->getSubject()->getId();
        $typeOfLawId = $blog->getTypeOfLaw()->getId();
        $description = $blog->getDescription();
        $content = $blog->getContent();

        $stmt->bindParam(':dateTime', $dateTime);
        $stmt->bindParam(':institutionId', $institutionId);
        $stmt->bindParam(':educationId', $educationId);
        $stmt->bindParam(':subjectId', $subjectId);
        $stmt->bindParam(':typeOfLawId', $typeOfLawId);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':content', $content);

        $stmt->execute();

        $generatedId = $this->connection->lastInsertId();

        $newBlog = new Blog($generatedId, $blog->getDateTime(), $blog->getInstitution(), $blog->getEducation(), $blog->getSubject(), $blog->getTypeOfLaw(), $blog->getDescription(), $blog->getContent(), $blog->getReacties());

        return $newBlog;
    }

    public function delete(int $id) {
        try {
            $stmtReacties = $this->connection->prepare("DELETE FROM reactie WHERE blogId = :blogId");
            $stmtReacties->bindParam(':blogId', $id);
            $stmtReacties->execute();

            $stmt = $this->connection->prepare("DELETE FROM blog WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function update(Blog $blog) {
        try {
            $this->connection->beginTransaction();

            $stmt = $this->connection->prepare("UPDATE blog SET institutionId = :institutionId, educationId = :educationId, subjectId = :subjectId, typeOfLawId = :typeOfLawId, description = :description, content = :content WHERE id = :id");

            $id = $blog->getId();
            $institutionId = $blog->getInstitution()->getId();
            $educationId = $blog->getEducation()->getId();
            $subjectId = $blog->getSubject()->getId();
            $typeOfLawId = $blog->getTypeOfLaw()->getId();
            $description = $blog->getDescription();
            $content = $blog->getContent();

            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':institutionId', $institutionId);
            $stmt->bindParam(':educationId', $educationId);
            $stmt->bindParam(':subjectId', $subjectId);
            $stmt->bindParam(':typeOfLawId', $typeOfLawId);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':content', $content);

            $stmt->execute();

            $stmtExistingReacties = $this->connection->prepare("SELECT id FROM reactie WHERE blogId = :blogId");
            $stmtExistingReacties->bindParam(':blogId', $id);
            $stmtExistingReacties->execute();
            $existingReacties = $stmtExistingReacties->fetchAll(PDO::FETCH_COLUMN, 0);

            $reactieIds = [];
            foreach ($blog->getReacties() as $reactie) {
                if ($reactie->getId() === null) {
                    $stmtReactie = $this->connection->prepare("INSERT INTO reactie (blogId, dateTime, content) VALUES (:blogId, :dateTime, :content)");

                    $reactieDateTime = $reactie->getDateTime();
                    $reactieContent = $reactie->getContent();

                    $stmtReactie->bindParam(':blogId', $id);
                    $stmtReactie->bindParam(':dateTime', $reactieDateTime);
                    $stmtReactie->bindParam(':content', $reactieContent);

                    $stmtReactie->execute();
                } else {
                    $stmtReactie = $this->connection->prepare("UPDATE reactie SET content = :content WHERE id = :id AND blogId = :blogId");

                    $reactieId = $reactie->getId();
                    $reactieContent = $reactie->getContent();

                    $stmtReactie->bindParam(':id', $reactieId);
                    $stmtReactie->bindParam(':content', $reactieContent);
                    $stmtReactie->bindParam(':blogId', $id);

                    $stmtReactie->execute();
                    $reactieIds[] = $reactieId;
                }
            }

            $reactiesToDelete = array_diff($existingReacties, $reactieIds);
            foreach ($reactiesToDelete as $reactieIdToDelete) {
                $stmtDeleteReactie = $this->connection->prepare("DELETE FROM reactie WHERE id = :id");
                $stmtDeleteReactie->bindParam(':id', $reactieIdToDelete);
                $stmtDeleteReactie->execute();
            }

            $this->connection->commit();

            return $this->getOne($id);
        } catch (Exception $e) {
            $this->connection->rollBack();
            return null;
        }
    }
}
?>