<?php 

namespace Forthicime\ClientBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ClientRepository extends EntityRepository
{
	public function getLatest($medecin)
	{
		return $this->createQueryBuilder("c")
             ->select("c.id, c.nom, c.prenom, c.NomPrenom, c.created")
             ->distinct("c.NomPrenom")
             ->leftJoin("c.dossiers", "d")
             ->where("d.medecin = :medecin")
             ->setParameter('medecin', $medecin)    
             ->setFirstResult(0)
             ->setMaxResults(10)       
             ->orderBy('c.created')                     
             ->getQuery()
             ->getResult();
	}
}