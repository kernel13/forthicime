<?php

namespace Forthicime\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext; 
use Symfony\Component\HttpFoundation\Response;

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
            $datas[] = array(
                    "id" => $value->getId(),
                    "command" => $value->getCommand(),
                    "returnCode" => $value->getReturnCode(),
                    "tableName" => $value->getTableName()
                );
        }

        return new Response(json_encode($datas));  
    }

   
}
