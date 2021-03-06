<?php

namespace Forthicime\DossierBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * AccessHistoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AccessHistoryRepository extends EntityRepository
{
	public function getLatestRead($medecin)
	{
		$dossiers = $this->createQueryBuilder("a")
             ->select("d.id, d.libelle, c.nom, c.prenom, a.access, d.created, m.nom as nomMedecin")   
             ->innerJoin("a.dossier", "d")
             ->innerJoin("d.client", "c")                                
             ->innerJoin("d.medecin", "m") 
             ->where("d.medecin = :medecin")             
             ->setParameter('medecin', $medecin)    
             ->setMaxResults(10)       
             ->orderBy('a.access', 'DESC')                     
             ->getQuery()
             ->getResult();	

            for ($i=0; $i < sizeof($dossiers); $i++) { 
                  $libelle = $dossiers[$i]["libelle"];
                  $libelle = substr($libelle, 6, 2)."/".substr($libelle, 4, 2)."/".substr($libelle, 0, 4);
                  $dossiers[$i]["libelle"] = $libelle;
            }

            return $dossiers;            
	}

      public function getLatestReadFromAll()
      {
            $dossiers = $this->createQueryBuilder("a")
             ->select("d.id, d.libelle, c.nom, c.prenom, a.access, d.created, m.nom as nomMedecin")   
             ->innerJoin("a.dossier", "d")
             ->innerJoin("d.client", "c")                 
             ->innerJoin("d.medecin", "m")             
             ->setMaxResults(10)       
             ->orderBy('a.access', 'DESC')                     
             ->getQuery()
             ->getResult();   

              for ($i=0; $i < sizeof($dossiers); $i++) { 
                  $libelle = $dossiers[$i]["libelle"];
                  $libelle = substr($libelle, 6, 2)."/".substr($libelle, 4, 2)."/".substr($libelle, 0, 4);
                  $dossiers[$i]["libelle"] = $libelle;
            }

            return $dossiers;    
      }


}
