<?php
namespace App\Controllers;

use App\Services\TypeOfLawService;

class TypeOfLowController extends Controller {
    private $typeOfLowService;

    function __construct() {
        $this->typeOfLowService = new TypeOfLawService();
    }
    public function getAll() {
        $users = $this->typeOfLowService->getAll();

        if (!$users) {
            $this->respondWithError(404, "Type Of Low not found");
            return;
        }

        $this->respond($users);
    }
}
?>