<?php
namespace App\Controllers;

use App\Models\Reactie;
use App\Services\ReactieService;
use DateTime;
use Exception;

class ReactieController extends Controller {
    private ReactieService $reactieService;

    function __construct() {
        $this->reactieService = new ReactieService();
    }
    public function create(): void {
        $data = $this->getRequestData();

        if (empty($data['blogId']) || empty($data['reactie'])) {
            $this->respondWithError(400, "Missing required fields");
            return;
        }

        $currentDateTime = (new DateTime())->format('Y-m-d H:i:s');

        $reactie = new Reactie(null, $currentDateTime, $data['reactie']['content']);

        $createdReactie = null;
        try {
            $createdReactie = $this->reactieService->create($reactie, $data['blogId']);
        } catch (Exception $exception) {
            $this->respondWithError(500, "Failed to create blog " . $exception->getMessage());
        }

        if ($createdReactie == null) {
            $this->respondWithError(500, "Failed to create blog");
            return;
        }

        $this->respond($createdReactie);
    }
}
?>