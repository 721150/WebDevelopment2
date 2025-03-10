<?php
namespace App\Controllers;

use App\Models\Blog;
use App\Models\Education;
use App\Models\Enums\TypeOfLow;
use App\Models\Institution;
use App\Models\Reactie;
use App\Models\Subject;
use App\Models\TypeOfLaw;
use App\Services\BlogService;
use DateTime;
use Exception;

class BlogController extends Controller {
    private $blogService;

    function __construct() {
        $this->blogService = new BlogService();
    }

    public function getAll() {
        $blogs = $this->blogService->getAll();

        if (!$blogs) {
            $this->respondWithError(404, "Blogs not found");
            return;
        }

        $this->respond($blogs);
    }

    public function getOne($id) {
        $blog = $this->blogService->getOne($id);

        if (!$blog) {
            $this->respondWithError(404, "Blog not found");
            return;
        }

        $this->respond($blog);
    }

    public function create()
    {
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

        $blog = new Blog(
            null, // Zal door database worden gemaakt
            $currentDateTime,
            $institution,
            $education,
            $subject,
            $typeOfLaw,
            $data['description'],
            $data['content'],
            [] // Bestaan nog niet bij het aanmaken van een nieuw blog-bericht
        );

        $createdBlog = $this->blogService->create($blog);

        if (!$createdBlog) {
            $this->respondWithError(500, "Failed to create blog");
            return;
        }

        $this->respond($createdBlog);
    }

    public function update($id) {
        try {
            $data = $this->getRequestData();

            if (empty($data['dateTime']) || empty($data['institution']) || empty($data['education']) || empty($data['subject']) || empty($data['typeOfLaw']) || empty($data['description']) || empty($data['content'])) {
                $this->respondWithError(400, "Missing required fields");
                return;
            }

            $institution = new Institution($data['institution']['id'], $data['institution']['name']);
            $education = new Education($data['education']['id'], $data['education']['name']);
            $subject = new Subject($data['subject']['id'], $data['subject']['description']);
            $typeOfLaw = new TypeOfLaw($data['typeOfLaw']['id'], TypeOfLow::fromDatabase($data['typeOfLaw']['description']));

            $reacties = [];
            if (!empty($data['reacties'])) {
                foreach ($data['reacties'] as $reactieData) {
                    $reactieDateTime = $reactieData['dateTime'] ?? (new DateTime())->format('Y-m-d H:i:s');
                    $reactie = new Reactie(
                        $reactieData['id'] ?? null,
                            $reactieDateTime,
                        $reactieData['content']
                    );
                    $reacties[] = $reactie;
                }
            }

            $blog = new Blog(
                $id,
                $data['dateTime'],
                $institution,
                $education,
                $subject,
                $typeOfLaw,
                $data['description'],
                $data['content'],
                $reacties
            );

            $updatedBlog = $this->blogService->update($blog);

            if (!$updatedBlog) {
                $this->respondWithError(500, "Failed to update blog");
                return;
            }

            $this->respond($updatedBlog);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function delete($id) {
        $deleted = $this->blogService->delete($id);

        if ($deleted) {
            $this->respondWithError(500, "Failed to delete blog");
            return;
        }

        $this->respond(true);
    }
}
?>