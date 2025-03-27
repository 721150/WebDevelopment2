<?php
namespace App\Repositories;

use App\Models\Communication;
use PDO;
use PDOException;

class CommunicationRepository extends Repository {
    private UserRepository $userRepository;

    public function __construct() {
        parent::__construct();
        $this->userRepository = new UserRepository();
    }

    public function getOne(int $id) {
        $communication = null;

        try {
            $stmt = $this->connection->prepare("SELECT id, handler, content FROM communication WHERE caseId = :id");
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return $communication;
            }

            $handler = $this->userRepository->getOne($row['handler']);

            $communication = new Communication($row['id'], $handler, $row['content']);
        } catch (PDOException $exception) {
            throw $exception;
        }

        return $communication;
    }

    public function create(Communication $communication, int $caseId) {
        try {
            $stmt = $this->connection->prepare("INSERT INTO communication (handler, content, caseId) VALUES (:handler, :content, :caseId)");

            $handler = $communication->getHandler()->getId();
            $content = $communication->getContent();

            $stmt->bindParam(":handler", $handler);
            $stmt->bindParam(":content", $content);
            $stmt->bindParam(":caseId", $caseId);

            $stmt->execute();

            $generatedId = $this->connection->lastInsertId();

            $newCommunication = new Communication($generatedId, $communication->getHandler(), $communication->getContent());
        } catch (PDOException $exception) {
            throw $exception;
        }

        return $newCommunication;
    }
}
?>