<?php

namespace aplou00\Hangman\Model;

class Game {
    private $word;
    private $guessedLetters = [];
    private $attempts = [];
    private $gameOver = false;
    private $won = false;

    private $words = [
        "apple", "banana", "cherry", "orange", "grape", "lemon"
    ];

    public function start(string $word): void {
        $this->word = $word;
    }

    public function makeGuess(string $guess): void {
        $guess = strtolower($guess);
        if (empty($guess)) {
            echo "Please enter a letter or the whole word.\n";
            return;
        }
        if (strlen($guess) === 1) {
            if (in_array($guess, $this->guessedLetters)) {
                echo "You already guessed that letter.\n";
                return;
            }
            $this->guessedLetters[] = $guess;
            if (strpos($this->word, $guess) !== false) {
                echo "Correct guess!\n";
            } else {
                echo "Incorrect guess!\n";
                $this->attempts[] = $guess;
            }
        } else {
            if ($guess === $this->word) {
                $this->won = true;
                $this->gameOver = true;
            } else {
                echo "Incorrect word!\n";
                $this->attempts[] = $guess;
            }
        }
        if (count($this->attempts) >= 6) {
            $this->gameOver = true;
        }
        if ($this->isWordGuessed()) {
            $this->won = true;
            $this->gameOver = true;
        }
    }

    public function isGameOver(): bool {
        return $this->gameOver;
    }

    public function isWon(): bool {
        return $this->won;
    }

    public function getWord(): string {
        return $this->word;
    }

    public function getGuessedLetters(): array {
        return $this->guessedLetters;
    }

    public function getAttempts(): array {
        return $this->attempts;
    }

    public function getRandomWord(): string {
        return $this->words[array_rand($this->words)];
    }

    private function isWordGuessed(): bool {
        foreach (str_split($this->word) as $letter) {
            if (!in_array($letter, $this->guessedLetters)) {
                return false;
            }
        }
        return true;
    }
}