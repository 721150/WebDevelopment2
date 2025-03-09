<?php
namespace App\Services;

use App\Repositories\CaseRepository;

class CaseService {
    private $caseRepository;

    public function __construct() {
        $this->caseRepository = new CaseRepository();
    }

    public function getAll() {
        return $this->caseRepository->getAll();
    }

    public function getOne(int $id) {
        return $this->caseRepository->getOne($id);
    }
}