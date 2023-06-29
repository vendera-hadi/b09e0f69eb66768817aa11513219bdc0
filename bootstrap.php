<?php
require 'vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = DotEnv::createImmutable(__DIR__);
$dotenv->load();

// load all controller & services
foreach (glob("source/*/*.php") as $filename) {
    include $filename;
}
