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
}
