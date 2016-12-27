<?php

namespace API\TaskBundle\DataFixtures\ORM;


use API\TaskBundle\Entity\Comment;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CommentFixture
 *
 * @package API\TaskBundle\DataFixtures\ORM
 */
class CommentFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        // TODO: Implement setContainer() method.
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $adminsTask = $manager->getRepository('APITaskBundle:Task')->findOneBy([
            'title' => 'Task 3 - admin is creator, admin is requested'
        ]);

        $comment1 = new Comment();
        $comment1->setTitle('Koment - public');
        $comment1->setBody('Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ');
        $comment1->setEmail(false);
        $comment1->setInternal(false);
        $comment1->setTask($adminsTask);
        $manager->persist($comment1);
        $manager->flush();

        $comment = new Comment();
        $comment->setTitle('Koment - publik, podkomentar komentu');
        $comment->setBody('Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ');
        $comment->setEmail(false);
        $comment->setInternal(true);
        $comment->setTask($adminsTask);
        $comment->setComment($comment1);
        $manager->persist($comment);

        $comment = new Comment();
        $comment->setTitle('Koment - private');
        $comment->setBody('Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ');
        $comment->setEmail(false);
        $comment->setInternal(true);
        $comment->setTask($adminsTask);
        $manager->persist($comment);

        $comment = new Comment();
        $comment->setTitle('Email - public');
        $comment->setBody('Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ');
        $comment->setInternal(false);
        $comment->setEmail(true);
        $comment->setEmailTo(['email@email.com']);
        $comment->setEmailCc(['email2@email.sk', 'email3@email.com']);
        $comment->setTask($adminsTask);
        $manager->persist($comment);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 12;
    }
}