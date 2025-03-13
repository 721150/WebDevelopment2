<?php
namespace App\Services;

use App\Repositories\SubjectRepository;

class SubjectService {
    private $subjectRepository;

    public function __construct() {
        $this->subjectRepository = new SubjectRepository();
    }

    public function getAll() {
        return $this->subjectRepository->getAll();
    }
}
?>