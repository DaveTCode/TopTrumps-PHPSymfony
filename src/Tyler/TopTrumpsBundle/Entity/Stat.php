<?php

namespace Tyler\TopTrumpsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude; // Required even though phpstorm doesn't think so.

/**
 * @ORM\Entity
 * @ORM\Table(name="stat")
 * @ExclusionPolicy("none")
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
     * @ORM\JoinColumn(name="deck_id", referencedColumnName="id", onDelete="CASCADE")
     * @Exclude
     */
    protected $deck;

    /**
     * @ORM\OneToMany(targetEntity="StatValue", mappedBy="stat")
     * @Exclude
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

        foreach ($this->getStatValues() as $statValue) {
            if ($statValue->getValue() < $min) {
                $statValue->setValue($min);
            }
        }
    
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
     * Set max and propagate values to all stat values using this stat forcing 
     * their value to be between min and max.
     *
     * @param integer $max
     * @return Stat
     */
    public function setMax($max)
    {
        $this->max = $max;

        foreach ($this->getStatValues() as $statValue) {
            if ($statValue->getValue() > $max) {
                $statValue->setValue($max);
            }
        }
    
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