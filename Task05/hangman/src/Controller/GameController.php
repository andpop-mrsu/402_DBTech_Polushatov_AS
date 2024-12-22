<?php

namespace aplou00\Hangman\Controller;

use aplou00\Hangman\View\GameView;
use aplou00\Hangman\Service\GameService;

class GameController
{
    private $gameService;

    public function __construct()
    {
        $this->gameService = new GameService();
    }

    public function run(array $args): void
    {
        if (count($args) === 1 || (isset($args[1]) && in_array($args[1], ['--new', '-n']))) {
            $this->gameService->startNewGame();
        } elseif (isset($args[1]) && in_array($args[1], ['--list', '-l'])) {
            $this->gameService->listGames();
        } elseif (isset($args[1]) && in_array($args[1], ['--statistics', '-s'])) {
            $this->gameService->showStatistics();
        } elseif (isset($args[1]) && in_array($args[1], ['--replay', '-r']) && isset($args[2])) {
            $this->gameService->replayGame((int) $args[2]);
        } elseif (isset($args[1]) && in_array($args[1], ['--help', '-h'])) {
            GameView::showHelp();
        } else {
            echo "Invalid arguments. Use --help to see available options.\n";
        }
    }
}