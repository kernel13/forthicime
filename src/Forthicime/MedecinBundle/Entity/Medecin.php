<?php

namespace Forthicime\MedecinBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Medecin
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Forthicime\MedecinBundle\Entity\MedecinRepository")
 */
class Medecin implements UserInterface
{

    /**
    * @ORM\OneToMany(targetEntity="Forthicime\DossierBundle\Entity\Dossier", mappedBy="medecin")
    */
    private $dossiers;

    /**
    * @ORM\OneToMany(targetEntity="Forthicime\MedecinBundle\Entity\LoginHistory", mappedBy="medecin")
    */
    private $loginHistories;

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
     * @ORM\Column(name="identifiant", type="string", length=255)
     */
    private $identifiant;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

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
        $this->dossiers = new ArrayCollection();
    }


     /**
     * Set id
     *
     * @param integer $id
     * @return Medecin
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
     * @return Medecin
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
     * Set identifiant
     *
     * @param string $identifiant
     * @return Medecin
     */
    public function setIdentifiant($identifiant)
    {
        $this->identifiant = $identifiant;
    
        return $this;
    }

    /**
     * Get identifiant
     *
     * @return string 
     */
    public function getIdentifiant()
    {
        return $this->identifiant;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Medecin
     */
    public function setPassword($password)
    {
        $this->password = $password;
    
        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set created
     *
     * @param \datetime $created
     * @return Medecin
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
     * @return Medecin
     */
    public function setUpdated(\datetime $updated)
    {
        $this->updated = $updated;
    
        return $this;
    }

    /**
     * Get updated
     *
     * @return \timestamp 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

      public function getRoles()
    {
        return array('ROLE_ADMIN');
    }

    public function getSalt()
    {
    }

    public function getUsername()
    {
        return $this->identifiant;
    }

    public function eraseCredentials()
    {
    }

     /**
     * Add dossier
     *
     * @param Forthicime\DossierBundle\Entity\Dossier $dossiers
     * @return Dossier
     */
    public function addDossier(\Forthicime\DossierBundle\Entity\Dossier $dossiers)
    {
        $this->dossiers[] = $dossiers;
    
        return $this;
    }

    /**
     * Remove dossiers
     *
     * @param \Forthicime\MedecinBundle\Entity\ForthicimeDossierBundle:Dossier $dossiers
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

      /**
     * Add loginHistory
     *
     * @param Forthicime\MedecinBundle\Entity\LoginHistory $loginHistory
     * @return LoginHistory
     */
    public function addLoginHistory(\Forthicime\MedecinBundle\Entity\LoginHistory $loginHistory)
    {
        $this->loginHistories[] = $loginHistory;
    
        return $this;
    }

    /**
     * Remove loginHistory
     *
     * @param \Forthicime\MedecinBundle\Entity\ForthicimeMedecinBundle:LoginHistory $loginHistory
     */
    public function removeLoginHistory(\Forthicime\MedecinBundle\Entity\LoginHistory $loginHistory)
    {
        $this->loginHistories->removeElement($loginHistory);
    }

    /**
     * Get loginHistories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLoginHistories()
    {
        return $this->loginHistories;
    }
}