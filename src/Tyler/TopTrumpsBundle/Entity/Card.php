<?php

namespace Tyler\TopTrumpsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude; // Required even though phpstorm doesn't think so.

/**
 * @ORM\Entity
 * @ORM\Table(name="card")
 * @ExclusionPolicy("none")
 */
class Card
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
     * @ORM\Column(type="string", length=250)
     */
    protected $description;

    /**
     * @ORM\Column(type="blob", nullable=true)
     * @Exclude
     */
    protected $image;

    /**
     * @ORM\ManyToOne(targetEntity="Deck", inversedBy="cards")
     * @ORM\JoinColumn(name="deck_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $deck;

    /**
     * @ORM\OneToMany(targetEntity="StatValue", mappedBy="card")
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
     * @return Card
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
     * @return Card
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
     * @return Card
     */
    public function setImage($image)
    {
        $this->image = $image;
    
        return $this;
    }

    /**
     * Set the image from a URI format string (i.e. the sort that comes back
     * from a FileReader.readDataURL in HTML5).
     *
     * @param string $image
     * @return Card
     */
    public function setImageFromURI($image)
    {
        $image = preg_replace('/data:image.*?;base64,/', '', $image, 1);
        $this->image = base64_decode($image);

        return $this;
    }

    /**
     * Get image
     *
     * @return Resource
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set deck
     *
     * @param \Tyler\TopTrumpsBundle\Entity\Deck $deck
     * @return Card
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
     * @return Card
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
