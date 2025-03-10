<?php
namespace App\Services;

use App\Models\Blog;
use App\Repositories\BlogRepository;

class BlogService {
    private $blogRepository;

    function __construct() {
        $this->blogRepository = new BlogRepository();
    }

    public function getAll() {
        return $this->blogRepository->getAll();
    }

    public function getOne(int $id) {
        return $this->blogRepository->getOne($id);
    }

    public function create(Blog $blog) {
        return $this->blogRepository->create($blog);
    }

    public function delete(int $id) {
        return $this->blogRepository->delete($id);
    }

    public function update(Blog $blog) {
        return $this->blogRepository->update($blog);
    }
}
?>