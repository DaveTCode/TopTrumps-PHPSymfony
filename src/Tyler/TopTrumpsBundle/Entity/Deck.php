<?php

namespace Tyler\TopTrumpsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="deck")
 */
class Deck
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=500)
     */
    protected $description;

    /**
     * @ORM\Column(type="blob", nullable=true)
     */
    protected $image;

    /**
     * @ORM\OneToMany(targetEntity="Stat", mappedBy="deck")
     */
    protected $stats;

    /**
     * @ORM\OneToMany(targetEntity="Card", mappedBy="deck")
     */
    protected $cards;

    public function __construct()
    {
        $this->stats = new ArrayCollection();
        $this->cards = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Deck
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
     * Set description
     *
     * @param string $description
     * @return Deck
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return Deck
     */
    public function setImage($image)
    {
        $this->image = $image;
    
        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Add stats
     *
     * @param \Tyler\TopTrumpsBundle\Entity\Stat $stats
     * @return Deck
     */
    public function addStat(\Tyler\TopTrumpsBundle\Entity\Stat $stats)
    {
        $this->stats[] = $stats;
    
        return $this;
    }

    /**
     * Remove stats
     *
     * @param \Tyler\TopTrumpsBundle\Entity\Stat $stats
     */
    public function removeStat(\Tyler\TopTrumpsBundle\Entity\Stat $stats)
    {
        $this->stats->removeElement($stats);
    }

    /**
     * Get stats
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * Add cards
     *
     * @param \Tyler\TopTrumpsBundle\Entity\Card $cards
     * @return Deck
     */
    public function addCard(\Tyler\TopTrumpsBundle\Entity\Card $cards)
    {
        $this->cards[] = $cards;
    
        return $this;
    }

    /**
     * Remove cards
     *
     * @param \Tyler\TopTrumpsBundle\Entity\Card $cards
     */
    public function removeCard(\Tyler\TopTrumpsBundle\Entity\Card $cards)
    {
        $this->cards->removeElement($cards);
    }

    /**
     * Get cards
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCards()
    {
        return $this->cards;
    }
}
