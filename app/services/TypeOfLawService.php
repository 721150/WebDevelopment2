<?php
namespace App\Services;

use App\Repositories\TypeOfLowRepository;

class TypeOfLawService {
    private $typeOfLowRepository;

    public function __construct() {
        $this->typeOfLowRepository = new TypeOfLowRepository();
    }

    public function getAll() {
        return $this->typeOfLowRepository->getAll();
    }
}
?>