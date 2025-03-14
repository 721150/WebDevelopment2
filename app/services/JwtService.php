<?php
namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;

class JwtService {
    public function generateJwt(User $user) {
        $key = 'studieknallers';
        $payload = [
            'iss' => 'http://inholland.nl',
            'aud' => 'http://inholland.nl/vieapp',
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + 3600,
            'sub' => $user->getId(),
            'data' => [ "user" => $user->getFirstname() . " " . $user->getLastname(), "email" => $user->getEmail() ]
        ];

        $jwt = JWT::encode($payload, $key, 'HS256');
        return $jwt;
    }
}
?>