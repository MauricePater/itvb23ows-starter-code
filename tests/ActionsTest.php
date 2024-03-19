<?php

use PHPUnit\Framework\TestCase;
use Hive\Game\Actions;

class ActionsTest extends TestCase {
    public function testIfStillHasPiece(): void {
        $actions = new Actions();
        $hand = ["Q" => 0, "B" => 2, "S" => 2, "A" => 3, "G" => 3];
        $tiles = $actions->selectPiece($hand);
        $this->assertNotContains("Q", $tiles);
    }
}
