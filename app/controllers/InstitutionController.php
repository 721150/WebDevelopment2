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
        $institutions = null;
        try {
            $institutions = $this->institutionService->getAll();
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