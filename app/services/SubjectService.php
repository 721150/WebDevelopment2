<?php
namespace App\Services;

use App\Repositories\SubjectRepository;

class SubjectService {
    private SubjectRepository $subjectRepository;

    public function __construct() {
        $this->subjectRepository = new SubjectRepository();
    }

    public function getAll(): array {
        return $this->subjectRepository->getAll();
    }
}
?>