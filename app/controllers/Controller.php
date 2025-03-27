<?php
namespace App\Controllers;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Controller {
    function respond($data): void {
        $this->respondWithCode(200, $data);
    }

    function respondWithError($httpcode, $message): void {
        $data = array('errorMessage' => $message);
        $this->respondWithCode($httpcode, $data);
    }

    private function respondWithCode($httpcode, $data): void {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($httpcode);
        echo json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    function getRequestData() {
        $json = file_get_contents('php://input');
        return json_decode($json, true);
    }

    function checkForJwt() {
        if(!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $this->respondWithError(401, "No token provided");
            return;
        }

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        $arr = explode(" ", $authHeader);
        $jwt = $arr[1];

        $secret_key = "studieknallers";

        if ($jwt) {
            try {
                $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
                return $decoded;
            } catch (Exception $exception) {
                $this->respondWithError(401, $exception->getMessage());
                return;
            }
        }
    }
}
?>