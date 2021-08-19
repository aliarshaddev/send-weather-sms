<?php
declare(strict_types=1);
require 'classes/RouteeAPI.php';
session_start();
$routee = new RouteeAPI();
try {
    $response =  $routee->sendSms(8133841 ,'+923224616165');
    echo $response;
}
catch (Exception $e)
{
    echo 'Message: ' .$e->getMessage();
    exit();
}

