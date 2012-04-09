<?php
Require __DIR__. "/src/EmomeIMSP/SMS.php";
use EmomeIMSP\SMS;

$imsp = new SMS("11582", "6158x");
$response = $imsp->submitSM(
  array("msg"     => "MESSAGE HERE",
        "to_addr" => "MOBILE PHONE NUMBER HERE"
));
var_dump($response);