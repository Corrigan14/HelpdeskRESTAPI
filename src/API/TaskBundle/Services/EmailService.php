<?php

namespace API\TaskBundle\Services;

use PHPUnit\Framework\Exception;

/**
 * Class EmailService
 *
 * @package API\TaskBundle\Services
 */
class EmailService
{
    protected $twig;
    protected $mailer;

    /**
     * EmailService constructor.
     * @param \Twig_Environment $twig
     * @param \Swift_Mailer $mailer
     */
    public function __construct(\Twig_Environment $twig, \Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->errors = [];
    }

    /**
     * @param array $params
     * @return string|bool
     */
    public function sendEmail(array $params)
    {
        $subject = $params['subject'];
        $from = $params['from'];
        $to = $params['to'];
        $body = $params['body'];

        try {
            $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($from)
                ->setTo($to)
                ->setBody($body, 'text/html');

            $this->mailer->send($message);
            return true;
        } catch (\Swift_TransportException $e) {
            return $e->getMessage();
        }
    }
}