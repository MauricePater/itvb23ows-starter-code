<?php

namespace Hive\Database;

use mysqli;

class Database{
    
    public function getDatabase() {
        return new mysqli('hive-database', 'root', 'hive_password_123', 'hive_database');
    }

    function getState() {
        return serialize([$_SESSION['hand'], $_SESSION['board'], $_SESSION['player']]);
    }

    function setState($state) {
        list($a, $b, $c) = unserialize($state);
        $_SESSION['hand'] = $a;
        $_SESSION['board'] = $b;
        $_SESSION['player'] = $c;
    }
}

