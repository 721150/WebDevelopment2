<?php
namespace App\Services;

use App\Repositories\EducationRepository;

class EducationService {
    private $courceRepository;

    public function __construct() {
        $this->courceRepository = new EducationRepository();
    }

    public function getAll() {
        return $this->courceRepository->getAll();
    }
}
?>