<?php

namespace Hive\Game;

include_once __DIR__ . '/../../vendor/autoload.php';

use Hive\AI\AI;
use Hive\Game\Logic;
use Hive\Database\Database;

$GLOBALS['OFFSETS'] = [[0, 1], [0, -1], [1, 0], [-1, 0], [-1, 1], [1, -1]];
define("DB_INSERT", 'insert into moves (game_id, type, move_from, move_to, previous_id, state)');
define("URL", 'http://hive-ai:5000');
class Actions {
    
    public function playPiece($player, $board, $hand, $piece, $to) {
        $logic = new Logic();
        $board = $logic->validPlay($player, $board, $hand, $piece, $to);
        if (isset($_SESSION['error'])) { return;}
        $_SESSION['board'] = $board;
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
        $ai = new AI();
        $ai->aiSet($_SESSION['player'], $board,
        $ai->sendInteraction($ai->moveNumber(), $_SESSION['hand'], $board, URL));
        $logic->redirect();
    }

    public function movePiece($player, $board, $hand, $from, $to) {
        $logic = new Logic();
        $board = $logic->validMove($player, $board, $hand, $from, $to);
        if (isset($_SESSION['error'])) { return; }
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
        $ai = new AI();
        $ai->aiSet($_SESSION['player'], $board,
        $ai->sendInteraction($ai->moveNumber(), $_SESSION['hand'], $board, URL));
        $logic->redirect();
    }

    public function passMove($player, $board, $hand) {
        $logic = new Logic();
        $logic->validPass($player, $board, $hand);
        if (isset($_SESSION['error'])) { return; }
        $db = new Database();
        $connection = $db->getDatabase();
        $values = 'values (?, "pass", null, null, ?, ?)';
        $stmt = $connection->prepare(DB_INSERT.$values);
        $state = $db->getState();
        $stmt->bind_param('iis', $_SESSION['game_id'], $_SESSION['last_move'], $state);
        $stmt->execute();
        $_SESSION['last_move'] = $connection->insert_id;
        $_SESSION['player'] = 1 - $_SESSION['player'];
        $ai = new AI();
        $ai->aiSet($_SESSION['player'], $board,
        $ai->sendInteraction($ai->moveNumber(), $_SESSION['hand'], $board, URL));
        $logic->redirect();
    }

    public function undoMove($board, $lastMove) {
        $logic = new Logic();
        $logic->validUndo($board, $lastMove);
        if (isset($_SESSION['error'])) { return; }
        $db = new Database();
        $connection = $db->getDatabase();
        $stmt = $connection->prepare('SELECT * FROM moves WHERE id = '.$lastMove-1);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_array();
        if($result == null || $result[5] == null){
            $this->restartGame();
            return;
        }
        $_SESSION['last_move'] = $result[5];
        $stmt = $connection->prepare('SELECT * FROM moves WHERE id = '.($_SESSION['last_move']));
        $stmt->execute();
        $result = $stmt->get_result()->fetch_array();
        if($result == null){
            $this->restartGame();
            return;
        }
        $db->setState($result[6]);
        $stmt = $connection->prepare('DELETE FROM moves WHERE id > '.$_SESSION['last_move']);
        $stmt->execute();
        $logic->redirect();
    }

    public function restartGame() {
        $logic = new Logic();
        unset($_SESSION['game']);
        $_SESSION['board'] = [];
        $_SESSION['hand'] = [0 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3],
                             1 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3]];
        $_SESSION['player'] = 0;
        $db = new Database();
        $connection = $db->getDatabase();
        $connection->prepare('INSERT INTO games VALUES ()')->execute();
        $_SESSION['game_id'] = $connection->insert_id;
        $stmt = $connection->prepare('DELETE FROM moves WHERE id > 0');
        $stmt->execute();
        $logic->redirect();
    }

    public function selectPiece($hand) {
        $pieces = [];
        echo "\n";
        foreach ($hand as $tile => $ct) {
            if($ct > 0){
            echo "\n";
            echo "<option value=\"$tile\">$tile</option>";
            array_push($pieces, $tile);
            }
        }
        return $pieces;
    }

    public function placePiece($hand, $player, $to, $board) {
        $logic = new Logic();
        $positions = [];
        echo "\n";
        foreach ($to as $pos) {
            if(array_sum($hand) > 10 && !array_key_exists($pos, $board) ||
               $logic->neighboursAreSameColor($player, $pos, $board) && !array_key_exists($pos, $board)){
                echo "\n";
                echo "<option value=\"$pos\">$pos</option>";
                array_push($positions, $pos);
            }
        }
        return $positions;
    }

    public function fromTile($board, $player) {
        $positions = [];
        echo "\n";
        foreach (array_keys($board) as $pos) {
            if($player == $board[$pos][0][0]){
                echo "\n";
                echo "<option value=\"$pos\">$pos</option>";
                array_push($positions, $pos);
            }
        }
        return $positions;
    }
    public function toTile($to) {
        $positions = [];
        echo "\n";
        foreach ($to as $pos) {
            echo "\n";
            echo "<option value=\"$pos\">$pos</option>";
            array_push($positions, $pos);
        }
        return $positions;
    }
}
