<?php

namespace Forthicime\ClientBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Client
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Forthicime\ClientBundle\Entity\ClientRepository")
 */
class Client
{

    /**
    * @ORM\OneToMany(targetEntity="Forthicime\DossierBundle\Entity\Dossier", mappedBy="client")
    */
    private $dossiers;


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom", type="string", length=255)
     */
    private $prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="NomPrenom", type="string", length=255)
     */
    private $NomPrenom;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime")
     */
    private $updated;


    public function __construct()
    {
        $this->dossiers = new ArrayCollection();
    }


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
     * Set nom
     *
     * @param string $nom
     * @return Client
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    
        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     * @return Client
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    
        return $this;
    }

    /**
     * Get prenom
     *
     * @return string 
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set NomPrenom
     *
     * @param string $nomPrenom
     * @return Client
     */
    public function setNomPrenom($nomPrenom)
    {
        $this->NomPrenom = $nomPrenom;
    
        return $this;
    }

    /**
     * Get NomPrenom
     *
     * @return string 
     */
    public function getNomPrenom()
    {
        return $this->NomPrenom;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Client
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Client
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    
        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Add dossiers
     *
     * @param Forthicime\DossierBundle\Entity\Dossier $dossiers
     * @return Client
     */
    public function addDossier(\Forthicime\DossierBundle\Entity\Dossier $dossiers)
    {
        $this->dossiers[] = $dossiers;
    
        return $this;
    }

    /**
     * Remove dossiers
     *
     * @param \Forthicime\ClientBundle\Entity\ForthicimeDossierBundle:Dossier $dossiers
     */
    public function removeDossier(\Forthicime\DossierBundle\Entity\Dossier $dossiers)
    {
        $this->dossiers->removeElement($dossiers);
    }

    /**
     * Get dossiers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDossiers()
    {
        return $this->dossiers;
    }
}