<?php

namespace Hive\AI;

use Hive\Game\Logic;
use Hive\Database\Database;

include_once __DIR__ . '/../../vendor/autoload.php';

class AI {
    // Receive response from AI server as an array
    public function sendInteraction($moveNumber, $hand, $board, $url) {
        $content = [
            'move_number' => $moveNumber,
            'hand' => $hand,
            'board' => $board
        ];

        $options = [
            'http' => [
                'header' => "Content-Type: application/json",
                'method' => 'POST',
                'content' => json_encode($content),
            ],
        ];

        return json_decode(file_get_contents($url, false, stream_context_create($options)));
    }

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
        $logic = new Logic();
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
        $logic->redirect();
    }
    public function aiMoveDatabase($board, $from, $to) {
        $logic = new Logic();
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

    public function aiPassDatabase() {
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

    public function moveNumber() {
        $db = new Database();
        $connection = $db->getDatabase();
        $stmt = $connection->prepare('SELECT * FROM moves WHERE game_id = '.$_SESSION['game_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $moves = [];
        while ($row = $result->fetch_array()) {
            $moves[] = $row[2];
        }
        return count($moves);
    }
}
