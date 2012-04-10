emome-imsp-sms 中華電信 emome 簡訊特碼 PHP client library
=====

PHP library for Emome IMSP SMS's HTTP API

## Usage

```php
<?php
use EmomeIMSP\SMS;

require __DIR__. "/src/EmomeIMSP/SMS.php";


$imsp = new SMS("ACCOUNT", "PASSWORD");
$response = $imsp->submitSM(
  array("msg"     => "MESSAGE HERE",
        "to_addr" => "MOBILE PHONE NUMBER HERE"
));
var_dump($response);
```
