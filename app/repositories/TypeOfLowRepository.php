<?php
namespace App\Repositories;

use App\Models\Enums\TypeOfLow;
use App\Models\TypeOfLaw;
use PDO;
use PDOException;

class TypeOfLowRepository extends Repository {
    public function getAll($offset = null, $limit = null): array {
        try {
            $query = "SELECT id, description FROM typeOfLaw";

            if (isset($limit) && isset($offset)) {
                $query .= " LIMIT :limit OFFSET :offset ";
            }
            $stmt = $this->connection->prepare($query);
            if (isset($limit) && isset($offset)) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }

            $stmt->execute();

            $typesOfLaw = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $typeOfLaw = new TypeOfLaw($row['id'], TypeOfLow::fromDatabase($row['description']));
                $typesOfLaw[] = $typeOfLaw;
            }
        } catch (PDOException $exception) {
            throw $exception;
        }

        return $typesOfLaw;
    }
}
?>