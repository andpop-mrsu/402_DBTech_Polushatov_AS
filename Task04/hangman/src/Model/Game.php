<?php

namespace aplou00\Hangman\Model;

class Game
{
    private $id;
    private $word;
    private $guessedLetters = [];
    private $attempts = [];
    private $gameOver = false;
    private $won = false;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function start(string $word): void
    {
        $this->word = $word;
    }

    public function isGameOver(): bool
    {
        return $this->gameOver;
    }

    public function setGameOver(bool $isGameOver): void
    {
        $this->gameOver = $isGameOver;
    }

    public function isWon(): bool
    {
        return $this->won;
    }

    public function setWon(bool $isWon): void
    {
        $this->won = $isWon;
    }

    public function getWord(): string
    {
        return $this->word;
    }

    public function addGuessedLetters(string $guess): void
    {
        $this->guessedLetters[] = $guess;
    }

    public function getGuessedLetters(): array
    {
        return $this->guessedLetters;
    }

    public function addAttempts(string $guess): void
    {
        $this->attempts[] = $guess;
    }

    public function getAttempts(): array
    {
        return $this->attempts;
    }
}