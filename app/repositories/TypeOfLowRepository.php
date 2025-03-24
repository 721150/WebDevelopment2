<?php
namespace App\Repositories;

use App\Models\Enums\TypeOfLow;
use App\Models\TypeOfLaw;
use PDO;
use PDOException;

class TypeOfLowRepository extends Repository {
    public function getAll(): array {
        try {
            $stmt = $this->connection->prepare("SELECT id, description FROM typeOfLaw");
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