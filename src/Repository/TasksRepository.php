<?php

namespace SimpleDaemon\Repository;

use SimpleDaemon\Entity\Tasks;
use DateTime;
use Doctrine\ORM\EntityRepository;

class TasksRepository extends EntityRepository
{
    /**
     * Создание новой таски.
     * @param integer $type тип задачи
     * @param string $params доп. параметры
     * @param integer $status статус
     * @return Tasks
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createNewTask($type, $params = '', $status = null)
    {
        $status = $status ? $status : Tasks::STATUS_NEW;
        $task = new Tasks();
        $task->setType($type);
        $task->setStatus($status);
        $task->setParams($params);
        $this->getEntityManager()->persist($task);
        $this->getEntityManager()->flush();

        return $task;
    }

    /**
     * Обновление статуса задачи.
     * @param integer $taskId ИД задачи
     * @param integer $taskStatus статус задачи
     * @param array $error
     * @param array $errorData
     * @return mixed
     */
    public function updateTasksById($taskId, $taskStatus, $error = [], $errorData = [])
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->update(Tasks::class, 't')
            ->set('t.status', ':status')
            ->set('t.dateUpdated', ':dateUpdated')
            ->where('t.id = :id')
            ->setParameter('id', $taskId)
            ->setParameter('status', $taskStatus)
            ->setParameter('dateUpdated', new DateTime("now"));

        if ($error) {
            $qb->set('t.error', ':error')
                ->setParameter('error', json_encode($error));
        }

        if ($errorData) {
            $qb->set('t.errorData', ':errorData')
                ->setParameter('errorData', json_encode($errorData));
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Получение числа активных задач по типу и статусу.
     * @param $status
     * @param $type
     * @return array
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCount($status, $type)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb = $qb->select($qb->expr()->count('t.id'))
            ->from(Tasks::class, 't')
            ->andWhere('t.status = :status')
            ->setParameter('status', $status)
        ;

        if ($type) {
            $qb->andWhere('t.type = :type')
                ->setParameter('type', $type);
        }

        return $qb->getQuery()->getSingleScalarResult();


    }

    /**
     * Обновление ошибки для задачи.
     * @param integer $taskId ИД задачи
     * @param string $error ошибка
     * @return mixed
     */
    public function setTaskErrorById($taskId, $error)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->update(Tasks::class, 't')
            ->set('t.status', ':status')
            ->set('t.error', ':error')
            ->set('t.dateUpdated', ':dateUpdated')
            ->where('t.id = :id')
            ->setParameter('id', $taskId)
            ->setParameter('status', Tasks::STATUS_FAILED)
            ->setParameter('error', $error)
            ->setParameter('dateUpdated', new DateTime("now"))
            ->getQuery()
            ->execute();
    }

    /**
     * Получение новых задач по типу.
     * @param integer $taskType тип задачи
     * @param null $limit
     * @return array
     */
    public function getNewTasksByType($taskType = null, $limit = null): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb = $qb->select('t')
            ->from(Tasks::class, 't')
            ->andWhere('t.status = :status')
            ->setParameter('status', Tasks::STATUS_NEW)
        ;

        if ($taskType) {
            $qb->andWhere('t.type = :type')
                ->setParameter('type', $taskType);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

}
