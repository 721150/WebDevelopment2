<?php
namespace App\Controllers;

use App\Services\SubjectService;
use Exception;

class SubjectController extends Controller {
    private SubjectService $subjectService;

    function __construct() {
        $this->subjectService = new SubjectService();
    }
    public function getAll(): void {
        $subjects = null;
        try {
            $subjects = $this->subjectService->getAll();
        } catch (Exception $exception) {
            $this->respondWithError(500, "Subject not found " . $exception->getMessage());
        }

        if ($subjects == null) {
            $this->respondWithError(404, "Subject not found");
            return;
        }

        $this->respond($subjects);
    }
}
?>