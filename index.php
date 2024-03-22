<?php
    session_start();

    require_once __DIR__ . '/vendor/autoload.php';

    use Hive\Game\Actions;
    use Hive\Game\Logic;
    use Hive\Game\Board;

    $actions = new Actions();
    $logic = new Logic();
    $view = new Board();

    if (!isset($_SESSION['board'])) {
        $actions->restartGame();
    }
    $board = $_SESSION['board'];
    $player = $_SESSION['player'];
    $hand = $_SESSION['hand'];
    $to = $logic->boardTiles($board);
    if(isset($_POST['play']) && !isset($_SESSION['game'])) {
        $actions->playPiece($player, $board, $hand[$player], $_POST['piece'], $_POST['to']);
        
    }
    if(isset($_POST['move']) && !isset($_SESSION['game'])) {
        $actions->movePiece($player, $board, $hand[$player], $_POST['from'], $_POST['to']);
    }
    if(isset($_POST['pass']) && !isset($_SESSION['game'])) {
        $actions->passMove($player, $board, $hand[$player]);
    }
    if(isset($_POST['restart'])) {
        $actions->restartGame();
    }
    if(isset($_POST['undo']) && !isset($_SESSION['game'])) {
        $actions->undoMove();
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Hive</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="board">
            <?php $view->drawBoard($board); ?>
        </div>
        <div class="hand">
            <?php $view->drawWhite($hand); ?>
        </div>
        <div class="hand">
            <?php $view->drawBlack($hand); ?>
        </div>
        <div class="turn">
            <?php $view->turn($player); ?>
        </div>
        <form method="post">
            <select name="piece">
                <?php $actions->selectPiece($hand[$player]); ?>
            </select>
            <select name="to">
                <?php $actions->placePiece($hand[$player], $player, $to, $board); ?>
            </select>
            <input type="submit" name="play" value="Play">
        </form>

        <form method="post">
            <select name="from">
                <?php $actions->fromTile($board, $player); ?>
            </select>
            <select name="to">
                <?php $actions->toTile($to); ?>
            </select>
            <input type="submit" name="move" value="To">
        </form>

        <form method="post">
            <input type="submit" name="pass" value="Pass">
        </form>

        <form method="post">
            <input type="submit" name="restart" value="Restart">
        </form>

        <strong><?php if (isset($_SESSION['error'])) { echo $_SESSION['error']; unset($_SESSION['error']); }
                      if (isset($_SESSION['game'])) { echo $_SESSION['game']; unset($_SESSION['game']); } ?>
        </strong>
        <ol>
            <?php $view->drawMoves(); ?>
        </ol>
        <form method="post">
            <input type="submit" name="undo" value="Undo">
        </form>
    </body>
</html>
