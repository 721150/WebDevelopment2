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
        $offset = null;
        $limit = null;

        if (isset($_GET["offset"]) && is_numeric($_GET["offset"])) {
            $offset = $_GET["offset"];
        }
        if (isset($_GET["limit"]) && is_numeric($_GET["limit"])) {
            $limit = $_GET["limit"];
        }

        $subjects = null;
        try {
            $subjects = $this->subjectService->getAll($offset, $limit);
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