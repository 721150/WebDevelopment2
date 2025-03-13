<?php
namespace App\Repositories;

use App\Models\Enums\TypeOfLow;
use App\Models\TypeOfLaw;
use PDO;

class TypeOfLowRepository extends Repository {
    public function getAll() {
        $stmt = $this->connection->prepare("SELECT id, description FROM typeOfLaw");
        $stmt->execute();

        $typesOfLaw = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $typeOfLaw = new TypeOfLaw($row['id'], TypeOfLow::fromDatabase($row['description']));
            $typesOfLaw[] = $typeOfLaw;
        }

        return $typesOfLaw;
    }
}
?>