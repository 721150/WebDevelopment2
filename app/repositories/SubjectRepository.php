<?php
namespace App\Repositories;

use App\Models\Subject;
use PDO;

class SubjectRepository extends Repository {
    public function getAll() {
        $stmt = $this->connection->prepare("SELECT id, description FROM subject");
        $stmt->execute();

        $subjects = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $subject = new Subject($row['id'], $row['description']);
            $subjects[] = $subject;
        }

        return $subjects;
    }
}
?>