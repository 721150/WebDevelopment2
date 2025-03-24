<?php
namespace App\Repositories;

use App\Models\Reactie;
use PDOException;

class ReactieRepository extends Repository {
    public function create(Reactie $reactie, int $blogId): Reactie {
        try {
            $stmt = $this->connection->prepare("INSERT INTO reactie (blogId, dateTime, content) VALUES (:blogId, :dateTime, :content)");

            $dateTime = $reactie->getDateTime();
            $content = $reactie->getContent();

            $stmt->bindParam(':blogId', $blogId);
            $stmt->bindParam(':dateTime', $dateTime);
            $stmt->bindParam(':content', $content);

            $stmt->execute();

            $generatedId = $this->connection->lastInsertId();

            $newReactie = new Reactie($generatedId, $reactie->getDateTime(), $reactie->getContent());
        } catch (PDOException $exception) {
            throw $exception;
        }

        return $newReactie;
    }
}
?>