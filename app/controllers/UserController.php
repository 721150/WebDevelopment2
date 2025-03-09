<?php
namespace App\Controllers;

use App\Services\UserService;

class UserController extends Controller {
    private $userService;

    function __construct() {
        $this->userService = new UserService();
    }
}
?>