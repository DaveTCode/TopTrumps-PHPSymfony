<?php

namespace Tyler\TopTrumpsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude; // Required even though phpstorm doesn't think so.

/**
 * @ORM\Entity
 * @ORM\Table(name="stat_value")
 * @ExclusionPolicy("none")
 */
class StatValue
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $value;

    /**
     * @ORM\ManyToOne(targetEntity="Card", inversedBy="statValues")
     * @ORM\JoinColumn(name="card_id", referencedColumnName="id", onDelete="CASCADE")
     * @Exclude
     */
    protected $card;

    /**
     * @ORM\ManyToOne(targetEntity="Stat", inversedBy="statValues")
     * @ORM\JoinColumn(name="stat_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $stat;

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
     * Set value
     *
     * @param integer $value
     * @return StatValue
     */
    public function setValue($value)
    {
        $this->value = $value;
    
        return $this;
    }

    /**
     * Get value
     *
     * @return integer 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set card
     *
     * @param \Tyler\TopTrumpsBundle\Entity\Card $card
     * @return StatValue
     */
    public function setCard(\Tyler\TopTrumpsBundle\Entity\Card $card = null)
    {
        $this->card = $card;
    
        return $this;
    }

    /**
     * Get card
     *
     * @return \Tyler\TopTrumpsBundle\Entity\Card 
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * Set stat
     *
     * @param \Tyler\TopTrumpsBundle\Entity\Stat $stat
     * @return StatValue
     */
    public function setStat(\Tyler\TopTrumpsBundle\Entity\Stat $stat = null)
    {
        $this->stat = $stat;
    
        return $this;
    }

    /**
     * Get stat
     *
     * @return \Tyler\TopTrumpsBundle\Entity\Stat 
     */
    public function getStat()
    {
        return $this->stat;
    }
}