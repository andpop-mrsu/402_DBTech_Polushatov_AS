<?php
require __DIR__ . '/../vendor/autoload.php';
use Slim\Factory\AppFactory;
use GuzzleHttp\Psr7\Utils;

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

// Редирект на index.html
$app->get('/', function ($request, $response) {
    return $response
        ->withHeader('Location', '/index.html')
        ->withStatus(302);
});
$app->get('/init', function ($request, $response) {
    try {
        // Подключаемся к базе данных SQLite
        $db = new SQLite3(__DIR__ . '/../db/games.db');
        $db->enableExceptions(true);  // Включаем исключения для SQLite3

        // Проверка существования таблиц и их создание
        $createGamesTable = "CREATE TABLE IF NOT EXISTS games (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        word TEXT NOT NULL,
        max_attempts INTEGER NOT NULL,
        attempts INTEGER DEFAULT 0,
        status TEXT CHECK( status IN ('active', 'won', 'lost') ) DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

        $createStepsTable = "CREATE TABLE IF NOT EXISTS steps (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        game_id INTEGER NOT NULL,
        letter TEXT CHECK( length(letter) = 1 ) NOT NULL,
        success BOOLEAN NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
    )";

        // Выполнение запросов и отладочная информация
        if ($db->exec($createGamesTable)) {
            echo "Таблица 'games' успешно создана или уже существует.<br>";
        } else {
            echo "Ошибка при создании таблицы 'games': " . $db->lastErrorMsg() . "<br>";
        }

        if ($db->exec($createStepsTable)) {
            echo "Таблица 'steps' успешно создана или уже существует.<br>";
        } else {
            echo "Ошибка при создании таблицы 'steps': " . $db->lastErrorMsg() . "<br>";
        }

    } catch (Exception $e) {
        echo "Ошибка подключения или выполнения SQL: " . $e->getMessage();
    }
});

// GET /games - получить все игры
$app->get('/games', function ($request, $response) {
    $db = new SQLite3(__DIR__ . '/../db/games.db');
    $stmt = $db->query("SELECT * FROM games");
    $games = [];

    while ($row = $stmt->fetchArray(SQLITE3_ASSOC)) {
        $games[] = $row;
    }

    $response->getBody()->write(json_encode($games));
    return $response->withHeader('Content-Type', 'application/json');
});

// GET /games/{id} - получить ходы для конкретной игры
$app->get('/games/{id}', function ($request, $response, $args) {
    $db = new SQLite3(__DIR__ . '/../db/games.db');
    $id = $args['id'];

    $stmt = $db->prepare("SELECT * FROM steps WHERE game_id = ?");
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    $result = $stmt->execute();

    $steps = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $steps[] = $row;
    }

    $response->getBody()->write(json_encode($steps));
    return $response->withHeader('Content-Type', 'application/json');
});

// POST /games - создать новую игру
$app->post('/games', function ($request, $response) {
    $db = new SQLite3(__DIR__ . '/../db/games.db');
    $data = json_decode($request->getBody(), true);

    $word = $data['word'] ?? '';
    $max_attempts = $data['max_attempts'] ?? 6;

    if (empty($word)) {
        return $response->withStatus(400)->write('Word is required');
    }

    $stmt = $db->prepare("INSERT INTO games (word, max_attempts) VALUES (?, ?)");
    $stmt->bindValue(1, $word, SQLITE3_TEXT);
    $stmt->bindValue(2, $max_attempts, SQLITE3_INTEGER);
    $stmt->execute();
    $gameId = $db->lastInsertRowID();

    $response->getBody()->write(json_encode(['id' => $gameId]));
    return $response->withHeader('Content-Type', 'application/json');
});

// POST /step/{id} - добавить ход для игры
$app->post('/step/{id}', function ($request, $response, $args) {
    $db = new SQLite3(__DIR__ . '/../db/games.db');
    $id = $args['id'];
    $data = json_decode($request->getBody(), true);

    $letter = $data['letter'] ?? '';
    $is_successful = $data['is_successful'] ?? 0;

    if (empty($letter)) {
        return $response->withStatus(400)->write('Letter is required');
    }

    $stmt = $db->prepare("INSERT INTO steps (game_id, letter, success) VALUES (?, ?, ?)");
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    $stmt->bindValue(2, $letter, SQLITE3_TEXT);
    $stmt->bindValue(3, $is_successful, SQLITE3_INTEGER);
    $stmt->execute();

    $response->getBody()->write(json_encode(['status' => 'Step recorded']));
    return $response->withHeader('Content-Type', 'application/json');
});

// Очистка базы данных
$app->post('/clear-db', function ($request, $response) {
    $db = new SQLite3(__DIR__ . '/../db/games.db');
    $db->exec('DELETE FROM games');
    $db->exec('DELETE FROM steps');
    return $response->withStatus(200);
});

// Запуск приложения
$app->run();
