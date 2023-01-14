<?php
// htmlspecialcharsを短くする
function h($value)
{
    return htmlspecialchars($value, ENT_QUOTES);
}


//DB接続
function dbconnect()
{
    $dsn  = 'mysql:dbname=min_bbs;host=localhost;';
    $user = "root";
    $pass = "";

    // 2. try&catchを使いつつPDOでデータベース接続
    try {
        $db = new PDO($dsn, $user, $pass);
        // $db->query("set names utf8");
        return $db;
    } catch (PDOException $e) {
        exit('データベースに接続できませんでした。' . $e->getMessage());
    }
}

//初期画像名
$first_image = 'figure_happy.png';
