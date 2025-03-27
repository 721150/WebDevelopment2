<?php
namespace App\Services;

use App\Models\Applicant;
use App\Models\Handler;
use App\Models\User;
use App\Repositories\UserRepository;

class UserService {
    private UserRepository $userRepository;

    function __construct() {
        $this->userRepository = new UserRepository();
    }

    public function getAll($offset = null, $limit = null): array {
        return $this->userRepository->getAll($offset, $limit);
    }

    public function getOne($id): User|Applicant|Handler|null {
        return $this->userRepository->getOne($id);
    }

    public function createAdmin(User $user): User|Applicant|Handler|null {
        return $this->userRepository->createAdmin($user);
    }

    public function update($user): User|Applicant|Handler|null {
        return $this->userRepository->update($user);
    }

    public function delete($id): bool {
        return $this->userRepository->delete($id);
    }

    public function login(string $username, string $password): User|Applicant|Handler|null {
        return $this->userRepository->login($username, $password);
    }

    public function createHandler(Handler $user): User|Applicant|Handler|null {
        return $this->userRepository->createHandler($user);
    }

    public function createApplicant(Applicant $user): User|Applicant|Handler|null {
        return $this->userRepository->createApplicant($user);
    }
}
?>