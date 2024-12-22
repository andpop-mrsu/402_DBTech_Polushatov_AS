<?php

namespace aplou00\Hangman\View;

use aplou00\Hangman\Model\Game;

class GameView
{
    private static $hangmanStates = [
        "  +---+\n  |   |\n      |\n      |\n      |\n      |\n=========",
        "  +---+\n  |   |\n  O   |\n      |\n      |\n      |\n=========",
        "  +---+\n  |   |\n  O   |\n  |   |\n      |\n      |\n=========",
        "  +---+\n  |   |\n  O   |\n /|   |\n      |\n      |\n=========",
        "  +---+\n  |   |\n  O   |\n /|\\  |\n      |\n      |\n=========",
        "  +---+\n  |   |\n  O   |\n /|\\  |\n /    |\n      |\n=========",
        "  +---+\n  |   |\n  O   |\n /|\\  |\n / \\  |\n      |\n========="
    ];

    public static function showGameState(Game $game): void
    {
        $word = $game->getWord();
        $guessedLetters = $game->getGuessedLetters();
        $maskedWord = '';
        foreach (str_split($word) as $letter) {
            $maskedWord .= in_array($letter, $guessedLetters) ? $letter : '_';
        }
        echo "Word: $maskedWord\n";
        echo "Attempts left: " . (6 - count($game->getAttempts())) . "\n";
        echo "Guessed letters: " . implode(', ', $guessedLetters) . "\n";
        echo self::$hangmanStates[count($game->getAttempts())] . "\n";
    }

    public static function showGameResult(Game $game): void
    {
        if ($game->isWon()) {
            echo "Congratulations! You won!\n";
        } else {
            echo "You didn't win!\n";
        }
        echo "Game Over! The word was: " . $game->getWord() . "\n";
    }

    public static function showGameStatistics(array $stats): void
    {
        echo "Total games played: " . $stats['total_games'] . "\n";
        echo "Total wins: " . $stats['total_wins'] . "\n";
        echo "Total losses: " . $stats['total_losses'] . "\n";
    }

    public static function showHelp(): void
    {
        echo "Usage: php hangman [options]\n";
        echo "Options:\n";
        echo "  --new, -n    Start a new game (default)\n";
        echo "  --list, -l   List all saved games\n";
        echo "  --statistics, -s   Show statistics of all games\n";
        echo "  --replay, -r <id>  Replay a game with the specified ID\n";
        echo "  --help, -h   Show this help message\n";
    }
}