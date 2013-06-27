<?php

namespace Forthicime\MedecinBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Forthicime\MedecinBundle\Entity\Medecin;
use Symfony\Component\Security\Core\SecurityContext; 
use Doctrine\ORM\Tools\Pagination\Paginator;

class MedecinController extends Controller
{
    public function indexAction($page)
    {
        $per_page = 20;
        $usr= $this->get('security.context')->getToken()->getUser();

    	# Get all patient
        $em = $this->getDoctrine()->getManager();
        //$users = $em->getRepository("ForthicimeClientBundle:Client")->findAll();

        $repository = $em->getRepository('ForthicimeClientBundle:Client');

        $total = $repository->createQueryBuilder("c")
                     ->select("COUNT(c.nom)")
                     ->distinct("c.NomPrenom")
                     ->leftJoin("c.dossiers", "d")
                     ->where("d.medecin = :medecin")
                     ->setParameter('medecin', $usr->getId())
                     ->getQuery()
                     ->getSingleScalarResult();


        $query = $repository->createQueryBuilder("c")
                     ->select("c.id, c.nom, c.prenom, c.NomPrenom")
                     ->distinct("c.NomPrenom")
                     ->leftJoin("c.dossiers", "d")
                     ->where("d.medecin = :medecin")
                     ->setParameter('medecin', $usr->getId())
                     ->setFirstResult(($page - 1) * $per_page)
                     ->setMaxResults($per_page)
                     ->orderBy('c.nom, c.NomPrenom')                     
                     ->getQuery();

        $paginator = new Paginator($query, $fetchJoinCollection = false);
        $users = $query->getResult();

        // Get latest added clients
        $latest_client = $repository->getLatest($usr->getId());

        // Get latest dossier added
        $repository = $em->getRepository('ForthicimeDossierBundle:Dossier');
        $latest_dossiers = $repository->getLatest($usr->getId());

        // Get latest accessed dossier
        $repository = $em->getRepository('ForthicimeDossierBundle:AccessHistory');
        $latest_read = $repository->getLatestRead($usr->getId());

        return $this->render('ForthicimeMedecinBundle:Medecin:index.html.twig',
            array(  'clients' => $users, 
                    'current_page' => $page, 
                    'last_page' => ceil($total / $per_page) -1,
                    'latest_client' => $latest_client,
                    'latest_dossiers' => $latest_dossiers,
                    'latest_read' => $latest_read));
                    //'last_logged_in' => $last_logged_in  ));
    }

    public function createAction($id, $nom, $identifiant, $password)
    {
    	$medecin = new Medecin();
    	$medecin->setNom($nom);
    	$medecin->setIdentifiant($identifiant);
    	$medecin->setPassword($password);

    	$em = $this->getDoctrine()->getManager();
    	$em->persist($medecin);
    	$em->flush();

    	return new Response('Le medecin a ete enregistre avec succes '.$medecin.getId());
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
	 
	      return $this->render('ForthicimeMedecinBundle:Medecin:login.html.twig', array(
	          // last username entered by the user
	          'identifiant' => $this->get('request')->getSession()->get(SecurityContext::LAST_USERNAME),
	          'error' => $error,
	      ));
    }
}
