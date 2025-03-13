<?php
namespace App\Controllers;

use App\Services\EducationService;

class EducationController extends Controller {
    private $courceService;

    function __construct() {
        $this->courceService = new EducationService();
    }
    public function getAll() {
        $users = $this->courceService->getAll();

        if (!$users) {
            $this->respondWithError(404, "Educations not found");
            return;
        }

        $this->respond($users);
    }
}
?>