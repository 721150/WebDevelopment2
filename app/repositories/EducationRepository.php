<?php
namespace App\Repositories;

use App\Models\Education;
use PDO;

class EducationRepository extends Repository {
    public function getAll() {
        $stmt = $this->connection->prepare("SELECT id, name FROM education");
        $stmt->execute();

        $educations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $education = new Education($row['id'], $row['name']);
            $educations[] = $education;
        }

        return $educations;
    }
}
?>