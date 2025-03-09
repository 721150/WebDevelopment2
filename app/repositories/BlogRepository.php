<?php
namespace App\Repositories;

use App\Models\Education;
use App\Models\Enums\TypeOfLow;
use App\Models\Institution;
use App\Models\Reactie;
use App\Models\Subject;
use App\Models\TypeOfLaw;
use PDO;
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
}
?>