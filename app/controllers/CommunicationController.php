<?php
namespace App\Controllers;

use App\Models\Communication;
use App\Models\Handler;
use App\Services\CommunicationService;
use App\Services\UserService;
use Exception;

class CommunicationController extends Controller {
    private CommunicationService $communicationService;
    private UserService $userService;

    function __construct() {
        $this->communicationService = new CommunicationService();
        $this->userService = new UserService();
    }

    public function getOne(int $id): void {
        $token = $this->checkForJwt();
        if (!$token) {
            $this->respondWithError(401, "No token provided");
            return;
        }

        $communication = null;
        try {
            $communication = $this->communicationService->getOne($id);
        } catch (Exception $exception) {
            $this->respondWithError(500, "Communication not found " . $exception->getMessage());
        }

        $this->respond($communication);
    }

    public function create(): void {
        $token = $this->checkForJwt();
        if (!$token) {
            $this->respondWithError(401, "No token provided");
            return;
        }

        $data = $this->getRequestData();

        if (empty($data['handler'] || empty($data['content']) || empty($data['caseId']))) {
            $this->respondWithError(400, "Missing required fields");
        }

        $handler = $this->userService->getOne($data['handler']);
        $communication = new Communication(null, $handler, $data['content']);

        $newCommunication = null;
        try {
            $newCommunication = $this->communicationService->create($communication, $data['caseId']);
        } catch (Exception $exception) {
            $this->respondWithError(500, "Failed to create new case " . $exception->getMessage());
        }

        if ($newCommunication == null) {
            $this->respondWithError(500, "Failed to create new case");
        }

        $this->respond($newCommunication);
    }
}
?>