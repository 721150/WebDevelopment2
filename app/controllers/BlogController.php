<?php
namespace App\Controllers;

use App\Services\BlogService;

class BlogController extends Controller {
    private $blogservice;

    function __construct() {
        $this->blogservice = new BlogService();
    }

    public function getAll() {
        $blogs = $this->blogservice->getAll();

        if (!$blogs) {
            $this->respondWithError(404, "Blogs not found");
            return;
        }

        $this->respond($blogs);
    }

    public function getOne($id) {
        $blog = $this->blogservice->getOne($id);

        if (!$blog) {
            $this->respondWithError(404, "Blog not found");
            return;
        }

        $this->respond($blog);
    }
}
?>