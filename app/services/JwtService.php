<?php
namespace App\Services;

use App\Models\Applicant;
use App\Models\Handler;
use App\Models\User;
use Firebase\JWT\JWT;

class JwtService {
    public function generateJwt(User $user): string {
        $key = 'studieknallers';
        $role = 'Beheerder';

        if ($user instanceof Handler) {
            $role = 'Behandelaar';
        } elseif ($user instanceof Applicant) {
            $role = 'Indiener';
        }

        $payload = [
            'iss' => 'http://inholland.nl',
            'aud' => 'http://inholland.nl/vieapp',
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + 3600,
            'sub' => $user->getId(),
            'data' => [ "user" => $user->getFirstname() . " " . $user->getLastname(), "role" => $role ]
        ];

        return JWT::encode($payload, $key, 'HS256');
    }
}
?>