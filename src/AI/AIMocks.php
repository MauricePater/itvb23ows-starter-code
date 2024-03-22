<?php

namespace Hive\AI;

use Hive\AI\AI;
use Hive\Game\Logic;

include_once __DIR__ . '/../../vendor/autoload.php';


class AIMocks extends AI {

    public function aiSet($player, $board, $response){
        if ($response[0] == "play"){
            $this->aiPlay($player, $board, $response[1], $response[2]);
        }
        if ($response[0] == "move"){
            $this->aiMove($board, $response[1], $response[2]);
        }
        if ($response[0] == "pass"){
            $this->aiPassDatabase();
        }
    }

    public function aiPlay($player, $board, $piece, $to) {
        $logic = new Logic();
        $board[$to] = [[$player, $piece]];
        $logic->endOfGame($board);
        $this->aiPlayDatabase($player, $board, $piece, $to);
    }

    public function aiMove($board, $from, $to) {
        $logic = new Logic();
        $tile = array_pop($board[$from]);
        unset($board[$from]);
        isset($board[$to]) ? array_push($board[$to], $tile) : $board[$to] = [$tile];
        $logic->endOfGame($board);
        $this->aiMoveDatabase($board, $from, $to);
    }

    public function aiPlayDatabase($player, $board, $piece, $to) {
		$_SESSION['ai'] = 'ai placed piece';
    }
    public function aiMoveDatabase($board, $from, $to) {
		$_SESSION['ai'] = 'ai moved piece';
    }

    public function aiPassDatabase() {
		$_SESSION['ai'] = 'ai passed';
    }
}