<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['protocol'] = $_ENV['SMTP_PROTOCOL'] ?? 'smtp';
$config['smtp_host'] = $_ENV['SMTP_HOST'] ?? '';
$config['smtp_user'] = $_ENV['SMTP_USER'] ?? '';
$config['smtp_pass'] = $_ENV['SMTP_PASS'] ?? '';
$config['smtp_port'] = $_ENV['SMTP_PORT'] ?? 465;
$config['smtp_crypto'] = $_ENV['SMTP_CRYPTO'] ?? 'ssl';
$config['mailtype'] = $_ENV['SMTP_MAILTYPE'] ?? 'html';
$config['charset'] = $_ENV['SMTP_CHARSET'] ?? 'utf-8';
$config['newline'] = "\r\n";
$config['crlf'] = "\r\n";
