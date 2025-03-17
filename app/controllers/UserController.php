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
use PDOException;

class UserController extends Controller {
    private $userService;
    private $jwtService;

    function __construct() {
        $this->userService = new UserService();
        $this->jwtService = new JwtService();
    }

    public function login() {
        $logindata = $this->getRequestData();

        $user = $this->userService->login($logindata['username'], $logindata['password']);

        if (!$user) {
            $this->respondWithError(401,"Username or password is incorrect");
            return;
        }

        $jwt = $this->jwtService->generateJwt($user);

        $this->respond($jwt);
    }

    public function getAll() {
        $users = $this->userService->getAll();

        if (!$users) {
            $this->respondWithError(404, "Users not found");
            return;
        }

        $this->respond($users);
    }

    public function getOne($id) {
        try {
            $user = $this->userService->getOne($id);
        } catch (PDOException $e) {
            $this->respondWithError(500,"Failed to read user: " . $e->getMessage());
            return;
        }

        if (!$user) {
            $this->respondWithError(404, "User not found");
            return;
        }

        $this->respond($user);
    }

    public function createAdmin() {
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

        $createdUser = $this->userService->createAdmin($user);

        if (!$createdUser) {
            $this->respondWithError(500, "Failed to create user");
            return;
        }

          $this->respond($createdUser);
    }

    public function createHandler() {
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

        $createdUser = $this->userService->createHandler($user);

        if (!$createdUser) {
            $this->respondWithError(500, "Failed to create user");
            return;
        }

        $this->respond($createdUser);
    }

    public function createApplicant() {
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

        $createdUser = $this->userService->createApplicant($user);

        if (!$createdUser) {
            $this->respondWithError(500, "Failed to create user");
            return;
        }

        $this->respond($createdUser);
    }

    public function update($id) {
        $data = $this->getRequestData();

        if (empty($data['firstname']) || empty($data['lastname']) || empty($data['email']) || empty($data['institution']) || empty($data['phone'])) {
            $this->respondWithError(400, "Missing required fields");
            return;
        }

        $institution = new Institution($data['institution']['id'], $data['institution']['name']);
        $image = $data['image'] ?? null; // Maak de afbeelding optioneel
        $user = new User(
            $id,
            $data['firstname'],
            $data['lastname'],
            $data['email'],
            $institution,
            $image,
            $data['phone']
        );

        $updatedUser = $this->userService->update($user);

        if (!$updatedUser) {
            $this->respondWithError(500, "Failed to update user");
            return;
        }

        $this->respond($updatedUser);
    }

    public function delete($id) {
        $deleted = $this->userService->delete($id);

        if (!$deleted) {
            $this->respondWithError(500, "Failed to delete user");
            return;
        }

        $this->respond(true);
    }
}
?>