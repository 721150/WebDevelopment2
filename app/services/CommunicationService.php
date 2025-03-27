<?php
namespace App\Services;

use App\Models\Communication;
use App\Repositories\CommunicationRepository;

class CommunicationService {
    private CommunicationRepository $communicationRepository;

    public function __construct() {
        $this->communicationRepository = new CommunicationRepository();
    }

    public function getOne(int $id): Communication {
        return $this->communicationRepository->getOne($id);
    }

    public function create(Communication $communication, int $userId): Communication {
        return $this->communicationRepository->create($communication, $userId);
    }
}
?>