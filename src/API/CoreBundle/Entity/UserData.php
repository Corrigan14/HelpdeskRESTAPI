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
     * @Assert\Type("string")
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
    public function getId(): int
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
    public function setName($name): UserData
    {
        if ('null' === strtolower($name)) {
            $this->name = null;
        } else {
            $this->name = $name;
        }

        return $this;
    }

    /**
     * Get name
     *
     * @return string|null
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
    public function setSurname($surname): UserData
    {
        if ('null' === strtolower($surname)) {
            $this->surname = null;
        } else {
            $this->surname = $surname;
        }

        return $this;
    }

    /**
     * Get surname
     *
     * @return string|null
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
    public function setTitleBefore($titleBefore): UserData
    {
        if ('null' === strtolower($titleBefore)) {
            $this->title_before = null;
        } else {
            $this->title_before = $titleBefore;
        }

        return $this;
    }

    /**
     * Get titleBefore
     *
     * @return string|null
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
    public function setTitleAfter($titleAfter): UserData
    {
        if ('null' === strtolower($titleAfter)) {
            $this->title_after = null;
        } else {
            $this->title_after = $titleAfter;
        }

        return $this;
    }

    /**
     * Get titleAfter
     *
     * @return string|null
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
    public function setFunction($function): UserData
    {
        if ('null' === strtolower($function)) {
            $this->function = null;
        } else {
            $this->function = $function;
        }

        return $this;
    }

    /**
     * Get function
     *
     * @return string|null
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
    public function setMobile($mobile): UserData
    {
        if ('null' === strtolower($mobile)) {
            $this->mobile = null;
        } else {
            $this->mobile = $mobile;
        }

        return $this;
    }

    /**
     * Get mobile
     *
     * @return string|null
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
    public function setTel($tel): UserData
    {
        if ('null' === strtolower($tel)) {
            $this->tel = null;
        } else {
            $this->tel = $tel;
        }

        return $this;
    }

    /**
     * Get tel
     *
     * @return string|null
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
    public function setFax($fax): UserData
    {
        if ('null' === strtolower($fax)) {
            $this->fax = null;
        } else {
            $this->fax = $fax;
        }

        return $this;
    }

    /**
     * Get fax
     *
     * @return string|null
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
    public function setSignature($signature): UserData
    {
        if ('null' === strtolower($signature)) {
            $this->signature = null;
        } else {
            $this->signature = $signature;
        }

        return $this;
    }

    /**
     * Get signature
     *
     * @return string|null
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
    public function setUser(User $user = null): UserData
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser(): User
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
    public function setStreet($street): UserData
    {
        if ('null' === strtolower($street)) {
            $this->street = null;
        } else {
            $this->street = $street;
        }

        return $this;
    }

    /**
     * Get street
     *
     * @return string|null
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
    public function setCity($city): UserData
    {
        if ('null' === strtolower($city)) {
            $this->city = null;
        } else {
            $this->city = $city;
        }

        return $this;
    }

    /**
     * Get city
     *
     * @return string|null
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
    public function setZip($zip): UserData
    {
        if ('null' === strtolower($zip)) {
            $this->zip = null;
        } else {
            $this->zip = $zip;
        }

        return $this;
    }

    /**
     * Get zip
     *
     * @return string|null
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
    public function setCountry($country): UserData
    {
        if ('null' === strtolower($country)) {
            $this->country = null;
        } else {
            $this->country = $country;
        }

        return $this;
    }

    /**
     * Get country
     *
     * @return string|null
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
    public function setFacebook($facebook): UserData
    {
        if ('null' === strtolower($facebook)) {
            $this->facebook = null;
        } else {
            $this->facebook = $facebook;
        }

        return $this;
    }

    /**
     * Get facebook
     *
     * @return string|null
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
    public function setTwitter($twitter): UserData
    {
        if ('null' === strtolower($twitter)) {
            $this->twitter = null;
        } else {
            $this->twitter = $twitter;
        }

        return $this;
    }

    /**
     * Get twitter
     *
     * @return string|null
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
    public function setLinkdin($linkdin): UserData
    {
        if ('null' === strtolower($linkdin)) {
            $this->linkdin = null;
        } else {
            $this->linkdin = $linkdin;
        }

        return $this;
    }

    /**
     * Get linkdin
     *
     * @return string|null
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
    public function setGoogle($google): UserData
    {
        if ('null' === strtolower($google)) {
            $this->google = null;
        } else {
            $this->google = $google;
        }

        return $this;
    }

    /**
     * Get google
     *
     * @return string|null
     */
    public function getGoogle()
    {
        return $this->google;
    }
}
