<?php
namespace App\Services;

use App\Models\CaseModel;
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

    public function create(CaseModel $case) {
        return $this->caseRepository->create($case);
    }

    public function update(CaseModel $case) {
        return $this->caseRepository->update($case);
    }

    public function getByUser(int $userId) {
        return $this->caseRepository->getByUser($userId);
    }
}