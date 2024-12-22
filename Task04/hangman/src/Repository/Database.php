<?php

namespace aplou00\Hangman\Repository;

class Database
{
    private $db;

    public function __construct()
    {
        try {
            $this->db = new \PDO('sqlite:hangman.db');
        } catch (\Exception $e) {
            echo "Ошибка при создании базы данных: " . $e->getMessage();
            exit;
        }
        $this->createTables();
        $this->migrationData();
    }

    private function createTables(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS words (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            word TEXT NOT NULL
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS games (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            date TEXT,
            player_name TEXT NOT NULL,
            word_id INTEGER,
            attempts INTEGER,
            won BOOLEAN,
            FOREIGN KEY (word_id) REFERENCES words(id)
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS moves (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            game_id INTEGER,
            move_number INTEGER,
            letter TEXT,
            result BOOLEAN NOT NULL,
            FOREIGN KEY (game_id) REFERENCES games(id)
        )");
    }

    private function migrationData(): void
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM words");
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            $words = [
                "apple",
                "banana",
                "cherry",
                "orange",
                "grape",
                "lemon"
            ];

            $stmt = $this->db->prepare("INSERT INTO words (word) VALUES (:word)");

            foreach ($words as $word) {
                $stmt->bindValue(':word', $word, \PDO::PARAM_STR);
                $stmt->execute();
            }
        }
    }

    public function addWord(string $word): void
    {
        $stmt = $this->db->prepare("INSERT INTO words (word) VALUES (:word)");
        $stmt->bindValue(':word', $word, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function getRandomWord(): string
    {
        $stmt = $this->db->query("SELECT word FROM words ORDER BY RANDOM() LIMIT 1");
        $result = $stmt->fetchColumn();
        return $result;
    }

    public function getAllGame(): ?array
    {
        $stmt = $this->db->query("SELECT * FROM games");
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return null;
        }

        return $rows;
    }

    public function getWordId(string $word): int
    {
        $stmt = $this->db->prepare("SELECT id FROM words WHERE word = :word");
        $stmt->bindValue(':word', $word, \PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            throw new \Exception("Word '$word' not found in the database.");
        }

        return $row['id'];
    }

    public function getWordById(int $wordId): ?array
    {
        $stmt = $this->db->prepare("SELECT word FROM words WHERE id = :word_id");
        $stmt->bindValue(':word_id', $wordId, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return $row;
    }

    public function getGameById(int $gameId): ?array
    {
        $stmt = $this->db->prepare("SELECT word_id, attempts, won FROM games WHERE id = :id");
        $stmt->bindValue(':id', $gameId, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return $row;
    }

    public function creatNewGame(string $name): int
    {
        $stmt = $this->db->prepare("INSERT INTO games (player_name) VALUES (:player_name)");
        $stmt->bindValue(':player_name', $name, SQLITE3_TEXT);
        $result = $stmt->execute();
        if ($result) {
            return $this->db->lastInsertId();
        } else {
            return -1;
        }
    }

    public function saveGameResult(int $id, int $wordId, int $attempts, bool $won): void
    {
        $stmt = $this->db->prepare("
            UPDATE games 
            SET date = :date, 
                word_id = :word_id, 
                attempts = :attempts, 
                won = :won 
            WHERE id = :id
        ");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':date', date('Y-m-d H:i:s'), SQLITE3_TEXT);
        $stmt->bindValue(':word_id', $wordId, SQLITE3_INTEGER);
        $stmt->bindValue(':attempts', $attempts, SQLITE3_INTEGER);
        $stmt->bindValue(':won', $won, SQLITE3_INTEGER);
        $stmt->execute();
    }

    public function saveMoves(int $game_id, int $move_number, string $letter, bool $result): void
    {
        $stmt = $this->db->prepare("INSERT INTO moves (game_id, move_number, letter, result) VALUES (:game_id, :move_number, :letter, :result)");
        $stmt->bindValue(':game_id', $game_id, SQLITE3_INTEGER);
        $stmt->bindValue(':move_number', $move_number, SQLITE3_INTEGER);
        $stmt->bindValue(':letter', $letter, SQLITE3_TEXT);
        $stmt->bindValue(':result', $result, SQLITE3_INTEGER);
        $stmt->execute();
    }

    public function updateGameResult(int $gameId, int $wordId, int $attempts, bool $won): void
    {
        $stmt = $this->db->prepare("UPDATE games SET word_id = :word_id, attempts = :attempts, won = :won WHERE id = :id");
        $stmt->bindValue(':id', $gameId, \PDO::PARAM_INT);
        $stmt->bindValue(':word_id', $wordId, \PDO::PARAM_INT);
        $stmt->bindValue(':attempts', $attempts, \PDO::PARAM_INT);
        $stmt->bindValue(':won', $won, \PDO::PARAM_BOOL);
        $stmt->execute();
    }

    public function getGameStatistics(): array
    {
        $stmt = $this->db->query("SELECT COUNT(*) as total_games, SUM(won) as total_wins FROM games");
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return [
            'total_games' => $row['total_games'],
            'total_wins' => $row['total_wins'],
            'total_losses' => $row['total_games'] - $row['total_wins']
        ];
    }
}