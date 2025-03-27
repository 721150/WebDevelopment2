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
use App\Models\User;
use App\Services\CaseService;
use App\Services\UserService;
use Exception;
use Random\RandomException;

class CaseController extends Controller {
    private CaseService $caseService;
    private UserService $userService;

    function __construct() {
        $this->caseService = new CaseService();
        $this->userService = new UserService();
    }

    public function getAll(): void {
        $cases = [];
        try {
            $cases = $this->caseService->getAll();
        } catch (Exception $exception) {
            $this->respondWithError(500, "Cases not found " . $exception->getMessage());
        }

        if (empty($cases)) {
            $this->respondWithError(404, "Cases not found");
            return;
        }

        $this->respond($cases);
    }

    public function getOne($id): void {
        $case = null;
        try {
            $case = $this->caseService->getOne($id);
        } catch (Exception $exception) {
            $this->respondWithError(500, "Case not found " . $exception->getMessage());
        }

        if ($case == null) {
            $this->respondWithError(404, "Case not found");
            return;
        }

        $this->respond($case);
    }

    public function getByUser($userId): void {
        try {
            $cases = $this->caseService->getByUser($userId);
        } catch (Exception $exception) {
            $this->respondWithError(500, $exception->getMessage());
        }

        $this->respond($cases);
    }

    /**
     * @throws RandomException
     */
    public function create(): void {
        $requiredFields = ['user', 'subject', 'typeOfLaw', 'content'];
        $data = [];

        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field])) {
                $this->respondWithError(400, "Missing required field: $field");
                return;
            }
            $data[$field] = $_POST[$field];
        }

        $user = $this->userService->getOne($data['user']);
        $case = $this->createCaseFromData($data, $user);

        if (!empty($_FILES['document'])) {
            list($file, $filePath) = $this->handleDocuments();

            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $document = new Document(null, $filePath);
                $case->addDocument($document);
            } else {
                $this->respondWithError(500, "Failed to upload file");
                return;
            }
        }

        $createdCase = null;
        try {
            $createdCase = $this->caseService->create($case);
        } catch (Exception $exception) {
            $this->respondWithError(500, "Failed to create case " . $exception->getMessage());
        }

        if ($createdCase === null) {
            $this->respondWithError(500, "Failed to create case");
            return;
        }
        $this->respond($createdCase);
    }

    public function update($id): void {
        $data = $this->getRequestData();

        $requiredFields = ['user', 'subject', 'typeOfLaw', 'content', 'status'];
        if (!$this->validateRequiredFields($data, $requiredFields)) {
            return;
        }

        $user = $this->userService->getOne($data['user']);
        $case = $this->createCaseFromData($data, $user, $id);

        $updatedCase = null;
        try {
            $updatedCase = $this->caseService->update($case);
        } catch (Exception $exception) {
            $this->respondWithError(500, "Failed to update case " . $exception->getMessage());
        }

        if ($updatedCase === null) {
            $this->respondWithError(500, "Failed to update case");
            return;
        }
        $this->respond($updatedCase);
    }

    private function validateRequiredFields($data, $requiredFields): bool {
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $this->respondWithError(400, "Missing required fields");
                return false;
            }
        }
        return true;
    }

    private function createCaseFromData($data, $user, $id = null): CaseModel {
        $subject = new Subject($data['subject']['id'], $data['subject']['description']);
        $typeOfLaw = new TypeOfLaw($data['typeOfLaw']['id'], TypeOfLow::fromDatabase($data['typeOfLaw']['description']));
        $status = null;
        if (!empty($data['status'])) {
            $status = Status::fromDatabase($data['status']);
        } else {
            $status = Status::Open;
        }

        $documents = [];
        if (!empty($data['documents'])) {
            foreach ($data['documents'] as $doc) {
                $documents[] = new Document($doc['id'] ?? null, $doc['document']);
            }
        }

        return new CaseModel($id, $user, $subject, $typeOfLaw, $data['content'], $status, $user->getInstitution(), $user->getEducation(), $documents);
    }

    /**
     * @return array
     * @throws RandomException
     */
    public function handleDocuments(): array {
        $file = $_FILES['document'];
        $uploadDirectory = './documents/';

        // Genereer een willekeurige reeks van 10 tekens om een dubbele bestandsnaam te voorkomen
        $randomString = bin2hex(random_bytes(10));
        $fileName = pathinfo($file['name'], PATHINFO_FILENAME);
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = $fileName . '_' . $randomString . '.' . $fileExtension;
        $filePath = $uploadDirectory . $newFileName;
        return array($file, $filePath);
    }
}
?>