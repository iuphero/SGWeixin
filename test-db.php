<?php
function get_db($host, $dbname, $user, $pass){
    $text = sprintf("mysql:host=%s;dbname=%s", $host, $dbname);
    $dbh = new PDO($text, $user, $pass, [PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8']);
    return $dbh;
}