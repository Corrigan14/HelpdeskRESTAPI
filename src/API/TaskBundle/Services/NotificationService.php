<?php

namespace API\TaskBundle\Services;


use API\TaskBundle\Repository\NotificationRepository;
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
     * @param int $page
     * @param array $options
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getLoggedUserNotifications(int $page, array $options): array
    {
        /** @var NotificationRepository $notificationRepository */
        $notificationRepository = $this->em->getRepository('APITaskBundle:Notification');
        $responseData = $notificationRepository->getLoggedUserNotifications($page, $options);

        $response['data'] = $responseData['array'];

        $url = $this->router->generate('users_notifications');
        $limit = $options['limit'];
        $filters = $options['filtersForUrl'];

        if (999 !== $limit) {
            $count = $responseData['count'];
        } else {
            $count = \count($responseData['array']);
        }

        $pagination = PaginationHelper::getPagination($url, $limit, $page, $count, $filters);

        $notificationCountNotRead = $notificationRepository->countLoggedUserNotifications($options['loggedUserId'], false);
        $notificationCountRead = $notificationRepository->countLoggedUserNotifications($options['loggedUserId'], true);
        $notificationCount['_counts'] = [
            'not read' => $notificationCountNotRead,
            'read' => $notificationCountRead,
            'all' => $notificationCountNotRead + $notificationCountRead
        ];

        return array_merge($response, $notificationCount,$pagination);
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