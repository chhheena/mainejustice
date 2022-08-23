<?php 
$pharFile = 'autoload.phar';

// clean up
if (file_exists($pharFile)) {
    unlink($pharFile);
}

$p = new Phar($pharFile);

$p->buildFromDirectory('vendor/');

// pointing main file which requires all classes  
$p->setDefaultStub('autoload.php', '/autoload.php');

echo "$pharFile successfully created";