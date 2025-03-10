<?php
namespace App\Services;

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

    public function create(User $user) {
        return $this->userRepository->create($user);
    }

    public function update(User $user) {
        return $this->userRepository->update($user);
    }

    public function delete($id) {
        return $this->userRepository->delete($id);
    }
}
?>