<?php

namespace Traits;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Tag;
use API\TaskBundle\Entity\UserHasProject;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Exclude;

/**
 * Class UserTrait
 *
 * @package Traits
 */
trait UserTrait
{
    /**
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Tag", mappedBy="createdBy")
     * @Exclude()
     *
     * @var ArrayCollection
     */
    private $tags;

    /**
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Project", mappedBy="createdBy")
     * @Exclude()
     *
     * @var ArrayCollection
     */
    private $projects;

    /**
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\UserHasProject", mappedBy="project")
     * @Exclude()
     *
     * @var ArrayCollection
     */
    private $userHasProjects;

    /**
     * Add tag
     *
     * @param Tag $tag
     * @return User
     */
    public function addTag(Tag $tag)
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * Remove tag
     *
     * @param Tag $tag
     */
    public function removeTag(Tag $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Get tags
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Add project
     *
     * @param Project $project
     * @return User
     */
    public function addProject(Project $project)
    {
        $this->projects[] = $project;

        return $this;
    }

    /**
     * Remove project
     *
     * @param Project $project
     */
    public function removeProject(Project $project)
    {
        $this->projects->removeElement($project);
    }

    /**
     * Get projects
     *
     * @return ArrayCollection
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Add userHasProject
     *
     * @param UserHasProject $userHasProject
     *
     * @return User
     */
    public function addUserHasProject(UserHasProject $userHasProject)
    {
        $this->userHasProjects[] = $userHasProject;

        return $this;
    }

    /**
     * Remove userHasProject
     *
     * @param UserHasProject $userHasProject
     */
    public function removeUserHasProject(UserHasProject $userHasProject)
    {
        $this->userHasProjects->removeElement($userHasProject);
    }

    /**
     * Get userHasProjects
     *
     * @return ArrayCollection
     */
    public function getUserHasProjects()
    {
        return $this->userHasProjects;
    }

}