<?php

namespace Forthicime\AdminBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * SynchronizationLine
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Forthicime\AdminBundle\Entity\SynchronizationLineRepository")
 */
class SynchronizationLine
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
     * @var string
     *
     * @ORM\Column(name="command", type="string")
     */
    private $command;

    /**
     * @var integer
     *
     * @ORM\Column(name="returnCode", type="integer")
     */
    private $returnCode;

    /**
     * @var string
     *
     * @ORM\Column(name="tableName", type="string", nullable=true)
     */
    private $tableName;

    /**
     * @var integer
     *
     * @ORM\Column(name="tableId", type="integer", nullable=true)
     */
    private $tableId;

    /**
     * @var text
     *
     * @ORM\Column(name="columnValues", type="text", nullable=true)
     */
    private $columnValues;

    /**
     * @var text
     *
     * @ORM\Column(name="oldColumnValues", type="text", nullable=true)
     */
    private $oldColumnValues;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    private $message;

    /**
    * @ORM\ManyToOne(targetEntity="\Forthicime\AdminBundle\Entity\Synchronization", inversedBy="synchronizationLines")
    * @ORM\JoinColumn(name="synchronization_id", referencedColumnName="id")    
    */
    private $synchronization;

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
     * Set command
     *
     * @param string $command
     * @return SynchronizationLine
     */
    public function setCommand($command)
    {
        $this->command = $command;
    
        return $this;
    }

    /**
     * Get command
     *
     * @return string 
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Set returnCode
     *
     * @param integer $returnCode
     * @return SynchronizationLine
     */
    public function setReturnCode($returnCode)
    {
        $this->returnCode = $returnCode;
    
        return $this;
    }

    /**
     * Get returnCode
     *
     * @return integer 
     */
    public function getReturnCode()
    {
        return $this->returnCode;
    }

    /**
     * Set tableName
     *
     * @param string $tableName
     * @return SynchronizationLine
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    
        return $this;
    }

    /**
     * Get tableName
     *
     * @return string 
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Set tableId
     *
     * @param integer $tableId
     * @return SynchronizationLine
     */
    public function setTableId($tableId)
    {
        $this->tableId = $tableId;
    
        return $this;
    }

    /** Get tableId
     *
     * @return integer 
     */
    public function getTableId()
    {
        return $this->tableId;
    }

    /**
     * Set columnValues
     *
     * @param string $values
     * @return SynchronizationLine
     */
    public function setColumnValues($values)
    {
        $this->columnValues = $values;
    
        return $this;
    }

    /**
     * Get columnValues
     *
     * @return string 
     */
    public function getColumnValues()
    {
        return $this->columnValues;
    }

    /**
     * Set oldColumnValues
     *
     * @param string $oldValues
     * @return SynchronizationLine
     */
    public function setOldColumnValues($oldValues)
    {
        $this->oldColumnValues = $oldValues;
    
        return $this;
    }

    /**
     * Get oldColumnValues
     *
     * @return string 
     */
    public function getOldColumnValues()
    {
        return $this->oldColumnValues;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return SynchronizationLine
     */
    public function setMessage($message)
    {
        $this->message = $message;
    
        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

  
    /**
     * Set created
     *
     * @param \datetime $created
     * @return SynchronizationLine
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
     * @return SynchronizationLine
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
     * Set synchronization
     *
     * @param Forthicime\AdminBundle\Entity\Synchronization $synchronization
     * @return SynchronizationLine
     */
    public function setSynchronization(\Forthicime\AdminBundle\Entity\Synchronization $synchronization = null)
    {
        $this->synchronization = $synchronization;
    
        return $this;
    }

    /**
     * Get synchronization
     *
     * @return Forthicime\AdminBundle\Entity\Synchronization 
     */
    public function getSynchronization()
    {
        return $this->synchronization;
    }

}
