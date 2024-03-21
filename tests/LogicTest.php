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

    public function testIfPieceMovedAnotherPieceCanBePlaced(): void {
        $logic = new Logic();
        unset($_SESSION['error']);
        $hand = ["Q" => 0, "B" => 0, "S" => 1, "A" => 2, "G" => 3];
        $board = ["0,0" => [[0, "Q"]],
                  "0,1" => [[1, "Q"]],
                  "0,-1" => [[0, "A"]],
                  "0,2" => [[1, "A"]]];
        $board = $logic->validMove(0, $board, $hand, "0,-1", "1,-1");
        $board = $logic->validPlay(1, $board, $hand, "A", "0,3");
        $logic->validPlay(0, $board, $hand, "A", "0,-1");
        $this->assertTrue(!isset($_SESSION['error']));
    }

    public function testIfGrasshopperCanJump(): void {
        $logic = new Logic();
        unset($_SESSION['error']);
        $hand = ["Q" => 0, "B" => 0, "S" => 1, "A" => 2, "G" => 3];
        $board = ["0,0" => [[0, "Q"]],
                  "0,1" => [[1, "Q"]],
                  "0,-1" => [[0, "G"]],
                  "0,2" => [[1, "A"]]];
        $logic->validMove(0, $board, $hand, "0,-1", "0,3");
        $this->assertTrue(!isset($_SESSION['error']));
    }

        public function testIfGrasshopperJumpInvalid(): void {
        $logic = new Logic();
        unset($_SESSION['error']);
        $hand = ["Q" => 0, "B" => 0, "S" => 1, "A" => 2, "G" => 3];
        $board = ["0,0" => [[0, "Q"]],
                  "0,1" => [[1, "Q"]],
                  "0,-1" => [[0, "G"]],
                  "0,2" => [[1, "A"]]];
        $board = $logic->validMove(0, $board, $hand, "0,-1", "1,-1");
        $this->assertTrue(isset($_SESSION['error']));
    }
}
