<?php
namespace App\Repositories;

use PDO;
use App\Models\Education;
use App\Models\Enums\TypeOfLow;
use App\Models\Institution;
use App\Models\Reactie;
use App\Models\Subject;
use App\Models\TypeOfLaw;
use App\Models\Blog;
use PDOException;

class BlogRepository extends Repository {

    public function getAll(): array {
        try {
            $stmt = $this->connection->prepare("SELECT b.id, b.dateTime, b.institutionId, b.educationId, b.subjectId, b.typeOfLawId, b.description, b.content, i.name as institutionName, e.name as educationName, s.description as subjectDescription, t.description as typeOfLawDescription FROM blog b JOIN institution i ON b.institutionId = i.id JOIN education e ON b.educationId = e.id JOIN subject s ON b.subjectId = s.id JOIN typeOfLaw t ON b.typeOfLawId = t.id ORDER BY b.dateTime DESC");
            $stmt->execute();

            $blogs = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $blog = $this->getReacties($row);
                $blogs[] = $blog;
            }
        } catch (PDOException $exception) {
            throw $exception;
        }

        return $blogs;
    }

    public function getOne(int $id): ?Blog {
        try {
            $stmt = $this->connection->prepare("SELECT b.id, b.dateTime, b.institutionId, b.educationId, b.subjectId, b.typeOfLawId, b.description, b.content, i.name as institutionName, e.name as educationName, s.description as subjectDescription, t.description as typeOfLawDescription FROM blog b JOIN institution i ON b.institutionId = i.id JOIN education e ON b.educationId = e.id JOIN subject s ON b.subjectId = s.id JOIN typeOfLaw t ON b.typeOfLawId = t.id WHERE b.id = :id");
            $stmt->execute(['id' => $id]);

            $blog = null;
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $blog = $this->getReacties($row);
            }
        } catch (PDOException $exception) {
            throw $exception;
        }

        return $blog;
    }

    public function create(Blog $blog): Blog {
        try {
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
        } catch (PDOException $exception) {
            throw $exception;
        }

        return $newBlog;
    }

    /**
     * @param mixed $row
     * @return Blog
     */
    public function getReacties(mixed $row): Blog
    {
        try {
            $reactiesStmt = $this->connection->prepare("SELECT id, dateTime, content FROM reactie WHERE blogId = :blogId");
            $reactiesStmt->execute(['blogId' => $row['id']]);
            $reacties = [];
            while ($reactieRow = $reactiesStmt->fetch(PDO::FETCH_ASSOC)) {
                $reactie = new Reactie($reactieRow['id'], $reactieRow['dateTime'], $reactieRow['content']);
                $reacties[] = $reactie;
            }

            $blog = new Blog($row['id'], $row['dateTime'], new Institution($row['institutionId'], $row['institutionName']), new Education($row['educationId'], $row['educationName']), new Subject($row['subjectId'], $row['subjectDescription']), new TypeOfLaw($row['typeOfLawId'], TypeOfLow::fromDatabase($row['typeOfLawDescription'])), $row['description'], $row['content'], $reacties);
        } catch (PDOException $exception) {
            throw $exception;
        }

        return $blog;
    }
}
?>