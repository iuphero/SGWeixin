<?php
function get_db($host, $dbname, $user, $pass){
    $text = sprintf("mysql:host=%s;dbname=%s", $host, $dbname);
    $dbh = new PDO($text, $user, $pass, [PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8']);
    return $dbh;
}


function get_text($conn, $name){
    $sql = sprintf("select name, style_name, sex, ts, wl, zl, zz, ml, native_place,
history_dpt, novel_dpt, assessment, office, live_year, die_year
from person where name like '%s' or alias like '%s' ", $name.'%', $name.'%');

    $results = $conn->query($sql);
    print $results->rowCount();
    $row = $results->fetch();
    print $row['name'];
    // foreach ($conn->query($sql) as $row) {
    //     $assessment = $row['assessment'];
    //     $ass = str_replace('##', "\r\n", $assessment);
    //     print($ass);
    //     print($row['history_dpt']);
    // }
    return 1;
}


// $conn = get_db('127.0.0.1',  'sanguo', 'xfight', 'wgmmla');
$conn1 = get_db('107.170.133.81', 'sanguo', 'root', 'wgmmla');
get_text($conn, '孙尚香');