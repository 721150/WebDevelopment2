<?php
namespace App\Controllers;

use App\Services\UserService;

class UserController extends Controller {
    private $userService;

    function __construct() {
        $this->userService = new UserService();
    }

    public function login() {
        $logindata = $this->getRequestData();

        // TODO dit verder afmaken
        $user = $this->userService->login($logindata['username'], $logindata['password']);

        if (!$user) {
            $this->respondWithError(401,"Username or password is incorrect");
        }

        // TODO nog aanmaken in apparte service
        $jwt = $this->userService->generateJwt($user);

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
        $user = $this->userService->getOne($id);

        if (!$user) {
            $this->respondWithError(404, "User not found");
            return;
        }

        $this->respond($user);
    }

    public function create() {
        $data = $this->getRequestData();

        if (empty($data['firstname']) || empty($data['lastname']) || empty($data['email']) || empty($data['institution']) || empty($data['phone'])) {
            $this->respondWithError(400, "Missing required fields");
            return;
        }

        $institution = new Institution($data['institution']['id'], $data['institution']['name']);
        $image = $data['image'] ?? null; // Maak de afbeelding optioneel
        $user = new User(
            null, // Zal door database worden gemaakt
            $data['firstname'],
            $data['lastname'],
            $data['email'],
            $institution,
            $image,
            $data['phone']
        );

        $createdUser = $this->userService->create($user);

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