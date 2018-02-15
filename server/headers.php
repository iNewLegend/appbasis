<?php
/**
 * @file    : headers.php
 * @author  : Leonid Vinikov (czf.leo123@gmail.com)
 */

header('Access-Control-Allow-Origin: http://localhost:8080');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Hash");
    exit();
}
