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
        $offset = null;
        $limit = null;

        if (isset($_GET["offset"]) && is_numeric($_GET["offset"])) {
            $offset = $_GET["offset"];
        }
        if (isset($_GET["limit"]) && is_numeric($_GET["limit"])) {
            $limit = $_GET["limit"];
        }

        $educations = null;
        try {
            $educations = $this->educationService->getAll($offset, $limit);
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