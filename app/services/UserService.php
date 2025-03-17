<?php
namespace App\Services;

use App\Models\Applicant;
use App\Models\Handler;
use App\Models\User;
use App\Repositories\UserRepository;

class UserService {
    private $userRepository;

    function __construct() {
        $this->userRepository = new UserRepository();
    }

    public function getAll() {
        return $this->userRepository->getAll();
    }

    public function getOne($id) {
        return $this->userRepository->getOne($id);
    }

    public function createAdmin(User $user) {
        return $this->userRepository->createAdmin($user);
    }

    public function update($user) {
        return $this->userRepository->update($user);
    }

    public function delete($id) {
        return $this->userRepository->delete($id);
    }

    public function login(string $username, string $password) {
        return $this->userRepository->login($username, $password);
    }

    public function createHandler(Handler $user) {
        return $this->userRepository->createHandler($user);
    }

    public function createApplicant(Applicant $user) {
        return $this->userRepository->createApplicant($user);
    }
}
?>