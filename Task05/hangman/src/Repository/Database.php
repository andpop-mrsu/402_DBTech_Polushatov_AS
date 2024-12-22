<?php

namespace aplou00\Hangman\Repository;

use RedBeanPHP\R;

class Database
{
    public function __construct()
    {
        if (!R::testConnection()) {
            R::setup('sqlite:hangman.db');
            R::ext('xdispense', function ($type) {
                return R::dispense($type);
            });
        }

        $this->createTables();
        $this->migrationData();
    }

    private function createTables(): void
    {
        // Создание таблиц и проверка их существования осуществляется автоматически RedBeanPHP при вставке записей.
    }

    private function migrationData(): void
    {
        if (R::count('words') == 0) {
            $words = ["apple", "banana", "cherry", "orange", "grape", "lemon"];

            foreach ($words as $word) {
                $wordBean = R::xdispense('words');
                $wordBean->word = $word;
                R::store($wordBean);
            }
        }
    }

    public function addWord(string $word): void
    {
        $wordBean = R::xdispense('words');
        $wordBean->word = $word;
        R::store($wordBean);
    }

    public function getRandomWord(): string
    {
        $wordBean = R::findOne('words', 'ORDER BY RANDOM()');
        return $wordBean->word ?? '';
    }

    public function getAllGame(): array
    {
        return R::exportAll(R::findAll('games'));
    }

    public function getWordId(string $word): int
    {
        $wordBean = R::findOne('words', 'word = ?', [$word]);

        if (!$wordBean) {
            throw new \Exception("Word '$word' not found in the database.");
        }

        return $wordBean->id;
    }

    public function getWordById(int $wordId): ?string
    {
        $wordBean = R::load('words', $wordId);
        return $wordBean->word ?? null;
    }

    public function getGameById(int $gameId): ?array
    {
        $gameBean = R::load('games', $gameId);
        if (!$gameBean->id) return null;

        return $gameBean->export();
    }

    public function creatNewGame(string $name): int
    {
        $gameBean = R::xdispense('games');
        $gameBean->player_name = $name;
        $gameBean->date = date('Y-m-d H:i:s');
        return R::store($gameBean);
    }

    public function saveGameResult(int $id, int $wordId, int $attempts, bool $won): void
    {
        $gameBean = R::load('games', $id);
        $gameBean->word_id = $wordId;
        $gameBean->attempts = $attempts;
        $gameBean->won = $won;
        R::store($gameBean);
    }

    public function saveMoves(int $game_id, int $move_number, string $letter, bool $result): void
    {
        $moveBean = R::xdispense('moves');
        $moveBean->game_id = $game_id;
        $moveBean->move_number = $move_number;
        $moveBean->letter = $letter;
        $moveBean->result = $result;
        R::store($moveBean);
    }

    public function updateGameResult(int $gameId, int $wordId, int $attempts, bool $won): void
    {
        $gameBean = R::load('games', $gameId);
        $gameBean->word_id = $wordId;
        $gameBean->attempts = $attempts;
        $gameBean->won = $won;
        R::store($gameBean);
    }

    public function getGameStatistics(): array
    {
        $totalGames = R::count('games');
        $totalWins = R::count('games', 'won = ?', [1]);
        return [
            'total_games' => $totalGames,
            'total_wins' => $totalWins,
            'total_losses' => $totalGames - $totalWins
        ];
    }
}
