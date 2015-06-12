<?php
namespace Adcuz\FatSecret;

class FatSecretException extends Exception{
	
    public function FatSecretException($code, $message)
    {
        parent::__construct($message, $code);
    }
}