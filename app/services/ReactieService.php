<?php
namespace App\Services;

use App\Models\Reactie;
use App\Repositories\ReactieRepository;

class ReactieService {
    private $reactieRepository;

    public function __construct() {
        $this->reactieRepository = new ReactieRepository();
    }

    public function create(Reactie $reactie, int $blogId) {
        return $this->reactieRepository->create($reactie, $blogId);
    }
}
?>