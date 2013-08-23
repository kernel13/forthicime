<?php 

namespace Forthicime\DossierBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DossierRepository extends EntityRepository
{

 	public function getLatest($medecin)
	{
		return $this->createQueryBuilder("d")
             ->select("d.id, d.libelle, c.nom, c.prenom, d.created")   
             ->leftJoin("d.client", "c")           
             ->where("d.medecin = :medecin")
             ->setParameter('medecin', $medecin)    
             ->setFirstResult(0)
             ->setMaxResults(10)       
             ->orderBy('d.created')                     
             ->getQuery()
             ->getResult();
	}
}