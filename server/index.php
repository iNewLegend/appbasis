<?php
/**
 * file 		: index.php
 * author 	: czf.leo123@gmail.com
 * todo		:
 */
require_once 'init.php';

$cmd = '';

if(isset($_GET['cmd'])) {
    $cmd = $_GET['cmd'];
}

try {
    $core = new Core($cmd);
} catch(Exception $e) {
    exit($e->getMessage());
}
