<?php
namespace App\Controllers;

use App\Services\TypeOfLawService;
use Exception;

class TypeOfLowController extends Controller {
    private TypeOfLawService $typeOfLowService;

    function __construct() {
        $this->typeOfLowService = new TypeOfLawService();
    }
    public function getAll(): void {
        $typeOfLows = null;
        try {
            $typeOfLows = $this->typeOfLowService->getAll();
        } catch (Exception $exception) {
            $this->respondWithError(500, "Type Of Low not found " . $exception->getMessage());
        }

        if ($typeOfLows == null) {
            $this->respondWithError(404, "Type Of Low not found");
            return;
        }

        $this->respond($typeOfLows);
    }
}
?>