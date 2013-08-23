<?php

namespace Forthicime\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext; 
use Symfony\Component\HttpFoundation\Response;

use Forthicime\AdminBundle\Entity\SynchronizationLine;
use Forthicime\MedecinBundle\Entity\Medecin;
use Forthicime\ClientBundle\Entity\Client;
use Forthicime\DossierBundle\Entity\Dossier;

class SynchronizationController extends Controller
{
   
    public function indexAction()
    {
    	// ======================================
    	// Get repository
    	$em = $this->getDoctrine()->getManager();
    	$synchronizations = $em->getRepository('ForthicimeAdminBundle:Synchronization')->findAll();

        return $this->render('ForthicimeAdminBundle:Synchronization:index.html.twig',
    	array(
    			"synchronizations" => $synchronizations
    		));
    }

    public function synchronizationDetailAction($category, $synchronizationId)
    {
        $nom = "";

        // ======================================
        // Get repository
         $em = $this->getDoctrine()->getManager();
         $synchronizations = $em->getRepository('ForthicimeAdminBundle:SynchronizationLine')
                                ->findBy(
                                     array("synchronization" => $synchronizationId, 
                                           "tableName" => $category)
                                 );

       


        //$serializer = $this->get('serializer');
        //$syncData = $serializer->serialize($synchronizations, 'json');

        $datas = array();
        foreach ($synchronizations as $key => $value) {

            switch($value->getTableName())
            {
                case "medecin":
                    $item = $em->getRepository('ForthicimeMedecinBundle:Medecin')
                                   ->find( $value->getTableId() );
                    if($item)
                        $nom = $item->getNom();

                    break;
                case "client":
                    $item = $em->getRepository('ForthicimeClientBundle:Client')
                                   ->find( $value->getTableId() );
                    if($item) 
                        $nom = $item->getNom()." ".$item->getPrenom();

                    break;
                case "dossier":
                    $item = $em->getRepository('ForthicimeDossierBundle:Dossier')
                                   ->find( $value->getTableId() );
                    if($item)
                        $nom = $item->getLibelle();

                    break;
            }

            $datas[] = array(
                    "id" => $value->getId(),
                    "command" => $value->getCommand(),
                    "returnCode" => $value->getReturnCode(),
                    "tableName" => $value->getTableName(),
                    "name" => utf8_encode($nom)
                );
        }

        return new Response(json_encode($datas));  
    }

   
}
