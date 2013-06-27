<?php

namespace Forthicime\DossierBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AccessHistory
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Forthicime\DossierBundle\Entity\AccessHistoryRepository")
 */
class AccessHistory
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
     * @ORM\Column(name="access", type="datetime")
     */
    private $access;


    /**
    * @ORM\ManyToOne(targetEntity="\Forthicime\DossierBundle\Entity\Dossier", inversedBy="accessHistory")
    * @ORM\JoinColumn(name="dossier_id", referencedColumnName="id")    
    */
    private $dossier;


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
     * Set access
     *
     * @param \DateTime $access
     * @return AccessHistory
     */
    public function setAccess($access)
    {
        $this->access = $access;
    
        return $this;
    }

    /**
     * Get access
     *
     * @return \DateTime 
     */
    public function getAccess()
    {
        return $this->access;
    }

   
      /**
     * Set dossier
     *
     * @param Forthicime\DossierBundle\Entity\Dossier $dossier
     * @return AccessHistory
     */
    public function setDossier(\Forthicime\DossierBundle\Entity\Dossier $dossier = null)
    {
        $this->dossier = $dossier;
    
        return $this;
    }

    /**
     * Get dossier
     *
     * @return Forthicime\DossierBundle\Entity\Dossier
     */
    public function getDossier()
    {
        return $this->dossier;
    }

}
