<?php

namespace API\TaskBundle\Services;

use API\TaskBundle\Entity\Smtp;
use Doctrine\ORM\EntityManager;

/**
 * Class EmailService
 *
 * @package API\TaskBundle\Services
 */
class EmailService
{
    protected $twig;
    protected $mailer;
    protected $em;

    /**
     * EmailService constructor.
     * @param \Twig_Environment $twig
     * @param \Swift_Mailer $mailer
     */
    public function __construct(\Twig_Environment $twig, \Swift_Mailer $mailer, EntityManager $em)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->em = $em;
    }

    /**
     * @param array $params
     * @return array
     */
    public function sendEmail(array $params): array
    {
        // Load SMTP settings
        $smtpSettings = $this->em->getRepository('APITaskBundle:Smtp')->findOneBy([]);

        if ($smtpSettings instanceof Smtp) {
            if ($smtpSettings->getSsl()) {
                $security = 'ssl';
            } elseif ($smtpSettings->getTls()) {
                $security = 'tls';
            } else {
                $security = null;
            }

            // override transport options so that parameters.yml is by-passed
            $transport = \Swift_SmtpTransport::newInstance($smtpSettings->getHost(), $smtpSettings->getPort(), $security)
                ->setUsername($smtpSettings->getName())
                ->setPassword($smtpSettings->getPassword());

            $this->mailer = \Swift_Mailer::newInstance($transport);
        } else {
            return [
                'error' => 'Problem with SMTP settings! Please contact admin!',
                'sentEmails' => []
            ];
        }

        $subject = $params['subject'];
        $from = $params['from'];
        $addressesTo = $params['to'];
        $body = $params['body'];
        $sentEmailsTo = [];

        try {
            foreach ($addressesTo as $to) {
                if (isset($params['attachment']) && false !== $params['attachment']) {
                    $message = \Swift_Message::newInstance()
                        ->setSubject($subject)
                        ->setFrom($from)
                        ->setTo($to)
                        ->setBody($body, 'text/html');
                    foreach ($params['attachment'] as $attachment) {
                        $attachmentDir = $attachment['dir'];
                        $attachmentFile = $attachment['name'];
                        $path = __DIR__ . '/../../../../app/uploads/' . $attachmentDir . '/' . $attachmentFile;
                        $message->attach(\Swift_Attachment::fromPath($path));
                    }
                } else {
                    $message = \Swift_Message::newInstance()
                        ->setSubject($subject)
                        ->setFrom($from)
                        ->setTo($to)
                        ->setBody($body, 'text/html');
                }
                $this->mailer->send($message);
                $sentEmailsTo[] = $to;
            }
            return [
                'error' => false,
                'sentEmails' => $sentEmailsTo
            ];
        } catch (\Swift_TransportException $e) {
            return [
                'error' => $e->getMessage(),
                'sentEmails' => []
            ];
        }
    }
}