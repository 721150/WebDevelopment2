<?php
namespace App\Controllers;

use App\Models\Applicant;
use App\Models\CaseModel;
use App\Models\Document;
use App\Models\Education;
use App\Models\Enums\Status;
use App\Models\Enums\TypeOfLow;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\TypeOfLaw;
use App\Services\CaseService;

class CaseController extends Controller {
    private $caseService;

    function __construct() {
        $this->caseService = new CaseService();
    }
    public function getAll() {
        $cases = $this->caseService->getAll();

        if (!$cases) {
            $this->respondWithError(404, "Cases not found");
            return;
        }

        $this->respond($cases);
    }

    public function getOne($id) {
        $case = $this->caseService->getOne($id);

        if (!$case) {
            $this->respondWithError(404, "Case not found");
            return;
        }

        $this->respond($case);
    }

    public function create() {
        $data = $this->getRequestData();

        if (empty($data['user']) || empty($data['subject']) || empty($data['typeOfLaw']) || empty($data['content']) || empty($data['status']) || empty($data['institution']) || empty($data['education'])) {
            $this->respondWithError(400, "Missing required fields");
            return;
        }

        $institution = new Institution($data['institution']['id'], $data['institution']['name']);
        $education = new Education($data['education']['id'], $data['education']['name']);
        $image = $data['user']['image'] ?? null;
        $user = new Applicant($data['user']['id'], $data['user']['firstname'], $data['user']['lastname'], $data['user']['email'], $institution, $image, $data['user']['phone'], $data['user']['userId'], $education);
        $subject = new Subject($data['subject']['id'], $data['subject']['description']);
        $typeOfLaw = new TypeOfLaw($data['typeOfLaw']['id'], TypeOfLow::fromDatabase($data['typeOfLaw']['description']));
        $status = Status::fromDatabase($data['status']);


        $documents = [];
        if (!empty($data['documents'])) {
            foreach ($data['documents'] as $doc) {
                $documents[] = new Document(null, $doc['document']);
            }
        }

        $case = new CaseModel(
            null, // Zal door database worden gemaakt
            $user,
            $subject,
            $typeOfLaw,
            $data['content'],
            $status,
            $institution,
            $education,
            $documents
        );

        $createdCase = $this->caseService->create($case);

        if (!$createdCase) {
            $this->respondWithError(500, "Failed to create case");
            return;
        }

        $this->respond($createdCase);
    }

    public function update($id) {
        $data = $this->getRequestData();

        if (empty($data['user']) || empty($data['subject']) || empty($data['typeOfLaw']) || empty($data['content']) || empty($data['status']) || empty($data['institution']) || empty($data['education'])) {
            $this->respondWithError(400, "Missing required fields");
            return;
        }

        $institution = new Institution($data['institution']['id'], $data['institution']['name']);
        $education = new Education($data['education']['id'], $data['education']['name']);
        $image = $data['user']['image'] ?? null;
        $user = new Applicant(
            $data['user']['id'],
            $data['user']['firstname'],
            $data['user']['lastname'],
            $data['user']['email'],
            $institution,
            $image,
            $data['user']['phone'],
            $data['user']['userId'],
            $education
        );

        $subject = new Subject($data['subject']['id'], $data['subject']['description']);
        $typeOfLaw = new TypeOfLaw($data['typeOfLaw']['id'], TypeOfLow::fromDatabase($data['typeOfLaw']['description']));
        $status = Status::fromDatabase($data['status']);

        $documents = [];
        if (!empty($data['documents'])) {
            foreach ($data['documents'] as $doc) {
                $documents[] = new Document($doc['id'] ?? null, $doc['document']);
            }
        }

        $case = new CaseModel(
            $id,
            $user,
            $subject,
            $typeOfLaw,
            $data['content'],
            $status,
            $institution,
            $education,
            $documents
        );

        $updatedCase = $this->caseService->update($case);

        if (!$updatedCase) {
            $this->respondWithError(500, "Failed to update case");
            return;
        }

        $this->respond($updatedCase);
    }
}
?>