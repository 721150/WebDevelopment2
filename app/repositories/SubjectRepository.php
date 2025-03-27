<?php
namespace App\Repositories;

use App\Models\Subject;
use PDO;
use PDOException;

class SubjectRepository extends Repository {
    public function getAll($offset = null, $limit = null): array {
        try {
            $query = "SELECT id, description FROM subject";

            if (isset($limit) && isset($offset)) {
                $query .= " LIMIT :limit OFFSET :offset ";
            }
            $stmt = $this->connection->prepare($query);
            if (isset($limit) && isset($offset)) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }

            $stmt->execute();

            $subjects = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $subject = new Subject($row['id'], $row['description']);
                $subjects[] = $subject;
            }
        } catch (PDOException $exception) {
            throw $exception;
        }

        return $subjects;
    }
}
?>