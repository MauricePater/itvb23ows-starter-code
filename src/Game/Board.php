<?php

namespace Hive\Game;

include_once __DIR__ . '/../../vendor/autoload.php';

use Hive\Database\Database;

class Board{

public function drawBoard($board){
        $min_p = 1000;
        $min_q = 1000;
        foreach ($board as $pos => $tile) {
            $pq = explode(',', $pos);
            if ($pq[0] < $min_p) { $min_p = $pq[0]; }
            if ($pq[1] < $min_q) { $min_q = $pq[1]; }
        }
        foreach (array_filter($board) as $pos => $tile) {
            $pq = explode(',', $pos);
            $pq[0];
            $pq[1];
            $h = count($tile);
            echo '<div class="tile player';
            echo $tile[$h-1][0];
            if ($h > 1) { echo ' stacked'; }
            echo '" style="left: ';
            echo ($pq[0] - $min_p) * 4 + ($pq[1] - $min_q) * 2;
            echo 'em; top: ';
            echo ($pq[1] - $min_q) * 4;
            echo "em;\">($pq[0],$pq[1])<span>";
            echo $tile[$h-1][1];
            echo '</span></div>';
        }
    }

    public function drawBlack($hand){
        echo "Black:";
        foreach ($hand[1] as $tile => $ct) {
            for ($i = 0; $i < $ct; $i++) {
                echo '<div class="tile player1"><span>'.$tile."</span></div> ";
            }
        }
    }

    
    public function drawWhite($hand){
        echo "White:";
        foreach ($hand[0] as $tile => $ct) {
            for ($i = 0; $i < $ct; $i++) {
                echo '<div class="tile player0"><span>'.$tile."</span></div> ";
            }
        }
    }

    public function turn($player){
        echo "Turn:";
        if ($player == 0) { echo "White"; } else { echo "Black"; }
    }

    public function drawMoves(){
        $db = new Database();
        $connection = $db->getDatabase();
        $stmt = $connection->prepare('SELECT * FROM moves WHERE game_id = '.$_SESSION['game_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_array()) {
            echo '<li>'.$row[2].' '.$row[3].' '.$row[4].'</li>';
        }
    }
}
