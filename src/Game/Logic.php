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
    
    public function hasNeighbour($a, $board) {
        $b = explode(',', $a);

        foreach ($GLOBALS['OFFSETS'] as $pq) {
            $p = $b[0] + $pq[0];
            $q = $b[1] + $pq[1];

            $position = $p . "," . $q;

            if (isset($board[$position]) && $this->isNeighbour($a, $position)) {
                return true;
            }
        }
        return false;
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
        if ((!$this->hasNeighbour($to, $board)) || (!$this->isNeighbour($from, $to))) {
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

        if ((!isset($board[$common[0]]) || !$board[$common[0]]) &&
            (!isset($board[$common[1]]) || !$board[$common[1]]) &&
            (!isset($board[$from]) || !$board[$from]) &&
            (!isset($board[$to]) || !$board[$to])) {
            return false;
        }
        $commonDistance = min($this->len($board[$common[0]] ?? 0), $this->len($board[$common[1]] ?? 0));
        $distance = max($this->len($board[$from] ?? 0), $this->len($board[$to] ?? 0));
        return $commonDistance <= $distance;
    }


    public function boardTiles($board){
        $to = [];
        foreach ($GLOBALS['OFFSETS'] as $pq) {
            foreach (array_keys($board) as $pos) {
                $pq2 = explode(',', $pos);
                $to[] = ($pq[0] + $pq2[0]).','.($pq[1] + $pq2[1]);
            }
        }
        $to = array_unique($to);
        if (!count($to)) { $to[] = '0,0'; }
        return $to;
    }

    public function redirect(){
        header('Location: index.php');
        exit(0);
    }

    public function validPlay($player, $board, $hand, $piece, $to){
        if (!$hand[$piece] && !isset($_SESSION['error'])) {
            $_SESSION['error'] = "Player does not have Piece";
        }
        if (isset($board[$to]) && !isset($_SESSION['error'])) {
            $_SESSION['error'] = 'Tile is not empty';
        }
        if (array_sum($hand) <= 8 && $hand['Q'] && $piece != "Q" && !isset($_SESSION['error'])) {
            $_SESSION['error'] = 'Must play queen bee';
        }
        if (count($board) && !$this->hasNeighbour($to, $board) && !isset($_SESSION['error'])) {
            $_SESSION['error'] = "Tile has no neighbour";
        }
        if (array_sum($hand) < 11 &&
            !$this->neighboursAreSameColor($player, $to, $board) &&
            !isset($_SESSION['error'])) {
            $_SESSION['error'] = "Board position has opposing neighbour";
        }
    }

    public function validMove($player, $board, $hand, $from, $to){
        if (!isset($board[$from]) && !isset($_SESSION['error'])) {
            $_SESSION['error'] = 'Tile is empty';
        }
        if ($board[$from][count($board[$from])-1][0] != $player && !isset($_SESSION['error'])) {
            $_SESSION['error'] = "Piece is not owned by player";
        }
        $currentBoard = $board;
        $tile = array_pop($board[$from]);
        $all = $this->getSplitPieces($board);
        if (isset($board[$to]) && $tile[1] != "B" && !isset($_SESSION['error'])) {
            $_SESSION['error'] = 'Tile is not empty';
        }
        if ($hand['Q'] && !isset($_SESSION['error'])) {
            $_SESSION['error'] = "Queen bee is not played";
        }
        if ((!$this->hasNeighbour($to, $board) || $all) && !isset($_SESSION['error'])) {
            $_SESSION['error'] = "Move would split hive";
        }
        if ($from == $to && !isset($_SESSION['error'])) {
            $_SESSION['error'] = 'Piece must move';
        }
        if (($tile[1] == "Q" || $tile[1] == "B") && !$this->slide($currentBoard, $from, $to) &&
             !isset($_SESSION['error'])) {
            $_SESSION['error'] = 'Piece must slide';
        }
        return array($tile, $board);
    }

    public function getSplitPieces($board){
        $all = array_keys($board);
        $queue = [array_shift($all)];
        while ($queue) {
            $next = explode(',', array_shift($queue));
            foreach ($GLOBALS['OFFSETS'] as $pq) {
                list($p, $q) = $pq;
                $p += $next[0];
                $q += $next[1];
                $position = $p . "," . $q;
                if (in_array($position, $all)) {
                    $queue[] = $position;
                    $all = array_diff($all, [$position]);
                }
            }
        }
        return $all;
    }
}
