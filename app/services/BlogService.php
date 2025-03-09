<?php
namespace App\Services;

use App\Repositories\BlogRepository;

class BlogService {
    private $blogRepository;

    function __construct() {
        $this->blogRepository = new BlogRepository();
    }

    public function getAll() {
        return $this->blogRepository->getAll();
    }
}
?>