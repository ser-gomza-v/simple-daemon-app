<?php

use SimpleDaemon\Controller\TasksController;
use SimpleDaemon\Service\TasksService;
use SimpleDaemon\Service\TelegramService;
use SimpleDaemon\Repository\TasksRepository;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

$containerBuilder = new ContainerBuilder();

$containerBuilder->set('entityManager', require_once 'doctrine.php');

// services
$containerBuilder->register('service.telegram', TelegramService::class);
$containerBuilder->register('service.tasks', TasksService::class)->addArgument($containerBuilder->get('entityManager'));

// repositories
$containerBuilder->register('repository.tasks', TasksRepository::class);

// controllers
$containerBuilder->register('controller.tasks', TasksController::class)
    ->addArgument(new Reference('service.telegram'))
    ->addArgument(new Reference('service.tasks'))
;

return $containerBuilder;