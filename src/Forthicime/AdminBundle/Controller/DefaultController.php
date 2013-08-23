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

        $usr= $this->get('security.context')->getToken()->getUser();
        if ($usr->getIdentifiant() != "admin")
        {
            return $this->redirect($this->generateUrl('_welcome'));
        }

		// ======================================
    	// Get repository
    	$em = $this->getDoctrine()->getManager();
    	$repository = $em->getRepository('ForthicimeMedecinBundle:LoginHistory');

        $year = $this->GetYearFromDB();  
        $month = $this->GetMonth();

        $currentDate = new \DateTime();
        $currentMonth = intval($currentDate->format('m')) - 1;
        
 		$form = $this->createFormBuilder()
            ->add('Annee', 'choice', array('choices' => $year))
            ->add('Mois',  'choice', array('choices' => $month, 'data' => $currentMonth))
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


         // Get latest accessed dossier        
        $repository = $em->getRepository('ForthicimeDossierBundle:AccessHistory');
        $latest_read = $repository->getLatestReadFromAll();


        return $this->render('ForthicimeAdminBundle:Default:index.html.twig',
        	array(
        			"form" => $form->createView(),
        			"last_connections" => $query->getResult(),        			
        			"never_connected" => $never_connected,
                    "total_never_connected" => $total_never_connected,
                    "max_never_connected" => $max_not_connected,
        			"statistic" => $statistic,
                    "latest_read" => $latest_read,
                    "identifiant" => $usr->getIdentifiant()
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
    					 	 "Mai", "Juin", "Juillet", "Août", "Septembre", 
    					 	 "Octobre", "Novembre", "Décembre");


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

     // This method will return the number of connection
    // made for each mounth and the number of medical 
    // file that was accessed
    public function GetConnectionCountByMonthAction()
    {

        // Get year and month value from query string
        $request = $this->getRequest();
        $year = $request->query->get('year');
        $month = $request->query->get('month');

        $m = array("Janvier" => 1, "Février" => 2, "Mars" => 3, "Avril" => 4, "Mai" => 5, "Juin" => 6, "Juillet" => 7, 
            "Aout" => 8, "Septembre" => 9, "Octobre" => 10, "Novembre" => 11, "Decembre" => 12);

        // Get repository
        $em = $this->getDoctrine()->getManager();

        $total_count = array();


        $number_of_day = cal_days_in_month(CAL_GREGORIAN, $m[$month], $year);

        //      Get count per day
        for ($i=1; $i <= $number_of_day; $i++) { 
    
            $date_from = new \DateTime($year."-".$m[$month]."-".$i." 00:00:00");
            $date_to = new \DateTime($year."-".$m[$month]."-".$i." 24:00:00");   
    
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

            $total_count[] = array($i, 
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

    private function GetMonth()
    {
        $month = array("Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Aout", "Septembre", "Octobre", "Novembre", "Décembre");

        return $month;
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
