<?php

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$paths = [__DIR__."/../src/Entity"];
$isDevMode = true;

$dbParams = require_once 'db.php';

$reader = new AnnotationReader();
$driver = new AnnotationDriver($reader, $paths);

$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, null, null, false);
$entityManager = EntityManager::create($dbParams, $config);

return $entityManager;