<?php

namespace Forthicime\AdminBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Synchronization
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Forthicime\AdminBundle\Entity\SynchronizationRepository")
 */
class Synchronization
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start", type="datetime", nullable=true)
     */
    private $start;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end", type="datetime", nullable=true)
     */
    private $end;

    /**
     * @var integer
     *
     * @ORM\Column(name="nbTransaction", type="integer", nullable=true)
     */
    private $nbTransaction;

    /**
     * @var integer
     *
     * @ORM\Column(name="nbSuccess", type="integer", nullable=true)
     */
    private $nbSuccess;

    /**
     * @var integer
     *
     * @ORM\Column(name="nbFailure", type="integer", nullable=true)
     */
    private $nbFailure;

    /**
    * @ORM\OneToMany(targetEntity="Forthicime\AdminBundle\Entity\SynchronizationLine", mappedBy="synchronization")
    */
    private $synchronizationLines;


    /**
     * @var timestamp
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var timestamp
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime")
     */
    private $updated;

    public function __construct()
    {
        $this->synchronizationLines = new ArrayCollection();
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
     * Set start
     *
     * @param \DateTime $start
     * @return Synchronization
     */
    public function setStart($start)
    {
        $this->start = $start;
    
        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime 
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end
     *
     * @param \DateTime $end
     * @return Synchronization
     */
    public function setEnd($end)
    {
        $this->end = $end;
    
        return $this;
    }

    /**
     * Get end
     *
     * @return \DateTime 
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set nbTransaction
     *
     * @param integer $nbTransaction
     * @return Synchronization
     */
    public function setNbTransaction($nbTransaction)
    {
        $this->nbTransaction = $nbTransaction;
    
        return $this;
    }

    /**
     * Get nbTransaction
     *
     * @return integer 
     */
    public function getNbTransaction()
    {
        return $this->nbTransaction;
    }

    /**
     * Set nbSuccess
     *
     * @param integer $nbSuccess
     * @return Synchronization
     */
    public function setNbSuccess($nbSuccess)
    {
        $this->nbSuccess = $nbSuccess;
    
        return $this;
    }

    /**
     * Get nbSuccess
     *
     * @return integer 
     */
    public function getNbSuccess()
    {
        return $this->nbSuccess;
    }

    /**
     * Set nbFailure
     *
     * @param integer $nbFailure
     * @return Synchronization
     */
    public function setNbFailure($nbFailure)
    {
        $this->nbFailure = $nbFailure;
    
        return $this;
    }

    /**
     * Get nbFailure
     *
     * @return integer 
     */
    public function getNbFailure()
    {
        return $this->nbFailure;
    }

    /**
     * Set created
     *
     * @param \datetime $created
     * @return Synchronization
     */
    public function setCreated(\datetime $created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return \datetime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \datetime $updated
     * @return Synchornization
     */
    public function setUpdated(\datetime $updated)
    {
        $this->updated = $updated;
    
        return $this;
    }

    /**
     * Get updated
     *
     * @return \datetime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

      /**
     * Add addSynchronizationLine
     *
     * @param Forthicime\AdminBundle\Entity\SynchronizationLine $transaction
     * @return Synchornization
     */
    public function addSynchronizationLine(\Forthicime\AdminBundle\Entity\SynchronizationLine $transaction)
    {
        $this->synchronizationLines[] = $transaction;
    
        return $this;
    }

    /**
     * Remove synchronizationLine
     *
     * @param \Forthicime\AdminBundle\Entity\SynchronizationLine $transaction
     */
    public function removeSynchronizationLine(\Forthicime\AdminBundle\Entity\SynchronizationLine $transaction)
    {
        $this->synchronizationLines->removeElement($transaction);
    }

    /**
     * Get getSynchronizationLines
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSynchronizationLines()
    {
        return $this->synchronizationLines;
    }

}
