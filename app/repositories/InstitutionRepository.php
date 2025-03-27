<?php
namespace App\Repositories;

use App\Models\Institution;
use PDO;
use PDOException;

class InstitutionRepository extends Repository {
    public function getAll($offset = null, $limit = null): array {
        try {
            $query = "SELECT id, name FROM institution";

            if (isset($limit) && isset($offset)) {
                $query .= " LIMIT :limit OFFSET :offset ";
            }
            $stmt = $this->connection->prepare($query);
            if (isset($limit) && isset($offset)) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }

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