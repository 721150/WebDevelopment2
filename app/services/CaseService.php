<?php
namespace App\Services;

use App\Models\CaseModel;
use App\Repositories\CaseRepository;

class CaseService {
    private CaseRepository $caseRepository;

    public function __construct() {
        $this->caseRepository = new CaseRepository();
    }

    public function getAll() {
        return $this->caseRepository->getAll();
    }

    public function getOne(int $id): ?CaseModel {
        return $this->caseRepository->getOne($id);
    }

    public function create(CaseModel $case): CaseModel {
        return $this->caseRepository->create($case);
    }

    public function update(CaseModel $case): CaseModel {
        return $this->caseRepository->update($case);
    }

    public function getByUser(int $userId): array {
        return $this->caseRepository->getByUser($userId);
    }
}