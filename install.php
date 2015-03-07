<?php
require __DIR__ . '/webapp/autoload.php';

use Symfony\Component\Yaml\Parser;
$yaml = new Parser();
$data = $yaml->parse(file_get_contents(__DIR__ . '/.travis.yml'));

foreach($data['install'] as $installStep) {
    echo shell_exec($installStep);
}

foreach($data['before_script'] as $installStep) {
    echo shell_exec($installStep);
}

echo "
SmartResolution is now installed. You may want to remove install.php as a security precaution, otherwise you might overwrite the production database!
";