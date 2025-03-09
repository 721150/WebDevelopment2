<?php
namespace App\Controllers;

use App\Services\BlogService;

class BlogController extends Controller {
    private $blogservice;

    function __construct() {
        $this->blogservice = new BlogService();
    }

    public function index()
    {
        echo "Index";
    }

    public function getAll() {
        $blogs = $this->blogservice->getAll();

        $this->respond($blogs);
    }
}
?>