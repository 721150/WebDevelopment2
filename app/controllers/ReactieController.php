<?php
namespace App\Controllers;

use App\Models\Reactie;
use App\Services\ReactieService;
use DateTime;

class ReactieController extends Controller {
    private $reactieService;

    function __construct() {
        $this->reactieService = new ReactieService();
    }
    public function create()
    {
        $data = $this->getRequestData();

        if (empty($data['blogId']) || empty($data['reactie'])) {
            $this->respondWithError(400, "Missing required fields");
            return;
        }

        $currentDateTime = (new DateTime())->format('Y-m-d H:i:s');

        $reactie = new Reactie(null, $currentDateTime, $data['reactie']['content']);

        $createdReactie = $this->reactieService->create($reactie, $data['blogId']);

        if (!$createdReactie) {
            $this->respondWithError(500, "Failed to create blog");
            return;
        }

        $this->respond($createdReactie);
    }
}
?>