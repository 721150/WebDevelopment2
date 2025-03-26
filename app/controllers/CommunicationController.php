<?php
namespace App\Controllers;

use App\Services\CommunicationService;
use Exception;

class CommunicationController extends Controller {
    private $communicationService;

    function __construct() {
        $this->communicationService = new CommunicationService();
    }

    public function getOne(int $id): void {
        $case = null;
        try {
            $case = $this->communicationService->getOne($id);
        } catch (Exception $exception) {
            $this->respondWithError(500, "Communication not found " . $exception->getMessage());
        }

        $this->respond($case);
    }
}
?>