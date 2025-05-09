<?php
namespace App\Services;

use App\Repositories\EducationRepository;

class EducationService {
    private EducationRepository $educationRepository;

    public function __construct() {
        $this->educationRepository = new EducationRepository();
    }

    public function getAll($offset = null, $limit = null): array {
        return $this->educationRepository->getAll($offset, $limit);
    }
}
?>