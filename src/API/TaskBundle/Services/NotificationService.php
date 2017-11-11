<?php

namespace API\TaskBundle\Services;


use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class NotificationService
 * @package API\TaskBundle\Services
 */
class NotificationService
{
    /**
     * @var EntityManager
     */
    private $em;

    /** @var Router */
    private $router;


    /**
     * NotificationService constructor.
     *
     * @param EntityManager $em
     * @param Router $router
     */
    public function __construct(EntityManager $em, Router $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

    /**
     * @param $options
     * @return array
     */
    public function getLoggedUserNotifications($options): array
    {
        $loggedUser = $options['loggedUser'];
        $read = $options['read'];

        $notifications = [];

        return [
            'data' => $notifications,
            'not read' => 10,
            'total' => 15
        ];
    }


    /**
     * @param int $notificationId
     * @return array
     */
    private function getNotificationLinks(int $notificationId): array
    {
        return [
            'get logged users notifications' => $this->router->generate('users_notifications'),
            'set as read' => $this->router->generate('notification_set_as_read', ['notificationId' => $notificationId]),
            'delete' => $this->router->generate('notification_delete', ['notificationId' => $notificationId]),
        ];
    }
}