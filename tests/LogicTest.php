<?php

use PHPUnit\Framework\TestCase;
use Hive\Game\Logic;

class LogicTest extends TestCase {
    public function testIfPieceCanBePlaced(): void {
        $logic = new Logic();
        unset($_SESSION['error']);
        $hand = ["Q" => 0, "B" => 2, "S" => 2, "A" => 3, "G" => 3];
        $board = ["0,0" => [[0, "Q"]], "1,0" => [[1, "Q"]]];
        $logic->validPlay(0, $board, $hand, "B", "0,0");
        $this->assertTrue(isset($_SESSION['error']));
    }
    public function testIfQueenCanMove(): void {
        $logic = new Logic();
        unset($_SESSION['error']);
        $hand = ["Q" => 0, "B" => 2, "S" => 2, "A" => 3, "G" => 3];
        $board = ["0,0" => [[0, "Q"]], "1,0" => [[1, "Q"]]];
        $logic->validMove(0, $board, $hand, "0,0", "0,1");
        $this->assertTrue(!isset($_SESSION['error']));
    }

    public function testIfQueenCanBePlacedFourthTurn(): void {
        $logic = new Logic();
        unset($_SESSION['error']);
        $hand = ["Q" => 1, "B" => 0, "S" => 1, "A" => 3, "G" => 3];
        $board = ["0,0" => [[0, "B"]],
                  "0,1" => [[1, "B"]],
                  "0,-1" => [[0, "B"]],
                  "0,2" => [[1, "B"]],
                  "0,-2" => [[0, "S"]],
                  "0,3" => [[1, "S"]]];
        $logic->validPlay(0, $board, $hand, "Q", "0,-3");
        $this->assertTrue(!isset($_SESSION['error']));
    }
}
