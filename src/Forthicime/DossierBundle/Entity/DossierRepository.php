<?php 

namespace Forthicime\DossierBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DossierRepository extends EntityRepository
{

 	public function getLatest($medecin)
	{
		$latest_dossiers = $this->createQueryBuilder("d")
             ->select("d.id, d.libelle, c.nom, c.prenom, d.created")   
             ->leftJoin("d.client", "c")           
             ->where("d.medecin = :medecin")
             ->setParameter('medecin', $medecin)    
             ->setFirstResult(0)
             ->setMaxResults(10)       
             ->orderBy('d.created')                     
             ->getQuery()
             ->getResult();

            for ($i=0; $i < sizeof($latest_dossiers); $i++) { 
                  $libelle = $latest_dossiers[$i]["libelle"];
                  $libelle = substr($libelle, 6, 2)."/".substr($libelle, 4, 2)."/".substr($libelle, 0, 4);
                  $latest_dossiers[$i]["libelle"] = $libelle;
            }
          
            return $latest_dossiers;
	}
}