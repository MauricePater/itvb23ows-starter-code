<?php

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Hive\Game\Logic;
use Hive\AI\AI;
use Hive\AI\AIMocks;

class AITest extends MockeryTestCase {
    public function testIfAIPlay(): void {
        $logic = new Logic();
        $ai = new AIMocks();
        $board = [];
        $hand = [0 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3],
                 1 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3]];
        $board = $logic->validPlay(0, $board, $hand[0], "Q", "0,0");
        $ai->aiSet(1, $board, ["play", "Q", "0,-1"]);
        $this->assertEquals('ai placed piece', $_SESSION['ai']);
    }

    public function testIfAIMove(): void {
        $ai = new AIMocks();
        $board = ["0,0" => [[0, "Q"]],
                  "0,1" => [[1, "Q"]],
                  "0,-1" => [[0, "A"]]];
        $ai->aiSet(1, $board, ["move", "0,1", "1,0"]);
        $this->assertEquals('ai moved piece', $_SESSION['ai']);
    }

    public function testIfAIPass(): void {
        $ai = new AIMocks();
        $board = ["0,0" => [[0, "Q"]],
                  "0,1" => [[1, "Q"]],
                  "0,-1" => [[0, "A"]]];
        $ai->aiSet(1, $board, ["pass", null, null]);
        $this->assertEquals('ai passed', $_SESSION['ai']);
    }

    public function testIfAIRespond(): void {
        $logic = new Logic();
        $ai = new AI();
        unset($_SESSION['error']);
        $url = 'http://localhost:5000';
        $board = [];
        $hand = [0 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3],
                 1 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3]];
        $player = 0;
        $board = $logic->validPlay($player, $board, $hand[$player], "Q", "0,0");
        $hand = [0 => ["Q" => 0, "B" => 2, "S" => 2, "A" => 3, "G" => 3],
                 1 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3]];
        $response = $ai->sendInteraction(1, $hand, $board, $url);
        $this->assertEquals(["play", "Q", "0,-1"], $response);
    }
}
