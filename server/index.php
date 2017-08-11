<?php
/**
 * @file    : index.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */
require 'headers.php';

$cmd = '';

if(isset($_GET['cmd'])) {
    $cmd = $_GET['cmd'];
}

if($cmd == 'phpinfo') {
    phpinfo();
    exit();
}

require 'init.php';

try {
    $app = new \Core\App($cmd);
} catch(Exception $e) {
    exit($e->getMessage());
}