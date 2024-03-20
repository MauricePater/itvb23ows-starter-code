<?php

use PHPUnit\Framework\TestCase;
use Hive\Game\Actions;
use Hive\Game\Logic;

class ActionsTest extends TestCase {
    public function testIfHandHasPiece(): void {
        $actions = new Actions();
        $hand = ["Q" => 0, "B" => 2, "S" => 2, "A" => 3, "G" => 3];
        $tiles = $actions->selectPiece($hand);
        $this->assertNotContains("Q", $tiles);
    }
    public function testIfTileIsEmpty(): void {
        $actions = new Actions();
        $logic = new Logic();
        $hand = ["Q" => 0, "B" => 2, "S" => 2, "A" => 3, "G" => 3];
        $board = ["0,0" => [[0, "Q"]], "0,1" => [[1, "Q"]]];
        $to = $logic->boardTiles($board);
        $positions = $actions->placePiece($hand, 0, $to, $board);
        $this->assertNotContains('0,0', $positions);
    }

    public function testIfNeighbourHasSameColor(): void {
        $actions = new Actions();
        $logic = new Logic();
        $hand = ["Q" => 0, "B" => 2, "S" => 2, "A" => 3, "G" => 3];
        $board = ["0,0" => [[0, "Q"]], "0,1" => [[1, "Q"]]];
        $to = $logic->boardTiles($board);
        $positions = $actions->placePiece($hand, 0, $to, $board);
        $this->assertNotContains('0,2', $positions);
    }

    public function testIfPlayerOwnsPiece(): void {
        $actions = new Actions();
        $board = ["0,0" => [[0, "Q"]], "0,1" => [[1, "Q"]]];
        $from = $actions->fromTile($board, 0);
        $this->assertNotContains('0,1', $from);
    }
}
