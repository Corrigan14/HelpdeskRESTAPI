<?php

namespace API\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserData
 *
 * @ORM\Table(name="user_data")
 * @ORM\Entity(repositoryClass="API\CoreBundle\Repository\UserDataRepository")
 */
class UserData
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="surname", type="string", length=255, nullable=true)
     */
    private $surname;

    /**
     * @var string
     *
     * @ORM\Column(name="titleBefore", type="string", length=45, nullable=true)
     */
    private $titleBefore;

    /**
     * @var string
     *
     * @ORM\Column(name="titleAfter", type="string", length=45, nullable=true)
     */
    private $titleAfter;

    /**
     * @var string
     *
     * @ORM\Column(name="function", type="string", length=255, nullable=true)
     */
    private $function;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", length=60, nullable=true)
     */
    private $mobile;

    /**
     * @var string
     *
     * @ORM\Column(name="tel", type="string", length=60, nullable=true)
     */
    private $tel;

    /**
     * @var string
     *
     * @ORM\Column(name="fax", type="string", length=60, nullable=true)
     */
    private $fax;

    /**
     * @var string
     *
     * @ORM\Column(name="signature", type="text", nullable=true)
     */
    private $signature;

    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="detailData")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

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
     * Set name
     *
     * @param string $name
     *
     * @return UserData
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set surname
     *
     * @param string $surname
     *
     * @return UserData
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get surname
     *
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set titleBefore
     *
     * @param string $titleBefore
     *
     * @return UserData
     */
    public function setTitleBefore($titleBefore)
    {
        $this->titleBefore = $titleBefore;

        return $this;
    }

    /**
     * Get titleBefore
     *
     * @return string
     */
    public function getTitleBefore()
    {
        return $this->titleBefore;
    }

    /**
     * Set titleAfter
     *
     * @param string $titleAfter
     *
     * @return UserData
     */
    public function setTitleAfter($titleAfter)
    {
        $this->titleAfter = $titleAfter;

        return $this;
    }

    /**
     * Get titleAfter
     *
     * @return string
     */
    public function getTitleAfter()
    {
        return $this->titleAfter;
    }

    /**
     * Set function
     *
     * @param string $function
     *
     * @return UserData
     */
    public function setFunction($function)
    {
        $this->function = $function;

        return $this;
    }

    /**
     * Get function
     *
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     *
     * @return UserData
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile
     *
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set tel
     *
     * @param string $tel
     *
     * @return UserData
     */
    public function setTel($tel)
    {
        $this->tel = $tel;

        return $this;
    }

    /**
     * Get tel
     *
     * @return string
     */
    public function getTel()
    {
        return $this->tel;
    }

    /**
     * Set fax
     *
     * @param string $fax
     *
     * @return UserData
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Get fax
     *
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set signature
     *
     * @param string $signature
     *
     * @return UserData
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Get signature
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Set user
     *
     * @param \API\CoreBundle\Entity\User $user
     *
     * @return UserData
     */
    public function setUser(\API\CoreBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \API\CoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
