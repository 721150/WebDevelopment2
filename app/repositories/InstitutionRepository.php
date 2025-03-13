<?php
namespace App\Repositories;

use App\Models\Institution;
use PDO;

class InstitutionRepository extends Repository {
    public function getAll() {
        $stmt = $this->connection->prepare("SELECT id, name FROM institution");
        $stmt->execute();

        $institutions = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $institution = new Institution($row['id'], $row['name']);
            $institutions[] = $institution;
        }

        return $institutions;
    }
}
?>