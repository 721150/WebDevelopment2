<?php
namespace App\Services;

use App\Repositories\TypeOfLowRepository;

class TypeOfLawService {
    private TypeOfLowRepository $typeOfLowRepository;

    public function __construct() {
        $this->typeOfLowRepository = new TypeOfLowRepository();
    }

    public function getAll($offset = null, $limit = null): array {
        return $this->typeOfLowRepository->getAll($offset, $limit);
    }
}
?>