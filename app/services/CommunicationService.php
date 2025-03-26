<?php
namespace App\Services;

use App\Models\Communication;
use App\Repositories\CommunicationRepository;

class CommunicationService {
    private $communicationRepository;

    public function __construct() {
        $this->communicationRepository = new CommunicationRepository();
    }

    public function getOne(int $id): Communication {
        return $this->communicationRepository->getOne($id);
    }
}
?>