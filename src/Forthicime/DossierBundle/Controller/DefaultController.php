<?php

namespace Forthicime\DossierBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Forthicime\DossierBundle\Entity\Dossier;
use Symfony\Component\Security\Core\SecurityContext; 
use Symfony\Component\HttpFoundation\Response; 
use Forthicime\DossierBundle\Entity\AccessHistory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    public function indexAction($client, $page)
    {
        $per_page = 10;
    	$em = $this->getDoctrine()->getManager();
    	$usr= $this->get('security.context')->getToken()->getUser();

        // $dossiers = $em->getRepository('ForthicimeDossierBundle:Dossier')->findBy(
        //  		array('medecin' => $usr->getId(), 'client' => $client),
        //  		array('created' => 'DESC')
        //  	);
	     
        $repository = $em->getRepository('ForthicimeDossierBundle:Dossier');

        $dossiers = $repository->createQueryBuilder("d")
                     ->select("d")                    
                     ->where("d.medecin = :medecin")
                     ->andWhere("d.client = :client")
                     ->setParameter('medecin', $usr->getId())
                     ->setParameter("client", $client)
                     ->setFirstResult(($page - 1) * $per_page)
                     ->setMaxResults($per_page)
                     ->orderBy('d.created', 'desc')                     
                     ->getQuery()
                     ->getResult();

        $total = $repository->createQueryBuilder("d")
                     ->select("COUNT(d.id)")                    
                     ->where("d.medecin = :medecin")
                     ->andWhere("d.client = :client")
                     ->setParameter('medecin', $usr->getId())
                     ->setParameter("client", $client)                                       
                     ->getQuery()
                     ->getSingleScalarResult();

	    $c = $em->getRepository('ForthicimeClientBundle:Client')->find($client);

        if(!$c) throw new NotFoundHttpException('Sorry not existing!');

        // Get latest added clients
        $repository = $em->getRepository('ForthicimeClientBundle:Client');
        $latest_client = $repository->getLatest($usr->getId());

        // Get latest dossier added
        $repository = $em->getRepository('ForthicimeDossierBundle:Dossier');
        $latest_dossiers = $repository->getLatest($usr->getId());

        // Get latest accessed dossier
        $repository = $em->getRepository('ForthicimeDossierBundle:AccessHistory');
        $latest_read = $repository->getLatestRead($usr->getId());


        return $this->render('ForthicimeDossierBundle:Default:index.html.twig', 
        	array(
        		'dossiers' => $dossiers,
                'client_id' => $client,
        		'client' => $c->getNom()." ".$c->getPrenom(),
                'latest_client' => $latest_client,
                'latest_dossiers' => $latest_dossiers,
                'latest_read' => $latest_read,
                'current_page' => $page, 
                'last_page' => ceil($total / $per_page) -1,
        		));
    }

    public function showAction($id)
    {
		$em = $this->getDoctrine()->getManager();
        $dossier = $em->getRepository('ForthicimeDossierBundle:Dossier')->find($id);

         if(!$dossier) throw new NotFoundHttpException('Sorry not existing!');


        //Update timestamp on read
        $access = new AccessHistory();      
        $access->setAccess(new \DateTime());          
        //$dossier->addAccessHistory($access);
        $access->setDossier($dossier);
        $em->persist($access);
        $em->flush();
        
        $filename = $dossier->getLibelle();
        	
		$file = "/Users/stephane/Sites/Forthicime/Analyses/".$filename.".pdf";
		$response = new Response();
		//$response->clearHttpHeaders();
		$response->setStatusCode(200);
		$response->headers->set('content-type', 'application/pdf');
		$response->headers->set('Pragma', 'public'); //optional cache header
		$response->headers->set('Expires', 0); //optional cache header
		$response->headers->set('Content-Disposition', sprintf("attachment; filename=%s.pdf", $filename));
		$response->headers->set('Content-Transfer-Encoding', 'binary');
		$response->headers->set('Content-Length', filesize($file));
		$response->setContent(file_get_contents($file));
		return $response;
	    }
}
