<?php
namespace App\Controllers;

use App\Services\EducationService;
use Exception;

class EducationController extends Controller {
    private EducationService $educationService;

    function __construct() {
        $this->educationService = new EducationService();
    }
    public function getAll(): void {
        $educations = null;
        try {
            $educations = $this->educationService->getAll();
        } catch (Exception $exception) {
            $this->respondWithError(500, "Educations not found " . $exception->getMessage());
        }

        if ($educations == null) {
            $this->respondWithError(404, "Educations not found");
            return;
        }

        $this->respond($educations);
    }
}
?>