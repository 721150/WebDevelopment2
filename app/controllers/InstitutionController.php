<?php
namespace App\Controllers;

use App\Services\InstitutionService;

class InstitutionController extends Controller {
    private $institutionService;

    function __construct() {
        $this->institutionService = new InstitutionService();
    }
    public function getAll() {
        $users = $this->institutionService->getAll();

        if (!$users) {
            $this->respondWithError(404, "Institution not found");
            return;
        }

        $this->respond($users);
    }
}
?>