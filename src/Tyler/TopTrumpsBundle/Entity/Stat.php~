<?php

namespace Tyler\TopTrumpsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="stat")
 */
class Stat
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
     * @ORM\Column(type="integer")
     */
    protected $min;

    /**
     * @ORM\Column(type="integer")
     */
    protected $max;

    /**
     * @ORM\ManyToOne(targetEntity="Deck", inversedBy="stats")
     * @ORM\JoinColumn(name="deck_id", referencedColumnName="id")
     */
    protected $deck;

    /**
     * @ORM\OneToMany(targetEntity="StatValue", mappedBy="stat")
     */
    protected $statValues;

    public function __construct()
    {
        $this->statValues = new ArrayCollection();
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
     * @return Stat
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
     * Set min
     *
     * @param integer $min
     * @return Stat
     */
    public function setMin($min)
    {
        $this->min = $min;
    
        return $this;
    }

    /**
     * Get min
     *
     * @return integer 
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * Set max
     *
     * @param integer $max
     * @return Stat
     */
    public function setMax($max)
    {
        $this->max = $max;
    
        return $this;
    }

    /**
     * Get max
     *
     * @return integer 
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * Set deck
     *
     * @param \Tyler\TopTrumpsBundle\Entity\Deck $deck
     * @return Stat
     */
    public function setDeck(\Tyler\TopTrumpsBundle\Entity\Deck $deck = null)
    {
        $this->deck = $deck;
    
        return $this;
    }

    /**
     * Get deck
     *
     * @return \Tyler\TopTrumpsBundle\Entity\Deck 
     */
    public function getDeck()
    {
        return $this->deck;
    }

    /**
     * Add statValues
     *
     * @param \Tyler\TopTrumpsBundle\Entity\StatValue $statValues
     * @return Stat
     */
    public function addStatValue(\Tyler\TopTrumpsBundle\Entity\StatValue $statValues)
    {
        $this->statValues[] = $statValues;
    
        return $this;
    }

    /**
     * Remove statValues
     *
     * @param \Tyler\TopTrumpsBundle\Entity\StatValue $statValues
     */
    public function removeStatValue(\Tyler\TopTrumpsBundle\Entity\StatValue $statValues)
    {
        $this->statValues->removeElement($statValues);
    }

    /**
     * Get statValues
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getStatValues()
    {
        return $this->statValues;
    }
}