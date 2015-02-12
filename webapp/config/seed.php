<?php
require __DIR__ . '/../lib/autoload.php';

use Symfony\Component\Yaml\Parser;

$yaml = new Parser();
$users = $yaml->parse(file_get_contents(__DIR__ . '/test/users.yml'));

var_dump($users);