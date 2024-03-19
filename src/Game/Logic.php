<?php

namespace Hive\Game;

include_once __DIR__ . '/../../vendor/autoload.php';

$GLOBALS['OFFSETS'] = [[0, 1], [0, -1], [1, 0], [-1, 0], [-1, 1], [1, -1]];

class Logic{

    public function isNeighbour($a, $b) {
        $a = explode(',', $a);
        $b = explode(',', $b);
        if (($a[0] == $b[0] && abs($a[1] - $b[1]) == 1) ||
           ($a[1] == $b[1] && abs($a[0] - $b[0]) == 1) ||
           ($a[0] + $a[1] == $b[0] + $b[1])) {
            return true;
        }
        return false;
    }
    
    public function hasNeighBour($a, $board) {
        foreach (array_keys($board) as $b) {
            if ($this->isNeighbour($a, $b)) {
                return true;
            }
        }
    }
    
    public function neighboursAreSameColor($player, $a, $board) {
        foreach ($board as $b => $st) {
            if (!$st) {
                continue;
            }
            $c = $st[count($st) - 1][0];
            if ($c != $player && $this->isNeighbour($a, $b)) {
                return false;
            }
        }
        return true;
    }
    
    public function len($tile) {
        return $tile ? count($tile) : 0;
    }
    
    public function slide($board, $from, $to) {
        if ((!$this->hasNeighBour($to, $board)) || (!$this->isNeighbour($from, $to))) {
            return false;
        }
        $b = explode(',', $to);
        $common = [];
        foreach ($GLOBALS['OFFSETS'] as $pq) {
            $p = $b[0] + $pq[0];
            $q = $b[1] + $pq[1];
            if ($this->isNeighbour($from, $p.",".$q)) {
                $common[] = $p.",".$q;
            }
        }
        if (!$board[$common[0]] && !$board[$common[1]] && !$board[$from] && !$board[$to]) {
            return false;
        }
        $commonDistance = min($this->len($board[$common[0]]), $this->len($board[$common[1]]));
        $distance = max($this->len($board[$from]), $this->len($board[$to]));
        return $commonDistance <= $distance;
    }

    public function redirect(){
    header('Location: index.php');
    exit(0);
    }

    public function checkIfTileEmpty($board, $from){
        if (!isset($board[$from]) && !isset($_SESSION['error'])) {
            return $_SESSION['error'] = 'Tile is empty';
        }
    }

    public function checkIfTileNotEmpty($board, $to, $tile){
        if (isset($board[$to]) && $tile[1] != "B" && !isset($_SESSION['error'])) {
            return $_SESSION['error'] = 'Tile is not empty';
        }
    }

    public function checkIfTileEmptyForPlacement($board, $to){
        if (isset($board[$to]) && !isset($_SESSION['error'])) {
            return $_SESSION['error'] = 'Tile is not empty';
        }
    }

    public function checkIfPieceOwned($board, $from, $player){
        if ($board[$from][count($board[$from])-1][0] != $player && !isset($_SESSION['error'])) {
            return $_SESSION['error'] = "Piece is not owned by player";
        }
    }

    public function checkIfQueenIsPlayed($hand){
        if ($hand['Q'] && !isset($_SESSION['error'])) {
            return $_SESSION['error'] = "Queen bee is not played";
        }
    }

    public function checkIfQueenMustBePlayed($hand){
        if (array_sum($hand) <= 8 && $hand['Q'] && !isset($_SESSION['error'])) {
            return $_SESSION['error'] = 'Must play queen bee';
        }
    }

    public function checkIfHiveSplit($to, $board, $all){
        if ((!$this->hasNeighBour($to, $board) || $all) && !isset($_SESSION['error'])) {
            return $_SESSION['error'] = "Move would split hive";
        }
    }
    public function checkIfHaveToMove($from, $to){
        if ($from == $to && !isset($_SESSION['error'])) {
            return $_SESSION['error'] = 'Piece must move';
        }
    }

    public function checkIfHaveToSlide($tile, $board, $from, $to){
        if (($tile[1] == "Q" || $tile[1] == "B" && !$this->slide($board, $from, $to)) && !isset($_SESSION['error'])) {
            return $_SESSION['error'] = 'Piece must slide';
        }
    }

    public function checkIfHandHasPiece($hand, $piece){
        if (!$hand[$piece] && !isset($_SESSION['error'])) {
            return $_SESSION['error'] = "Player does not have Piece";
        }
    }

    public function checkIfTileHasNeighbour($board, $to){
        if (count($board) && !$this->hasNeighBour($to, $board) && !isset($_SESSION['error'])) {
            return $_SESSION['error'] = "Tile has no neighbour";
        }
    }

    public function checkIfTileneighboursAreSameColor($hand, $player, $to, $board){
        if (array_sum($hand) < 11 &&
            !$this->neighboursAreSameColor($player, $to, $board) &&
            !isset($_SESSION['error'])) {
            return $_SESSION['error'] = "Board position has opposing neighbour";
        }
    }
}
