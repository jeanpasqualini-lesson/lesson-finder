<?php

require "../vendor/autoload.php";

define("ROOT_DIR", __DIR__);

$tests = array(
    new \Test\MainTest()
);

foreach($tests as $test)
{
    echo "===".get_class($test)."===".PHP_EOL;

    $test->runTest();
}