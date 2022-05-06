<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

/**
 * Class Address
 * @selectPure App\Entity
 * @author  Craig Rayner <craig@craigrayner.com>
 * 3/04/2021 16:30
 * @ORM\Entity(repositoryClass="App\Repository\AddressRepository")
 * @ORM\Table(name="address")
 */
class Address
{
    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)    
     */
    private ?string $id;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $line1;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $line2;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $line3;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $state;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $facsimile;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $url;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getLine1()
    {
        return $this->line1;
    }

    /**
     * @param mixed $line1
     * @return Address
     */
    public function setLine1($line1)
    {
        $this->line1 = $line1;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLine2()
    {
        return $this->line2;
    }

    /**
     * @param mixed $line2
     * @return Address
     */
    public function setLine2($line2)
    {
        $this->line2 = $line2;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLine3()
    {
        return $this->line3;
    }

    /**
     * @param mixed $line3
     * @return Address
     */
    public function setLine3($line3)
    {
        $this->line3 = $line3;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     * @return Address
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     * @return Address
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param mixed $postalCode
     * @return Address
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     * @return Address
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param mixed $phoneNumber
     * @return Address
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return Address
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFacsimile()
    {
        return $this->facsimile;
    }

    /**
     * @param mixed $facsimile
     * @return Address
     */
    public function setFacsimile($facsimile)
    {
        $this->facsimile = $facsimile;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     * @return Address
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }
}
