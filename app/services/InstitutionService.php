<?php
namespace App\Services;

use App\Repositories\InstitutionRepository;

class InstitutionService {
    private InstitutionRepository $institutionRepository;

    public function __construct() {
        $this->institutionRepository = new InstitutionRepository();
    }

    public function getAll(): array {
        return $this->institutionRepository->getAll();
    }
}
?>