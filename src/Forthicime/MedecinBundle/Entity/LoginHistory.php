<?php

namespace Forthicime\MedecinBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LoginHistory
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Forthicime\MedecinBundle\Entity\LoginHistoryRepository")
 */
class LoginHistory
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
     * @ORM\Column(name="login", type="datetime")
     */
    private $login;

    /**
    * @ORM\ManyToOne(targetEntity="\Forthicime\MedecinBundle\Entity\Medecin", inversedBy="LoginHistory")
    * @ORM\JoinColumn(name="medecin_id", referencedColumnName="id")    
    */
    private $medecin;


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
     * Set login
     *
     * @param \DateTime $login
     * @return LoginHistory
     */
    public function setLogin($login)
    {
        $this->login = $login;
    
        return $this;
    }

    /**
     * Get login
     *
     * @return \DateTime 
     */
    public function getLogin()
    {
        return $this->login;
    }

     /**
     * Set medecin
     *
     * @param Forthicime\MedecinBundle\Entity\Medecin $medecin
     * @return LoginHistory
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
}
