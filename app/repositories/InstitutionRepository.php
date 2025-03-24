<?php
namespace App\Repositories;

use App\Models\Institution;
use PDO;
use PDOException;

class InstitutionRepository extends Repository {
    public function getAll(): array {
        try {
            $stmt = $this->connection->prepare("SELECT id, name FROM institution");
            $stmt->execute();

            $institutions = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $institution = new Institution($row['id'], $row['name']);
                $institutions[] = $institution;
            }
        } catch (PDOException $exception) {
            throw $exception;
        }

        return $institutions;
    }
}
?>