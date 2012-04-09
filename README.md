emome-imsp-sms
=====

PHP library for Emome IMSP SMS's HTTP API

## Usage

```php
$imsp = new Emome_IMSP_SMS("ACCOUNT", "PASSWORD");
$response = $imsp->submitSM(
  array("msg"     => "MESSAGE HERE",
        "to_addr" => "MOBILE PHONE NUMBER HERE"
));
var_dump($response);
```
