<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once __DIR__ . '/../vendor/autoload.php';

$config = include __DIR__ . '/parameters.php';
$db = $config['database']['development'];
$dbParams = array(
    'dbname'    => $db['database'],
    'user'      => $db['username'],
    'password'  => $db['password'],
    'host'      => $db['host'],
    'driver'    => 'pdo_mysql',
    'driverOptions' => array(
        1002 => "SET NAMES {$db['charset']}"
    )
);
$paths = array(
    __DIR__ . '/../src/Model',
    __DIR__ . '/metadata'
);
$isDevMode = true;

$config = Setup::createYAMLMetadataConfiguration($paths, $isDevMode);
$entityManager = EntityManager::create($dbParams, $config);

return ConsoleRunner::createHelperSet($entityManager);
