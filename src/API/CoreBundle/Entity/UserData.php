<?php

namespace API\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\ReadOnly;
use JMS\Serializer\Annotation\Exclude;
use Traits\UserDataTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UserData
 *
 * @ORM\Table(name="user_data")
 * @ORM\Entity(repositoryClass="API\CoreBundle\Repository\UserDataRepository")
 * @Serializer\ExclusionPolicy("none")
 */
class UserData
{
    use UserDataTrait;
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @Assert\Type("string")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="surname", type="string", length=255, nullable=true)
     * @Assert\Type("string")
     */
    private $surname;

    /**
     * @var string
     *
     * @ORM\Column(name="title_before", type="string", length=45, nullable=true)
     * @Assert\Type("string")
     */
    private $title_before;

    /**
     * @var string
     *
     * @ORM\Column(name="title_after", type="string", length=45, nullable=true)
     * @Assert\Type("string")
     */
    private $title_after;

    /**
     * @var string
     *
     * @ORM\Column(name="function", type="string", length=255, nullable=true)
     * @Assert\Type("string")
     */
    private $function;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", length=60, nullable=true)
     * @Assert\Type("string")
     */
    private $mobile;

    /**
     * @var string
     *
     * @ORM\Column(name="tel", type="string", length=60, nullable=true)
     * @Assert\Type("string")
     */
    private $tel;

    /**
     * @var string
     *
     * @ORM\Column(name="fax", type="string", length=60, nullable=true)
     * @Assert\Type("string")
     */
    private $fax;

    /**
     * @var string
     *
     * @ORM\Column(name="signature", type="text", nullable=true)
     * @Assert\Type("text")
     */
    private $signature;

    /**
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=255, nullable=true)
     * @Assert\Type("string")
     */
    private $street;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     * @Assert\Type("string")
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="zip", type="string", length=255, nullable=true)
     * @Assert\Type("string")
     */
    private $zip;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     * @Assert\Type("string")
     */
    private $country;

    /**
     * @ORM\Column(name="facebook", type="string", length=255, nullable=true)
     * @Assert\Type("string")
     *
     * @var string
     */
    private $facebook;

    /**
     * @ORM\Column(name="twitter", type="string", length=255, nullable=true)
     * @Assert\Type("string")
     *
     * @var string
     */
    private $twitter;


    /**
     * @ORM\Column(name="linkdin", type="string", length=255, nullable=true)
     * @Assert\Type("string")
     *
     * @var string
     */
    private $linkdin;


    /**
     * @ORM\Column(name="google", type="string", length=255, nullable=true)
     * @Assert\Type("string")
     *
     * @var string
     */
    private $google;

    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="detailData", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="SET NULL")
     * @Exclude()
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
        $this->title_before = $titleBefore;

        return $this;
    }

    /**
     * Get titleBefore
     *
     * @return string
     */
    public function getTitleBefore()
    {
        return $this->title_before;
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
        $this->title_after = $titleAfter;

        return $this;
    }

    /**
     * Get titleAfter
     *
     * @return string
     */
    public function getTitleAfter()
    {
        return $this->title_after;
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
     * @param User $user
     *
     * @return UserData
     */
    public function setUser(User $user = null)
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
     * Set street
     *
     * @param string $street
     *
     * @return UserData
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return UserData
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set zip
     *
     * @param string $zip
     *
     * @return UserData
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return UserData
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set facebook
     *
     * @param string $facebook
     *
     * @return UserData
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;

        return $this;
    }

    /**
     * Get facebook
     *
     * @return string
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * Set twitter
     *
     * @param string $twitter
     *
     * @return UserData
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;

        return $this;
    }

    /**
     * Get twitter
     *
     * @return string
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * Set linkdin
     *
     * @param string $linkdin
     *
     * @return UserData
     */
    public function setLinkdin($linkdin)
    {
        $this->linkdin = $linkdin;

        return $this;
    }

    /**
     * Get linkdin
     *
     * @return string
     */
    public function getLinkdin()
    {
        return $this->linkdin;
    }

    /**
     * Set google
     *
     * @param string $google
     *
     * @return UserData
     */
    public function setGoogle($google)
    {
        $this->google = $google;

        return $this;
    }

    /**
     * Get google
     *
     * @return string
     */
    public function getGoogle()
    {
        return $this->google;
    }
}
