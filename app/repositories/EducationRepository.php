<?php
namespace App\Repositories;

use App\Models\Education;
use PDO;
use PDOException;

class EducationRepository extends Repository {
    public function getAll($offset = null, $limit = null): array {
        try {
            $query = "SELECT id, name FROM education";

            if (isset($limit) && isset($offset)) {
                $query .= " LIMIT :limit OFFSET :offset ";
            }
            $stmt = $this->connection->prepare($query);
            if (isset($limit) && isset($offset)) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }

            $stmt->execute();

            $educations = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $education = new Education($row['id'], $row['name']);
                $educations[] = $education;
            }
        } catch (PDOException $exception) {
            throw $exception;
        }

        return $educations;
    }
}
?>