<?php
/**
 * This installer sets up SmartResolution. Call in one of the following ways.
 * The first way does a full install, the second only cleans and re-initialises the database.
 *
 * php install.php
 *
 * php install.php --refresh
 */
require __DIR__ . '/../webapp/autoload.php';

use Symfony\Component\Yaml\Parser;
$yaml = new Parser();
$data = $yaml->parse(file_get_contents(__DIR__ . '/../.travis.yml'));

$fullInstall = true;

if (isset($argv[1]) && $argv[1] === '--refresh') {
    $fullInstall = false;
}

if ($fullInstall) {
    foreach($data['install'] as $installStep) {
        echo shell_exec($installStep);
    }
}

foreach($data['before_script'] as $installStep) {
    echo shell_exec($installStep);
}

if ($fullInstall) {
    echo "\nSmartResolution is now installed. You may want to remove install.php as a security precaution, otherwise you might overwrite the production database!";
}
else {
    echo "Database cleaned and repopulated.";
}

echo "\n";
