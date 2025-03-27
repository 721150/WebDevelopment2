<?php
namespace App\Controllers;

use App\Models\Blog;
use App\Models\Education;
use App\Models\Enums\TypeOfLow;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\TypeOfLaw;
use App\Services\BlogService;
use DateTime;
use Exception;

class BlogController extends Controller {
    private BlogService $blogService;

    function __construct() {
        $this->blogService = new BlogService();
    }

    public function getAll(): void {
        $offset = null;
        $limit = null;

        if (isset($_GET["offset"]) && is_numeric($_GET["offset"])) {
            $offset = $_GET["offset"];
        }
        if (isset($_GET["limit"]) && is_numeric($_GET["limit"])) {
            $limit = $_GET["limit"];
        }

        $blogs = null;
        try {
            $blogs = $this->blogService->getAll($offset, $limit);
        } catch (Exception $exception) {
            $this->respondWithError(500, "Blogs not found " . $exception->getMessage());
        }

        if ($blogs == null) {
            $this->respondWithError(404, "Blogs not found");
            return;
        }

        $this->respond($blogs);
    }

    /**
     * @throws Exception
     */
    public function create(): void {
        $data = $this->getRequestData();

        if (empty($data['institution']) || empty($data['education']) || empty($data['subject']) || empty($data['typeOfLaw']) || empty($data['description']) || empty($data['content'])) {
            $this->respondWithError(400, "Missing required fields");
            return;
        }

        $institution = new Institution($data['institution']['id'], $data['institution']['name']);
        $education = new Education($data['education']['id'], $data['education']['name']);
        $subject = new Subject($data['subject']['id'], $data['subject']['description']);
        $typeOfLaw = new TypeOfLaw($data['typeOfLaw']['id'], TypeOfLow::fromDatabase($data['typeOfLaw']['description']));

        $currentDateTime = (new DateTime())->format('Y-m-d H:i:s');

        $blog = new Blog(null, $currentDateTime, $institution, $education, $subject, $typeOfLaw, $data['description'], $data['content'], []);

        $createdBlog = null;
        try {
            $createdBlog = $this->blogService->create($blog);
        } catch (Exception $exception) {
            $this->respondWithError(500, "Failed to create blog " . $exception->getMessage());
        }

        if ($createdBlog == null) {
            $this->respondWithError(500, "Failed to create blog");
            return;
        }

        $this->respond($createdBlog);
    }
}
?>