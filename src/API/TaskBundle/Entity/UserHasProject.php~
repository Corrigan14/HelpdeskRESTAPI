<?php

namespace API\TaskBundle\Entity;

use API\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\ReadOnly;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UserHasProject
 *
 * @ORM\Table(name="user_has_project")
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\UserHasProjectRepository")
 */
class UserHasProject
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ReadOnly()
     */
    private $id;

    /**
     * @var array
     *
     * @ORM\Column(name="acl", type="text", nullable=true)
     */
    private $acl;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="API\CoreBundle\Entity\User", inversedBy="userHasProjects")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @ReadOnly()
     */
    private $user;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="API\TaskBundle\Entity\Project", inversedBy="userHasProjects")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=false)
     * @ReadOnly()
     */
    private $project;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set acl
     *
     * @param array $acl
     */
    public function setAcl(array $acl)
    {
        $this->acl = json_encode($acl);
    }

    /**
     * Get acl
     *
     * @return array
     */
    public function getAcl():array
    {
        return json_decode($this->acl);
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return UserHasProject
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set project
     *
     * @param Project $project
     *
     * @return UserHasProject
     */
    public function setProject(Project $project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }
}
