<?php

namespace SimpleDaemon\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use SimpleDaemon\Repository\TasksRepository;

/**
 *
 * @ORM\Table(name="tasks")
 * @ORM\Entity(repositoryClass="SimpleDaemon\Repository\TasksRepository")
 */
class Tasks
{
    /*Статусы задач*/
    const STATUS_PREPARE = -1;      // Статус подготовки
    const STATUS_NEW = 1;           // Статус новый
    const STATUS_IN_PROGRESS = 2;   // Статус в процессе
    const STATUS_ACTIVE = 3;        // Статус активный
    const STATUS_COMPLETED = 4;     // Статус обработанный
    const STATUS_FAILED = 5;        // Статус отклонен
    const STATUS_MODERATION = 6;    // Статус на модерации

    /* Типы задач */
    const TYPE_FIBONACCI = 1;       // Уровни Фибоначчи
    const TYPE_LEAST_SQUARES = 2;    // Линейная регрессия наименьших квадратов

    const CANCEL_BACK = 1;               // Вернуть на шаг назад
    const CANCEL_FIRST = 2;              // Вернуть в начало

    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Тип.
     *
     * @ORM\Column(name="type", type="integer", nullable=false, unique=false)
     */
    private $type;

    /**
     * Статус.
     *
     * @ORM\Column(name="status", type="integer", nullable=false, unique=false)
     */
    private $status;

    /**
     * Дата обновления.
     * @var int
     *
     * @ORM\Column(name="date_updated", type="datetime", unique=false, nullable=true)
     */
    private $dateUpdated;

    /**
     * Дата создания.
     * @var int
     *
     * @ORM\Column(name="date_created", type="datetime", unique=false, nullable=true)
     */
    private $dateCreated;

    /**
     * @var string
     *
     * @ORM\Column(name="params", type="text", nullable=true)
     */
    private $params;

    /**
     * @var string
     *
     * @ORM\Column(name="error", type="text", nullable=true)
     */
    private $errorData;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="string", nullable=true)
     */
    private $text;


    public function __construct()
    {
        $date = new \DateTime('now');
        $this->dateCreated = $date;
        $this->dateUpdated = $date;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return number
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @return self
     */
    public function setDateCreated()
    {
        $this->dateCreated = new \DateTime("now");
        return $this;
    }

    /**
     * @return number
     */
    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }

    /**
     * @return self
     */
    public function setDateUpdated()
    {
        $this->dateUpdated = new \DateTime("now");
        return $this;
    }

    /**
     * @return string
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return string|null
     */
    public function getErrorData()
    {
        return $this->errorData;
    }

    /**
     * @param array|null $errorData
     * @return self
     */
    public function setErrorData($errorData)
    {
        $this->errorData = $errorData;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getText()
    {
        return $this->errorData;
    }

    /**
     * @param $text
     * @return self
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    public static function getStatusText($status)
    {
        switch ($status) {
            case self::STATUS_PREPARE:
                $text = 'Preparing';
                break;
            case self::STATUS_NEW:
                $text = 'New';
                break;
            case self::STATUS_IN_PROGRESS:
                $text = 'In progress';
                break;
            case self::STATUS_COMPLETED:
                $text = 'Completed';
                break;
            case self::STATUS_FAILED:
                $text = 'Failed';
                break;
        }

        return $text;
    }
}