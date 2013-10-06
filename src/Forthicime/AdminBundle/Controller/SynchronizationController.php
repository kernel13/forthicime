<?php

namespace Forthicime\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext; 
use Symfony\Component\HttpFoundation\Response;

use Forthicime\AdminBundle\Entity\SynchronizationLine;
use Forthicime\MedecinBundle\Entity\Medecin;
use Forthicime\ClientBundle\Entity\Client;
use Forthicime\DossierBundle\Entity\Dossier;
use Doctrine\ORM\Tools\Pagination\Paginator;

class SynchronizationController extends Controller
{
   
    public function indexAction($page)
    {
        $logger = $this->get('logger');

         $per_page = 30;
    	// ======================================
    	// Get repository
    	$em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('ForthicimeAdminBundle:Synchronization');
    	$query = $repository->createQueryBuilder("s")
                            ->select("s.id, s.start, s.end, s.nbTransaction, s.nbSuccess, s.nbFailure, s.created, s.updated")
                            ->setFirstResult(($page - 1) * $per_page)
                            ->setMaxResults($per_page)
                            ->orderBy("s.start", "desc")
                            ->getQuery();

        $paginator = new Paginator($query, $fetchJoinCollection = false);
        $synchronizations = $query->getResult();

        $total = $em->getRepository('ForthicimeAdminBundle:Synchronization')
                    ->createQueryBuilder("s")
                    ->select("COUNT(s.id)")
                    ->getQuery()
                    ->getSingleScalarResult();

        return $this->render('ForthicimeAdminBundle:Synchronization:index.html.twig',
    	array(
    			"synchronizations" => $synchronizations,
                'current_page' => $page,
                'last_page' => ceil($total / $per_page) -1
    		));
    }

    public function synchronizationDetailAction($category, $synchronizationId)
    {
        $nom = "";
        $logger = $this->get('logger');

        // ======================================
        // Get repository
         $em = $this->getDoctrine()->getManager();
         $synchronizations = $em->getRepository('ForthicimeAdminBundle:SynchronizationLine')
                                ->findBy(
                                     array("synchronization" => $synchronizationId, 
                                           "tableName" => $category)
                                 );

       
       $synchronization = $em->getRepository('ForthicimeAdminBundle:Synchronization')->find($synchronizationId);


        //$serializer = $this->get('serializer');
        //$syncData = $serializer->serialize($synchronizations, 'json');
        $logger->info("======== debut ==========");

        $datas = array();
        foreach ($synchronizations as $key => $value) {
            $nom = "";
            $logger->info("======== start synchronizations ==========");       


            if($value->getTableId())
            {  
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
            }
            $logger->info("======== end synchronizations ==========");
            $datas[] = array(
                    "id" => $value->getId(),
                    "command" => $value->getCommand(),
                    "returnCode" => $value->getReturnCode(),
                    "tableName" => $value->getTableName(),
                    "name" => utf8_encode($nom),
                    "message" => $value->getMessage(),
                    "SynchTime" => $synchronization->getStart()
                );
        }

         $logger->info("======== fin ==========");

        return new Response(json_encode($datas));  
    }

   
}
