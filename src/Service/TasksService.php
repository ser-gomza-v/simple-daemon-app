<?php
namespace SimpleDaemon\Service;

use Doctrine\ORM\EntityManager;
use SimpleDaemon\Entity\Tasks;
use SimpleDaemon\Repository\TasksRepository;

class TasksService
{
    /**
     * Репозиторий
     * @var EntityManager
     */
    private $entityManager;

    /**
     * TasksService constructor.
     * @param EntityManager $entityManager
     */
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param $type
     * @param string $params
     * @return Tasks
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function createNewTask($type, $params = '')
    {
        /** @var TasksRepository $taskRepository */
        $taskRepository = $this->entityManager->getRepository(Tasks::class);
        return $taskRepository->createNewTask($type, $params, Tasks::STATUS_NEW);
    }
}