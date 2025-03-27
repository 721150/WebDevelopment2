<?php
namespace App\Controllers;

use App\Services\InstitutionService;
use Exception;

class InstitutionController extends Controller {
    private InstitutionService $institutionService;

    function __construct() {
        $this->institutionService = new InstitutionService();
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

        $institutions = null;
        try {
            $institutions = $this->institutionService->getAll($offset, $limit);
        } catch (Exception $exception) {
            $this->respondWithError(500, "Institution not found " . $exception->getMessage());
        }

        if ($institutions == null) {
            $this->respondWithError(404, "Institution not found");
            return;
        }

        $this->respond($institutions);
    }
}
?>