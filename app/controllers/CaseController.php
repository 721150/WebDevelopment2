<?php
namespace App\Controllers;

use App\Services\CaseService;

class CaseController extends Controller {
    private $caseService;

    function __construct() {
        $this->caseService = new CaseService();
    }
    public function getAll() {
        $cases = $this->caseService->getAll();

        if (!$cases) {
            $this->respondWithError(404, "Cases not found");
            return;
        }

        $this->respond($cases);
    }

    public function getOne($id) {
        $case = $this->caseService->getOne($id);

        if (!$case) {
            $this->respondWithError(404, "Case not found");
            return;
        }

        $this->respond($case);
    }
}
?>