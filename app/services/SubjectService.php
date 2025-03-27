<?php
namespace App\Services;

use App\Repositories\SubjectRepository;

class SubjectService {
    private SubjectRepository $subjectRepository;

    public function __construct() {
        $this->subjectRepository = new SubjectRepository();
    }

    public function getAll($offset = null, $limit = null): array {
        return $this->subjectRepository->getAll($offset, $limit);
    }
}
?>