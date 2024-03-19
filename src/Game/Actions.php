<?php

namespace Hive\Game;

include_once __DIR__ . '/../../vendor/autoload.php';

use Hive\Game\Logic;
use Hive\Database\Database;

$GLOBALS['OFFSETS'] = [[0, 1], [0, -1], [1, 0], [-1, 0], [-1, 1], [1, -1]];
define("DB_INSERT", 'insert into moves (game_id, type, move_from, move_to, previous_id, state)');

class Actions{

    public function moveStone($player, $board, $hand){
        $logic = new Logic();
        $from = $_POST['from'];
        $to = $_POST['to'];
        $logic->checkIfTileEmpty($board, $from);
        $logic->checkIfPieceOwned($board, $from, $player);
        $tile = array_pop($board[$from]);
        $all = array_keys($board);
        $queue = [array_shift($all)];
        while ($queue) {
            $next = explode(',', array_shift($queue));
            foreach ($GLOBALS['OFFSETS'] as $pq) {
                list($p, $q) = $pq;
                $p += $next[0];
                $q += $next[1];
                if (in_array("$p,$q", $all)) {
                    $queue[] = "$p,$q";
                    $all = array_diff($all, ["$p,$q"]);
                }
            }
        }
        $logic->checkIfTileNotEmpty($board, $to, $tile);
        $logic->checkIfQueenIsPlayed($hand);
        $logic->checkIfHiveSplit($to, $board, $all);
        $logic->checkIfHaveToMove($from, $to);
        $logic->checkIfHaveToSlide($tile, $board, $from, $to);
        if (isset($_SESSION['error'])) {
        return isset($board[$from]) ? array_push($board[$from], $tile) : $board[$from] = [$tile];
        }
        isset($board[$to]) ? array_push($board[$to], $tile) : $board[$to] = [$tile];
        $_SESSION['player'] = 1 - $_SESSION['player'];
        $db = new Database();
        $connection = $db->getDatabase();
        $values = 'values (?, "move", ?, ?, ?, ?)';
        $stmt = $connection->prepare(DB_INSERT.$values);
        $state = $db->getState();
        $stmt->bind_param('issis', $_SESSION['game_id'], $from, $to, $_SESSION['last_move'], $state);
        $stmt->execute();
        $_SESSION['last_move'] = $connection->insert_id;
        $_SESSION['board'] = $board;
        $logic->redirect();
    }

    public function passMove(){
        $logic = new Logic();
        $db = new Database();
        $connection = $db->getDatabase();
        $values = 'values (?, "pass", null, null, ?, ?)';
        $stmt = $connection->prepare(DB_INSERT.$values);
        $state = $db->getState();
        $stmt->bind_param('iis', $_SESSION['game_id'], $_SESSION['last_move'], $state);
        $stmt->execute();
        $_SESSION['last_move'] = $connection->insert_id;
        $_SESSION['player'] = 1 - $_SESSION['player'];
        $logic->redirect();
    }

    public function undoMove(){
        $logic = new Logic();
        $db = new Database();
        $connection = $db->getDatabase();
        $stmt = $connection->prepare('SELECT * FROM moves WHERE id = '.$_SESSION['last_move']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_array();
        $_SESSION['last_move'] = $result[5];
        $db->setState($result[6]);
        $logic->redirect();
    }

    public function playStone($player, $board, $hand){
        $logic = new Logic();
        $piece = $_POST['piece'];
        $to = $_POST['to'];
        $logic->checkIfHandHasPiece($hand, $piece);
        $logic->checkIfTileEmptyForPlacement($board, $to);
        $logic->checkIfQueenMustBePlayed($hand);
        $logic->checkIfTileHasNeighbour($board, $to);
        $logic->checkIfTileneighboursAreSameColor($hand, $player, $to, $board);
        if (isset($_SESSION['error'])) { return;}
        $_SESSION['board'][$to] = [[$_SESSION['player'], $piece]];
        $_SESSION['hand'][$player][$piece]--;
        $_SESSION['player'] = 1 - $_SESSION['player'];
        $db = new Database();
        $connection = $db->getDatabase();
        $values = 'values (?, "play", ?, ?, ?, ?)';
        $stmt = $connection->prepare(DB_INSERT.$values);
        $state = $db->getState();
        $stmt->bind_param('issis', $_SESSION['game_id'], $piece, $to, $_SESSION['last_move'], $state);
        $stmt->execute();
        $_SESSION['last_move'] = $connection->insert_id;
        $logic->redirect();
    }

    public function restartGame(){
        $logic = new Logic();
        $_SESSION['board'] = [];
        $_SESSION['hand'] = [0 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3],
                             1 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3]];
        $_SESSION['player'] = 0;
        $db = new Database();
        $connection = $db->getDatabase();
        $connection->prepare('INSERT INTO games VALUES ()')->execute();
        $_SESSION['game_id'] = $connection->insert_id;
        $logic->redirect();
    }

    public function selectPiece($hand){
        $pieces = [];
        foreach ($hand as $tile => $ct) {
            echo "<option value=\"$tile\">$tile</option>";
            array_push($pieces, $tile);
        }
        return $pieces;
    }
    public function fromTile($board){
        foreach (array_keys($board) as $pos) {
            echo "<option value=\"$pos\">$pos</option>";
        }
    }
    public function toTile($to){
        foreach ($to as $pos) {
            echo "<option value=\"$pos\">$pos</option>";
        }
    }
}
