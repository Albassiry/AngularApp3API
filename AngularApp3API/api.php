<?php
// REST API controller for book_db books table (GET, POST, PUT, DELETE)
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = (int) $_GET['id'];
            $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
            $stmt->execute([$id]);
            $book = $stmt->fetch();

            if ($book) {
                echo json_encode($book);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Book not found"]);
            }
        } else {
            $stmt = $pdo->query("SELECT * FROM books ORDER BY id DESC");
            echo json_encode($stmt->fetchAll());
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $title = isset($data['title']) ? trim($data['title']) : '';
        $author = isset($data['author']) ? trim($data['author']) : '';
        $description = isset($data['description']) ? trim($data['description']) : '';
        $coverImage = (!empty($data['cover_image'])) ? trim($data['cover_image']) : 'placeholder_100.jpg';

        if ($title !== '' && $author !== '') {
            $stmt = $pdo->prepare(
                "INSERT INTO books (title, author, description, cover_image) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$title, $author, $description, $coverImage]);
            http_response_code(201);
            echo json_encode([
                "message" => "Book created successfully",
                "id" => (int) $pdo->lastInsertId()
            ]);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Title and Author are required."]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $title = isset($data['title']) ? trim($data['title']) : '';
        $author = isset($data['author']) ? trim($data['author']) : '';
        $description = isset($data['description']) ? trim($data['description']) : '';
        $coverImage = isset($data['cover_image']) ? trim($data['cover_image']) : '';

        if ($id > 0 && $title !== '' && $author !== '') {
            if ($coverImage !== '') {
                $stmt = $pdo->prepare(
                    "UPDATE books SET title = ?, author = ?, description = ?, cover_image = ? WHERE id = ?"
                );
                $stmt->execute([$title, $author, $description, $coverImage, $id]);
            } else {
                $stmt = $pdo->prepare(
                    "UPDATE books SET title = ?, author = ?, description = ? WHERE id = ?"
                );
                $stmt->execute([$title, $author, $description, $id]);
            }
            echo json_encode(["message" => "Book updated successfully"]);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Valid ID, Title, and Author are required."]);
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            $id = (int) $_GET['id'];
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(["message" => "Book deleted successfully"]);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Invalid book ID."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Missing book ID."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method Not Allowed"]);
        break;
}