<?php
namespace App\Repositories;

use App\Models\Education;
use PDO;
use PDOException;

class EducationRepository extends Repository {
    public function getAll(): array {
        try {
            $stmt = $this->connection->prepare("SELECT id, name FROM education");
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