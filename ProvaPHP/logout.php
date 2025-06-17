<?php
    session_start();
    require_once 'conexao.php';
    header("Location: login.php");
    exit();