<?php

namespace Forthicime\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Forthicime\MedecinBundle\Entity\Medecin;
use Symfony\Component\Security\Core\SecurityContext; 

class MedecinController extends Controller
{
   
    public function createAction($id, $nom, $identifiant, $password)
    {
    	$medecin = new Medecin();
    	$medecin.setNom($nom);
    	$medecin.setIdentifiant($identifiant);
    	$medecin.setPassword($password);

    	$em = $this->getDoctrine()->getManager();
    	$em->persist($medecin);
    	$em->flush();

    	return new Response('Le medecin a ete enregistre avec succes '.$medecin->getId());
    }

   
}
