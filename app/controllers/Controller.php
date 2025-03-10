<?php
namespace App\Controllers;

class Controller {
    function respond($data) {
        $this->respondWithCode(200, $data);
    }

    function respondWithError($httpcode, $message) {
        $data = array('errorMessage' => $message);
        $this->respondWithCode($httpcode, $data);
    }

    private function respondWithCode($httpcode, $data) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($httpcode);
        echo json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    function getRequestData() {
        $json = file_get_contents('php://input');
        return json_decode($json, true);
    }
}
?>