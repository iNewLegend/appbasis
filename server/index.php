<?php
/**
 * @file    : index.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 * @desc    : used for integrated HTTP server
 * ----
 * [ cmd format ] 
 * $params = 'parm1/param2/parm3/etc...'
 * controller/method/$params  
 */
require 'headers.php';
require 'init.php';

try {
    # Prepare 
    $cmd = $_SERVER["REQUEST_URI"];

    # Parse $_POST as cmd params
    foreach($_POST as $post) {
        $cmd . '/' . $post; 
    }
    
    # Get IP Address
    $ip = $_SERVER['REMOTE_ADDR'];

    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    # Initialize App
    $app = new \Core\App($cmd, $ip);

    # Get app output
    $r = $app->getOutput();

    # Print the result
    if (! empty($r)) {  
    	if (is_array($r)) {
            header('Content-Type: application/json');
            echo json_encode($r);
        } else {
            echo $r;
        }
    }
} catch (Exception $e) {
    exit($e->getMessage());
} // EOF index.php