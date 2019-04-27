<?php
    try {
      $db = new PDO('mysql:dbname=mini_service;host=127.0.0.1;charset=utf8','root', 'mamoru');
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      echo 'DB接続エラー： ' . $e->getMessage();
    }
?>
