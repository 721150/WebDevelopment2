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
        $users = null;
        try {
            $users = $this->userService->getAll();
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

        if (empty($data['firstname']) || empty($data['lastname']) || empty($data['email']) || empty($data['password']) || empty($data['institution']) || empty($data['phone'])) {
            $this->respondWithError(400, "Missing required fields");
            return;
        }

        $institution = new Institution($data['institution']['id'], $data['institution']['name']);
        $image = $data['image'] ?? null;
        $user = new User(null, $data['firstname'], $data['lastname'], $data['email'], $data['password'], $institution, null, $data['phone']);

        if ($image !== null) {
            $user->setImage($image);
        }

        $createdUser = null;
        try {
            $createdUser = $this->userService->createAdmin($user);
        } catch (Exception $exception) {
            $this->respondWithError(500, "Failed to create user: " . $exception->getMessage());
        }

        if ($createdUser == null) {
            $this->respondWithError(500, "Failed to create user");
            return;
        }

          $this->respond($createdUser);
    }

    public function createHandler(): void {
        $data = $this->getRequestData();

        if (empty($data['firstname']) || empty($data['lastname']) || empty($data['email']) || empty($data['password']) || empty($data['institution']) || empty($data['phone']) || empty($data['typeOfLaws']) || empty($data['subjects'])) {
            $this->respondWithError(400, "Missing required fields");
            return;
        }

        $institution = new Institution($data['institution']['id'], $data['institution']['name']);
        $image = $data['image'] ?? null;

        $typeOfLaws = [];
        foreach ($data['typeOfLaws'] as $type) {
            $typeOfLowEnum = TypeOfLow::fromDatabase($type['description']);
            if ($typeOfLowEnum !== null) {
                $typeOfLaws[] = new TypeOfLaw($type['id'], $typeOfLowEnum);
            }
        }

        $subjects = [];
        foreach ($data['subjects'] as $subject) {
            $subjects[] = new Subject($subject['id'], $subject['description']);
        }
        $user = new Handler(null, $data['firstname'], $data['lastname'], $data['email'], $data['password'], $institution, $image, $data['phone'], null, $typeOfLaws, $subjects);

        if ($image !== null) {
            $user->setImage($image);
        }

        $createdUser = null;
        try {
            $createdUser = $this->userService->createHandler($user);
        } catch (Exception $exception) {
            $this->respondWithError(500, "Failed to create user: " . $exception->getMessage());
        }

        if ($createdUser == null) {
            $this->respondWithError(500, "Failed to create user");
            return;
        }

        $this->respond($createdUser);
    }

    public function createApplicant(): void {
        $data = $this->getRequestData();

        if (empty($data['firstname']) || empty($data['lastname']) || empty($data['email']) || empty($data['password']) || empty($data['institution']) || empty($data['phone']) || empty($data['education'])) {
            $this->respondWithError(400, "Missing required fields");
            return;
        }

        $institution = new Institution($data['institution']['id'], $data['institution']['name']);
        $image = $data['image'] ?? null;
        $education = new Education($data['education']['id'], $data['education']['name']);

        $user = new Applicant(null, $data['firstname'], $data['lastname'], $data['email'], $data['password'], $institution, $image, $data['phone'], null, $education);

        if ($image !== null) {
            $user->setImage($image);
        }

        $createdUser = null;
        try {
            $createdUser = $this->userService->createApplicant($user);
        } catch (Exception $exception) {
            $this->respondWithError(500, "Failed to create user: " . $exception->getMessage());
        }

        if ($createdUser == null) {
            $this->respondWithError(500, "Failed to create user");
            return;
        }

        $this->respond($createdUser);
    }

    public function update($id): void {
        $data = $this->getRequestData();

        if (empty($data['id']) || empty($data['firstname']) || empty($data['lastname']) || empty($data['email']) || empty($data['institution']) || empty($data['phone'])) {
            $this->respondWithError(400, "Missing required fields");
            return;
        }

        $institution = new Institution($data['institution']['id'], $data['institution']['name']);
        $image = $data['image'] ?? null;

        if (!empty($data['userId']) && !empty($data['subjects'])) {
            if (empty($data['typeOfLaws'])) {
                $this->respondWithError(400, "Missing required fields");
                return;
            }

            $typeOfLaws = array_map(function($typeOfLaw) {
                return new TypeOfLaw($typeOfLaw['id'], TypeOfLow::fromDatabase($typeOfLaw['description']));
            }, $data['typeOfLaws']);

            $subjects = array_map(function($subject) {
                return new Subject($subject['id'], $subject['description']);
            }, $data['subjects']);
            $user = new Handler($id, $data['firstname'], $data['lastname'], $data['email'], null, $institution, null, $data['phone'], $data['userId'], $typeOfLaws, $subjects);
        } elseif (!empty($data['education'])) {
            if (empty($data['userId'])) {
                $this->respondWithError(400, "Missing required fields");
                return;
            }
            $education = new Education($data['education']['id'], $data['education']['name']);
            $user = new Applicant($id, $data['firstname'], $data['lastname'], $data['email'], null, $institution, null, $data['phone'], $data['userId'], $education);
        } else {
            $user = new User($id, $data['firstname'], $data['lastname'], $data['email'], null, $institution, null, $data['phone']);
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
}
?>