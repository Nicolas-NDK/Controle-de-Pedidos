<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config = array(
    'protocol'  => 'smtp',
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_user' => 'email@gmail.com',
    'smtp_pass' => 'senha',
    'smtp_crypto' => 'tls',    
    'mailtype'  => 'text',
    'charset'   => 'utf-8',
    'wordwrap'  => TRUE,
    'newline'   => "\r\n",
);
