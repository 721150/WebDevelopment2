<?php
namespace App\Services;

use App\Models\Blog;
use App\Repositories\BlogRepository;
use Exception;

class BlogService {
    private BlogRepository $blogRepository;

    function __construct() {
        $this->blogRepository = new BlogRepository();
    }

    /**
     * @throws Exception
     */
    public function getAll($offset = null, $limit = null): array {
        return $this->blogRepository->getAll($offset, $limit);
    }

    /**
     * @throws Exception
     */
    public function create(Blog $blog): Blog {
        return $this->blogRepository->create($blog);
    }
}
?>