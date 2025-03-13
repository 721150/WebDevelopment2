<?php
namespace App\Controllers;

use App\Services\SubjectService;

class SubjectController extends Controller {
    private $subjectService;

    function __construct() {
        $this->subjectService = new SubjectService();
    }
    public function getAll() {
        $users = $this->subjectService->getAll();

        if (!$users) {
            $this->respondWithError(404, "Subject not found");
            return;
        }

        $this->respond($users);
    }
}
?>