<?php

namespace aplou00\Hangman\Service;

use aplou00\Hangman\Repository\Database;
use aplou00\Hangman\Model\Game;
use aplou00\Hangman\View\GameView;

class GameService
{
    private $game;
    private $db;

    public function __construct()
    {
        $this->db = new Database();
        $this->game = new Game($this->db);
    }

    public function showStatistics(): void
    {
        $stats = $this->db->getGameStatistics();
        GameView::showGameStatistics($stats);
    }

    public function listGames(): ?array
    {
        $rows = $this->db->getAllGame();

        if ($rows === null) {
            echo "List empty!\n";
            return null;
        }

        foreach ($rows as $row) {
            if ($row['word_id'] !== null) {
                $word = $this->db->getWordById($row['word_id']);
                echo $row['date'] . " Player name: " . $row['player_name'] . ", Game ID: " . $row['id'] . ", Word: " . $word . ", Attempts: " . $row['attempts'] . ", Won: " . ($row['won'] ? 'Yes' : 'No') . "\n";
            }
        }

        return $rows;
    }

    public function startNewGame(): void
    {
        $word = $this->db->getRandomWord();
        $this->game->start($word);
        $this->playGame();
    }

    public function playGame(): void
    {
        $playerName = \cli\prompt("Enter your name:");
        $id = $this->db->creatNewGame($playerName);
        if ($id !== -1) {
            $this->game->setId($id);
            $this->loopGame();
            $wordId = $this->db->getWordId($this->game->getWord());
            $attempts = count($this->game->getAttempts());
            $won = $this->game->isWon();
            $this->db->saveGameResult($id, $wordId, $attempts, $won);
            GameView::showGameResult($this->game);
        } else {
            echo "error creat new game.";
        }
    }

    public function replayGame(int $id): void
    {
        echo "Replaying game with ID: $id\n";
        $row = $this->db->getGameById($id);

        if ($row === null) {
            echo "Game with ID $id not found.\n";
            return;
        }

        $wordId = $row['word_id'];
        $attempts = $row['attempts'];
        $won = $row['won'];
        $word = $this->db->getWordById($wordId);
        if ($word === null) {
            echo "Word with ID $wordId not found.\n";
            return;
        }

        $this->game->start($word);
        $this->game->setId($id);
        $this->loopGame();
        $wordId = $this->db->getWordId($this->game->getWord());
        $attempts = count($this->game->getAttempts());
        $won = $this->game->isWon();
        $this->db->updateGameResult($id, $wordId, $attempts, $won);
        GameView::showGameResult($this->game);
    }

    private function loopGame(): void
    {
        $temp = 0;
        while (!$this->game->isGameOver()) {
            $temp++;
            GameView::showGameState($this->game);
            $guess = readline("Enter a letter or the whole word: ");
            $result = $this->makeGuess($guess);
            $this->db->saveMoves($this->game->getId(), $temp, $guess, $result);
        }
        $this->game->setGameOver(false);
    }

    private function makeGuess(string $guess): bool
    {
        $guess = strtolower($guess);
        $isTrue = false;
        if (empty($guess)) {
            echo "Please enter a letter or the whole word.\n";
            return $isTrue;
        }
        if (strlen($guess) === 1) {
            if (in_array($guess, $this->game->getGuessedLetters())) {
                echo "You already guessed that letter.\n";
                return !$isTrue;
            }
            $this->game->addGuessedLetters($guess);
            if (strpos($this->game->getWord(), $guess) !== false) {
                echo "Correct guess!\n";
                $isTrue = true;
            } else {
                echo "Incorrect guess!\n";
                $this->game->addAttempts($guess);
                $isTrue = false;
            }
        } else {
            if ($guess === $this->game->getWord()) {
                $this->game->setWon(true);
                $this->game->setGameOver(true);
                $isTrue = true;
            } else {
                echo "Incorrect word!\n";
                $this->game->addAttempts($guess);
                $isTrue = false;
            }
        }
        if (count($this->game->getAttempts()) >= 6) {
            $this->game->setGameOver(true);
        }
        if ($this->isWordGuessed()) {
            $this->game->setWon(true);
            $this->game->setGameOver(true);
        }
        return $isTrue;
    }

    private function isWordGuessed(): bool
    {
        foreach (str_split($this->game->getWord()) as $letter) {
            if (!in_array($letter, $this->game->getGuessedLetters())) {
                return false;
            }
        }
        return true;
    }
}