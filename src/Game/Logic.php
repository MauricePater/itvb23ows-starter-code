<?php

namespace Hive\Game;

use Hive\Game\Actions;

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

    public function grasshopperJump($board, $from, $to) {
        $fromCoordinates = explode(',', $from);
        $toCoordinates = explode(',', $to);

        if ($fromCoordinates[0] == $toCoordinates[0]) {
            $offset = ($fromCoordinates[1] > $toCoordinates[1]) ? [0, -1] : [0, 1];
        }
        elseif ($fromCoordinates[1] == $toCoordinates[1]) {
            $offset = ($fromCoordinates[0] > $toCoordinates[0]) ? [-1, 0] : [1, 0];
        }
        elseif ($fromCoordinates[1] == $toCoordinates[1] - ($fromCoordinates[0] - $toCoordinates[0])) {
            $offset = ($fromCoordinates[0] > $toCoordinates[0]) ? [-1, 1] : [1, -1];
        }
        else {
            return false;
        }

        $p = $fromCoordinates[0] + $offset[0];
        $q = $fromCoordinates[1] + $offset[1];
        $position = $p . "," . $q;
        $positionCoordinates = [$p, $q];

        while (isset($board[$position])) {
            $p = $positionCoordinates[0] + $offset[0];
            $q = $positionCoordinates[1] + $offset[1];
            $position = $p . "," . $q;
            $positionCoordinates = [$p, $q];
            if ($position == $to) {
                return true;
            }
        }
        return false;
    }

    public function antSlide($board, $from, $to) {
        $steps = [];
        $tiles = array($from);

        while (!empty($tiles)) {
            $currentTile = array_shift($tiles);

            if (!in_array($currentTile, $steps)) {
                $steps[] = $currentTile;
                $b = explode(',', $currentTile);

                foreach ($GLOBALS['OFFSETS'] as $pq) {
                    $p = $b[0] + $pq[0];
                    $q = $b[1] + $pq[1];
                    $position = $p . "," . $q;

                    if (
                        !in_array($position, $steps) &&
                        !isset($board[$position]) &&
                        $this->hasNeighbour($position, $board)
                    ) {
                        if ($position == $to) {
                            return true;
                        }
                        $tiles[] = $position;
                    }
                }
            }
        }
        return false;
    }

    public function spiderSlide($board, $from, $to) {
        $steps = [];
        $tiles = [$from];
        $tiles[] = null;
        $prevTile = null;
        $depth = 0;

        while (!empty($tiles) && $depth < 3) {
            $currentTile = array_shift($tiles);

            if ($currentTile === null) {
                $depth++;
                $tiles[] = null;
                if ($tiles[0] === null) {
                    break;
                }
                continue;
            }

            if (!in_array($currentTile, $steps)) {
                $steps[] = $currentTile;
            }
            $b = explode(',', $currentTile);

            foreach ($GLOBALS['OFFSETS'] as $pq) {
                $p = $b[0] + $pq[0];
                $q = $b[1] + $pq[1];
                $position = $p . "," . $q;

                if (
                    !in_array($position, $steps) &&
                    $position !== $prevTile &&
                    !isset($board[$position]) &&
                    $this->hasNeighbour($position, $board)
                ) {
                    if ($position === $to && $depth === 2) {
                        return true;
                    }
                    $tiles[] = $position;
                }
            }

            $prevTile = $currentTile;
        }

        return false;
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
        unset($board[$from]);
        if (($tile[1] == "Q" || $tile[1] == "A" || $tile[1] == "S") &&
        $this->checkIfPiecesArePushed($board, $from) && !isset($_SESSION['error'])) {
            $_SESSION['error'] = "Piece is stuck";
        }
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
        if (($tile[1] == "Q" || $tile[1] == "A" || $tile[1] == "S") &&
        $this->checkIfPiecesArePushed($board, $to) && !isset($_SESSION['error'])) {
            $_SESSION['error'] = "Pieces get pushed";
        }
        if ($from == $to && !isset($_SESSION['error'])) {
            $_SESSION['error'] = 'Piece must move';
        }
        if (($tile[1] == "Q" || $tile[1] == "B") && !$this->slide($currentBoard, $from, $to) &&
             ($tile[1] == "A" && !$this->antSlide($currentBoard, $from, $to)) && !isset($_SESSION['error'])) {
            $_SESSION['error'] = 'Piece must slide';
        }
        if (($tile[1] == "S" && !$this->spiderSlide($board, $from, $to)) &&
             !isset($_SESSION['error'])) {
              $_SESSION['error'] = 'Invalid spider slide';
        }
        if (($tile[1] == "G" && !$this->grasshopperJump($board, $from, $to)) &&
             !isset($_SESSION['error'])) {
              $_SESSION['error'] = 'Invalid grasshopper jump';
        }
        if (isset($_SESSION['error'])) {
        return isset($board[$from]) ? array_push($board[$from], $tile) : $board[$from] = [$tile];
        }
        isset($board[$to]) ? array_push($board[$to], $tile) : $board[$to] = [$tile];
        $this->endOfGame($board);
        return $board;
    }

    public function boardTiles($board) {
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

    public function redirect() {
        header('Location: index.php');
        exit(0);
    }

    public function validPlay($player, $board, $hand, $piece, $to) {
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
        if (isset($_SESSION['error'])) {return; }
        $board[$to] = [[$player, $piece]];
        return $board;
    }

    public function checkIfPiecesArePushed($board, $position) {
        $b = explode(',', $position);
        $neighbouringTiles = [];
        foreach ($GLOBALS['OFFSETS'] as $pq) {
            $p = $b[0] + $pq[0];
            $q = $b[1] + $pq[1];

            $neighbouringTiles[] = $p . "," . $q;
        }
        if((isset($board[$neighbouringTiles[2]]) &&
            isset($board[$neighbouringTiles[3]]) &&
            isset($board[$neighbouringTiles[4]]) &&
            isset($board[$neighbouringTiles[5]])) ||
           (isset($board[$neighbouringTiles[0]]) &&
            isset($board[$neighbouringTiles[1]]) &&
            isset($board[$neighbouringTiles[2]]) &&
            isset($board[$neighbouringTiles[3]])) ||
           (isset($board[$neighbouringTiles[0]]) &&
            isset($board[$neighbouringTiles[1]]) &&
            isset($board[$neighbouringTiles[4]]) &&
            isset($board[$neighbouringTiles[5]]))) {
             return true;
        }
        return false;
    }

    public function getSplitPieces($board) {
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

    public function validPass($player, $board, $hand) {
        $to = $this->boardTiles($board);
        foreach ($to as $pos) {
            foreach ($hand as $piece => $amount) {
                if ($amount > 0 && $this->validPlay($player, $board, $hand, $piece, $pos)) {
                     $_SESSION['error'] ="Play is still possible";
                    return;
                }
            }
        }
        foreach ($board as $tile => $pieces) {
            foreach ($to as $pos) {
                if (end($pieces)[0] == $player && $this->validMove($player, $board, $hand, $tile, $pos)) {
                    $_SESSION['error'] ="Move is still possible";
                    return;
                }
            }
        }
    }

    public function endOfGame($board) {
        $white = false;
        $black = false;
        foreach ($board as $tile => $pieces) {
            $b = explode(',', $tile);
            $neighbouringPieces = 0;
            foreach ($GLOBALS['OFFSETS'] as $pq) {
                $p = $b[0] + $pq[0];
                $q = $b[1] + $pq[1];
                if(isset($board[$p . "," . $q])){
                    $neighbouringPieces += 1;
                }
            }
            if ($pieces[0][0] == 0 && $pieces[0][1] == 'Q' && $neighbouringPieces == 6) {
                $white = true;
            }
            if ($pieces[0][0] == 1 && $pieces[0][1] == 'Q' && $neighbouringPieces == 6) {
                $black = true;
            }
        }
        if ($white && $black) { $_SESSION['game'] ="Draw"; }
        if ($white && !$black) { $_SESSION['game'] ="White won"; }
        if (!$white && $black) { $_SESSION['game'] ="Black won"; }
    }

    public function validUndo($board, $lastMove) {
        $actions = new Actions();
        if($board == []) {
            $_SESSION['error'] ="Board is empty";
            return;
        }
        if($lastMove == null) {
            $actions->restartGame();
        }
    }
}
