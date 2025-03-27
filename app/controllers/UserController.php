<?php
namespace App\Controllers;

use App\Models\Applicant;
use App\Models\Education;
use App\Models\Enums\TypeOfLow;
use App\Models\Handler;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\TypeOfLaw;
use App\Models\User;
use App\Services\JwtService;
use App\Services\UserService;
use Exception;

class UserController extends Controller {
    private UserService $userService;
    private JwtService $jwtService;

    function __construct() {
        $this->userService = new UserService();
        $this->jwtService = new JwtService();
    }

    public function login(): void {
        $logindata = $this->getRequestData();

        try {
            $user = $this->userService->login($logindata['username'], $logindata['password']);
        } catch (Exception $exception) {
            $this->respondWithError(500,"Failed to read user: " . $exception->getMessage());
            return;
        }

        if (!$user) {
            $this->respondWithError(401,"Username or password is incorrect");
            return;
        }

        $jwt = $this->jwtService->generateJwt($user);

        $this->respond($jwt);
    }

    public function getAll(): void {
        $offset = null;
        $limit = null;

        if (isset($_GET["offset"]) && is_numeric($_GET["offset"])) {
            $offset = $_GET["offset"];
        }
        if (isset($_GET["limit"]) && is_numeric($_GET["limit"])) {
            $limit = $_GET["limit"];
        }

        $users = null;
        try {
            $users = $this->userService->getAll($offset, $limit);
        } catch (Exception $exception) {
            $this->respondWithError(500,"Failed to read users: " . $exception->getMessage());
        }

        if ($users == null) {
            $this->respondWithError(404, "Users not found");
            return;
        }

        $this->respond($users);
    }

    public function getOne($id): void {
        try {
            $user = $this->userService->getOne($id);
        } catch (Exception $exception) {
            $this->respondWithError(500,"Failed to read user: " . $exception->getMessage());
            return;
        }

        if (!$user) {
            $this->respondWithError(404, "User not found");
            return;
        }

        $this->respond($user);
    }

    public function createAdmin(): void {
        $data = $this->getRequestData();
        $user = $this->createUser($data, 'admin');
        if ($user === null) return;

        try {
            $createdUser = $this->userService->createAdmin($user);
            if ($createdUser === null) {
                $this->respondWithError(500, "Failed to create user");
                return;
            }
            $this->respond($createdUser);
        } catch (Exception $exception) {
            $this->respondWithError(500, "Failed to create user: " . $exception->getMessage());
        }
    }

    public function createHandler(): void {
        $data = $this->getRequestData();
        $user = $this->createUser($data, 'handler');
        if ($user === null) return;

        try {
            $createdUser = $this->userService->createHandler($user);
            if ($createdUser === null) {
                $this->respondWithError(500, "Failed to create user");
                return;
            }
            $this->respond($createdUser);
        } catch (Exception $exception) {
            $this->respondWithError(500, "Failed to create user: " . $exception->getMessage());
        }
    }

    public function createApplicant(): void {
        $data = $this->getRequestData();
        $user = $this->createUser($data, 'applicant');
        if ($user === null) return;

        try {
            $createdUser = $this->userService->createApplicant($user);
            if ($createdUser === null) {
                $this->respondWithError(500, "Failed to create user");
                return;
            }
            $this->respond($createdUser);
        } catch (Exception $exception) {
            $this->respondWithError(500, "Failed to create user: " . $exception->getMessage());
        }
    }

    public function update($id): void {
        $data = $this->getRequestData();

        $requiredFields = ['id', 'firstname', 'lastname', 'email', 'institution', 'phone'];
        if (!$this->validateRequiredFields($data, $requiredFields)) {
            return;
        }

        $institution = $this->createInstitution($data);
        $image = $data['image'] ?? null;

        $user = $this->updateUser($id, $data, $institution, $image);
        if ($user === null) {
            return;
        }

        if ($image !== null) {
            list(, $image) = explode(',', $image);
            $user->setImage($image);
        }

        try {
            $updatedUser = $this->userService->update($user);
        } catch (Exception $exception) {
            $this->respondWithError(500, "Failed to update user: " . $exception->getMessage());
            return;
        }

        $this->respond($updatedUser);
    }

