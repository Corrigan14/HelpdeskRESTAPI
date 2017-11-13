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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getLoggedUserNotifications($options): array
    {
        $loggedUserId = $options['loggedUserId'];
        $read = $options['read'];

        $allNotifications = $this->em->getRepository('APITaskBundle:Notification')->getLoggedUserNotifications($loggedUserId, $read);

        return [
            'data' => $allNotifications,
            'not read' => $this->em->getRepository('APITaskBundle:Notification')->countLoggedUserNotifications($loggedUserId,false),
            'read' => $this->em->getRepository('APITaskBundle:Notification')->countLoggedUserNotifications($loggedUserId,true)
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