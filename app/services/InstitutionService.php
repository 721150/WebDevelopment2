<?php
namespace App\Services;

use App\Repositories\InstitutionRepository;

class InstitutionService {
    private $institutionRepository;

    public function __construct() {
        $this->institutionRepository = new InstitutionRepository();
    }

    public function getAll() {
        return $this->institutionRepository->getAll();
    }
}
?>