    public function delete($id): void {
        try {
            $deleted = $this->userService->delete($id);
        } catch (Exception $exception) {
            $this->respondWithError(500, "Failed to delete user: " . $exception->getMessage());
        }

        if (!$deleted) {
            $this->respondWithError(500, "Failed to delete user");
            return;
        }

        $this->respond(true);
    }

    private function createUser($data, $userType): ?User {
        $requiredFields = ['firstname', 'lastname', 'email', 'password', 'institution', 'phone'];
        if (!$this->validateRequiredFields($data, $requiredFields)) {
            return null;
        }

        $institution = $this->createInstitution($data);
        $image = $data['image'] ?? null;

        if ($userType === 'handler' && (empty($data['typeOfLaws']) || empty($data['subjects']))) {
            $this->respondWithError(400, "Missing required fields");
            return null;
        }

        if ($userType === 'applicant' && empty($data['education'])) {
            $this->respondWithError(400, "Missing required fields");
            return null;
        }

        $user = $this->createUserByType($data, $userType, $institution, $image);

        if ($image !== null) {
            $user->setImage($image);
        }

        return $user;
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

    private function createInstitution($data): Institution {
        return new Institution($data['institution']['id'], $data['institution']['name']);
    }

    private function createUserByType($data, $userType, $institution, $image): ?User {
        switch ($userType) {
            case 'admin':
                return new User(null, $data['firstname'], $data['lastname'], $data['email'], $data['password'], $institution, null, $data['phone']);
            case 'handler':
                $typeOfLaws = array_map(function($type) {
                    return new TypeOfLaw($type['id'], TypeOfLow::fromDatabase($type['description']));
                }, $data['typeOfLaws']);
                $subjects = array_map(function($subject) {
                    return new Subject($subject['id'], $subject['description']);
                }, $data['subjects']);
                return new Handler(null, $data['firstname'], $data['lastname'], $data['email'], $data['password'], $institution, $image, $data['phone'], null, $typeOfLaws, $subjects);
            case 'applicant':
                $education = new Education($data['education']['id'], $data['education']['name']);
                return new Applicant(null, $data['firstname'], $data['lastname'], $data['email'], $data['password'], $institution, $image, $data['phone'], null, $education);
            default:
                $this->respondWithError(400, "Invalid user type");
                return null;
        }
    }

    private function updateUser($id, $data, $institution, $image): ?User {
        if (!empty($data['userId']) && !empty($data['subjects'])) {
            if (empty($data['typeOfLaws'])) {
                $this->respondWithError(400, "Missing required fields");
                return null;
            }

            $typeOfLaws = array_map(function($typeOfLaw) {
                return new TypeOfLaw($typeOfLaw['id'], TypeOfLow::fromDatabase($typeOfLaw['description']));
            }, $data['typeOfLaws']);

            $subjects = array_map(function($subject) {
                return new Subject($subject['id'], $subject['description']);
            }, $data['subjects']);
            return new Handler($id, $data['firstname'], $data['lastname'], $data['email'], null, $institution, null, $data['phone'], $data['userId'], $typeOfLaws, $subjects);
        } elseif (!empty($data['education'])) {
            if (empty($data['userId'])) {
                $this->respondWithError(400, "Missing required fields");
                return null;
            }
            $education = new Education($data['education']['id'], $data['education']['name']);
            return new Applicant($id, $data['firstname'], $data['lastname'], $data['email'], null, $institution, null, $data['phone'], $data['userId'], $education);
        } else {
            return new User($id, $data['firstname'], $data['lastname'], $data['email'], null, $institution, null, $data['phone']);
        }
    }
}
?>