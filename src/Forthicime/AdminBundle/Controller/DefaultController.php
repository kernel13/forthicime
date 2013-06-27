<?php

namespace Forthicime\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext; 
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $max_not_connected = 20;

    	$logger = $this->get('logger');

		// ======================================
    	// Get repository
    	$em = $this->getDoctrine()->getManager();
    	$repository = $em->getRepository('ForthicimeMedecinBundle:LoginHistory');

        $year = $this->GetYearFromDB();  
 		$form = $this->createFormBuilder()
            ->add('Annee', 'choice', array(
                'choices' => $year,
            ))
            ->getForm();
        // ==========================================
		

    	// ======================================	
    	// Get last connected user
    	$query = $repository->createQueryBuilder("l")
                     ->select("m.nom, l.login")  
                     ->leftJoin("l.medecin", "m")
                     ->orderBy("l.login", "DESC")
                     ->setMaxResults(10)                 
                     ->getQuery();
    	// ======================================
	
		// ======================================
        // Get users that never connected
        $qlh = $em->createQueryBuilder();
        $login = $qlh->select("mm.id")
        			->from("Forthicime\MedecinBundle\Entity\LoginHistory", "m")
                    ->innerJoin("m.medecin", "mm")
                    ->getDQL();

		$qme = $em->createQueryBuilder();
        $never_connected = $qme->select("me")        					  
        					  ->from("Forthicime\MedecinBundle\Entity\Medecin", "me")
        					  ->where($qme->expr()->notIn('me.id', $login))
                              ->setMaxResults($max_not_connected)
        					  ->getQuery()                              
        					  ->getResult();


        $qma = $em->createQueryBuilder();
        $total_never_connected = $qma->select("COUNT(med.id)")                           
                                ->from("Forthicime\MedecinBundle\Entity\Medecin", "med")
                                ->where($qma->expr()->notIn('med.id', $login))
                                ->getQuery()                              
                                ->getSingleScalarResult();

    	// ======================================


       // ======================================
       //		Get number of itemsa
	    $medecin = $em->getRepository('ForthicimeMedecinBundle:Medecin');
		$nbMedecin = $medecin->createQueryBuilder("m")
	             ->select("COUNT(m.id)")                     	             	             
	             ->getQuery()
	             ->getSingleScalarResult();    	   

		$client = $em->getRepository('ForthicimeClientBundle:Client');
		$nbClient = $client->createQueryBuilder("c")
	             ->select("COUNT(c.id)")                     	             	             
	             ->getQuery()
	             ->getSingleScalarResult(); 

	    $dossier = $em->getRepository('ForthicimeDossierBundle:Dossier');
		$nbDossier = $dossier->createQueryBuilder("d")
	     		 ->select("COUNT(d.id)")                     	             	             
	     		 ->getQuery()
	     		 ->getSingleScalarResult();   

	    $statistic = array(
	    	"nbMedecin" => $nbMedecin,
	    	"nbClient" => $nbClient,
	    	"nbDossier" => $nbDossier
	    	);

        return $this->render('ForthicimeAdminBundle:Default:index.html.twig',
        	array(
        			"form" => $form->createView(),
        			"last_connections" => $query->getResult(),        			
        			"never_connected" => $never_connected,
                    "total_never_connected" => $total_never_connected,
                    "max_never_connected" => $max_not_connected,
        			"statistic" => $statistic
        		));
    }

    // This method will return the number of connection
    // made for each mounth and the number of medical 
    // file that was accessed
    public function GetConnectionCountAction()
    {

    	// Get year value from query string
		$request = $this->getRequest();
    	$year = $request->query->get('year');

		// Get repository
    	$em = $this->getDoctrine()->getManager();
  	
    	// Setup mounth array
    	$mounth = array(1 => "Janvier", "Février", "Mars", "Avril", 
    					 	 "Mai", "Juin", "Juillet", "Aout", "Septembre", 
    					 	 "Octobre", "Novembre", "Decembre");


		$total_count = array();

    	// 		Get count per mounth
    	for ($i=1; $i <= 12; $i++) { 
	
			$date_from = new \DateTime($year."-".$i."-01 00:00:00");
			$date_to = new \DateTime($year."-".$i."-31 24:00:00");
	
			// Get number of connection
			$loginRepo = $em->getRepository('ForthicimeMedecinBundle:LoginHistory');
    		$totalConnection = $loginRepo->createQueryBuilder("l")
                     ->select("COUNT(l.login)")                     
                     ->where("l.login BETWEEN :date_from AND :date_to")
                     ->setParameter('date_from', $date_from)
                     ->setParameter('date_to', $date_to)
                     ->getQuery()
                     ->getSingleScalarResult();    	    		

    		 // Get number of accessed file
    		 $dossiers = $em->getRepository('ForthicimeDossierBundle:AccessHistory');
    		 $totalAccess = $dossiers->createQueryBuilder("d")
                     ->select("COUNT(d.access)")                     
                     ->where("d.access BETWEEN :date_from AND :date_to")
                     ->setParameter('date_from', $date_from)
                     ->setParameter('date_to', $date_to)
                     ->getQuery()
                     ->getSingleScalarResult();    	    		

			$total_count[] = array($mounth[$i], 
								   $totalConnection, 
								   $totalAccess);			
    	}

    	return new Response(json_encode($total_count, JSON_NUMERIC_CHECK)); 
    }

    // Return all the available years in the database
    private function GetYearFromDB()
    {
		$em = $this->getDoctrine()->getManager();
    	$repository = $em->getRepository('ForthicimeMedecinBundle:LoginHistory');

		// Get all year available in the database
    	$annee =  $repository->createQueryBuilder("l")
                     ->select("SUBSTRING(l.login, 1, 4)") 
                     ->orderBy("l.login", "DESC") 
                     ->distinct("l.login")                 
                     ->getQuery();

        $year = array();
        foreach ($annee->getArrayResult() as $key => $value) {
        	$year[] = $value[1];        	
        }

        return $year;
    }

 	public function loginAction()
    {

		// return $this->render('ForthicimeMedecinBundle:Medecin:login.html.twig', array('form' => $form->createView()));
		  // get the error if any (works with forward and redirect -- see below)
	      if ($this->get('request')->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
	          $error = $this->get('request')->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
	      } else {
	          $error = $this->get('request')->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
	      }
	 
	      return $this->render('ForthicimeAdminBundle:Default:login.html.twig', array(
	          // last username entered by the user
	          'identifiant' => $this->get('request')->getSession()->get(SecurityContext::LAST_USERNAME),
	          'error' => $error,
	      ));
    }    
}
