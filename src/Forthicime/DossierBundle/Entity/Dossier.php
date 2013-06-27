<?php

namespace Forthicime\DossierBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Dossier
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Forthicime\DossierBundle\Entity\DossierRepository")
 */
class Dossier
{

    /**
    * @ORM\ManyToOne(targetEntity="\Forthicime\MedecinBundle\Entity\Medecin", inversedBy="dossiers")
    * @ORM\JoinColumn(name="medecin_id", referencedColumnName="id")    
    */
    private $medecin;

    /**
    * @ORM\ManyToOne(targetEntity="\Forthicime\ClientBundle\Entity\Client", inversedBy="dossiers")
    * @ORM\JoinColumn(name="client_id", referencedColumnName="id")    
    */
    private $client;

    /**
    * @ORM\OneToMany(targetEntity="Forthicime\DossierBundle\Entity\AccessHistory", mappedBy="dossiers")
    */
    private $accessHistories;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="numeric", type="integer")
     */
    private $numeric;


    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    private $libelle;

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
     * Set Id
     *
     * @param string $id
     * @return Client
     */
    public function setId($id)
    {
        $this->id = $id;
    
        return $this;
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
     * Set numeric
     *
     * @param integer $numeric
     * @return Dossier
     */
    public function setNumeric($numeric)
    {
        $this->numeric = $numeric;
    
        return $this;
    }

    /**
     * Get numeric
     *
     * @return integer 
     */
    public function getNumeric()
    {
        return $this->numeric;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return Dossier
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
    
        return $this;
    }

    /**
     * Get libelle
     *
     * @return string 
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set created
     *
     * @param \datetime $created
     * @return Dossier
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
     * @return Dossier
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
     * Set medecin
     *
     * @param Forthicime\MedecinBundle\Entity\Medecin $medecin
     * @return Dossier
     */
    public function setMedecin(\Forthicime\MedecinBundle\Entity\Medecin $medecin = null)
    {
        $this->medecin = $medecin;
    
        return $this;
    }

    /**
     * Get medecin
     *
     * @return Forthicime\MedecinBundle\Entity\Medecin 
     */
    public function getMedecin()
    {
        return $this->medecin;
    }

    /**
     * Set client
     *
     * @param Forthicime\ClientBundle\Entity\Client $client
     * @return Dossier
     */
    public function setClient(\Forthicime\ClientBundle\Entity\Client $client = null)
    {
        $this->client = $client;
    
        return $this;
    }

    /**
     * Get client
     *
     * @return Forthicime\ClientBundle\Entity\Client
     */
    public function getClient()
    {
        return $this->client;
    }

       /**
     * Add accessHistory
     *
     * @param Forthicime\DossierBundle\Entity\AccessHistory $accessHistory
     * @return Dossier
     */
    public function addAccessHistory(\Forthicime\DossierBundle\Entity\AccessHistory $accessHistory)
    {
        $this->accessHistories[] = $accessHistory;
    
        return $this;
    }

    /**
     * Remove dossierHistory
     *
     * @param \Forthicime\DossierBundle\Entity\ForthicimeDossierBundle:DossierHistory $dossierHistory
     */
    public function removeAccessHistory(\Forthicime\DossierBundle\Entity\AccessHistory $accessHistory)
    {
        $this->accessHistories->removeElement($accessHistory);
    }

    /**
     * Get accessHistories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAccessHistories()
    {
        return $this->accessHistories;
    }
}