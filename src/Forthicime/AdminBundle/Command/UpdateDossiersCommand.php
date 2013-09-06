<?php

namespace Forthicime\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Session\Session;

use Forthicime\MedecinBundle\Entity\Medecin;
use Forthicime\ClientBundle\Entity\Client;
use Forthicime\DossierBundle\Entity\Dossier;
use Forthicime\AdminBundle\Entity\SynchronizationLine;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class UpdateDossiersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('UpdateDossiers')
            ->setDescription('Recupere les meta donnee des analyse et met a jour la base de donnee')
            ->addArgument('action', InputArgument::REQUIRED, 'Who do you want to greet?')
            ->addArgument('id', InputArgument::REQUIRED, 'Who do you want to greet?') 
            ->addArgument('numeric', InputArgument::REQUIRED, 'Who do you want to greet?')
            ->addArgument('medecin', InputArgument::REQUIRED, 'Who do you want to greet?')
            ->addArgument('client', InputArgument::REQUIRED, 'Who do you want to greet?')            
            ->addArgument('libelle', InputArgument::REQUIRED, 'Who do you want to greet?')
            ->addArgument('synchronizationID', InputArgument::REQUIRED, 'Who do you want to greet?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
       $id = $input->getArgument('id');
       $medecin = $input->getArgument('medecin');
       $client = $input->getArgument('client');
       $numeric = $input->getArgument('numeric');
       $libelle = $input->getArgument('libelle');
       $action = $input->getArgument('action');
       $synchronizationID = $input->getArgument('synchronizationID');

       $logger = $this->getContainer()->get('logger');
       $error = 0;
       $m = null;
       $c = null;

       $synchronizationLine = new SynchronizationLine();

       $logger->info("Working on ".$libelle);

       //$serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new JsonEncoder()));

       $em = $this->getContainer()->get('doctrine')->getManager();
      
       $synchronization = $em->getRepository('ForthicimeAdminBundle:Synchronization')->find($synchronizationID);

       if($this->IsNullOrEmpty($synchronization))
         throw new \Exception("La synchronization en cours ne peut etre récupéré", -201);

       $synchronizationLine->setSynchronization($synchronization);
       $synchronizationLine->setTableName("dossier");

       switch ($action) {
           case 'Ajout':
 
               $dossier = new Dossier();

                // Find the medecin
               try {
                    $m = $em->getRepository('ForthicimeMedecinBundle:Medecin')->find($medecin);
                    if($this->IsNullOrEmpty($m))
                      throw new \Exception("Le medecin avec l'id ".$medecin." est introuvable" , -100);
                      
               } catch (\Exception $e) {                  
                  $synchronizationLine->setMessage($e->getMessage());
                  $error = $e->getCode();

                  $logger->err($e->getMessage());
               }

               //Find the client
              try {
                    $c = $em->getRepository('ForthicimeClientBundle:Client')->find($client);    
                    if($this->IsNullOrEmpty($c))
                      throw new \Exception("Le client avec l'id ".$client." est introuvable" , -110);
                      
               } catch (\Exception $e) {                  
                  $synchronizationLine->setMessage($e->getMessage());
                  $error = $e->getCode();

                  $logger->err($e->getMessage());
               }

               if( !$this->IsNullOrEmpty($m) && !$this->IsNullOrEmpty($c))
               {
                   // Add the dossier
                   try {

                       $dossier->setId($id);
                       $dossier->setMedecin($m);              
                       $dossier->setClient($c);
                       $dossier->setNumeric($numeric);
                       $dossier->setLibelle($libelle);  

                       $em->persist($dossier);
                       $em->flush();                
                    } catch(\Exception $e) {
                      $synchronizationLine->setMessage($e->getMessage());
                      $error = $e->getCode();

                      $logger->err("An error occured during the save of the Dossier ".$id);
                      $logger->err($e->getMessage());
                    } 
                }

               break;
           
           case 'Modif':
               
               $dossier = null;

              // Find the medecin
               try {
                    $m = $em->getRepository('ForthicimeMedecinBundle:Medecin')->find($medecin);
                    if($this->IsNullOrEmpty($m))
                      throw new \Exception("Le medecin avec l'id ".$medecin." est introuvable" , -100);
                      
               } catch (\Exception $e) {                  
                  $synchronizationLine->setMessage($e->getMessage());
                  $error = $e->getCode();

                  $logger->err($e->getMessage());
               }

               //Find the client
              try {
                    $c = $em->getRepository('ForthicimeClientBundle:Client')->find($client);    
                    if($this->IsNullOrEmpty($c))
                      throw new \Exception("Le client avec l'id ".$client." est introuvable" , -110);
                      
               } catch (\Exception $e) {                  
                  $synchronizationLine->setMessage($e->getMessage());
                  $error = $e->getCode();

                  $logger->err($e->getMessage());
               }

              if( !$this->IsNullOrEmpty($m) && !$this->IsNullOrEmpty($c))
              {
                 // Modify the dossier
                 try{                
                     $dossier = $em->getRepository('\Forthicime\DossierBundle\Entity\Dossier')->find($id);                                             
                     $dossier->setMedecin($m);                   
                     $dossier->setClient($c);
                     $dossier->setNumeric($numeric);
                     $dossier->setLibelle($libelle);                   

                } catch(\Exception $e) {                  
                    $synchronizationLine->setMessage($e->getMessage());
                    $error = $e->getCode();

                    $logger->err("An error occured during the save of the Dossier ".$id);
                    $logger->err($e->getMessage());
                } 
              }

              break;

           case 'Supprime':
               
               $dossier = null;

               try{
                  $dossier = $em->getRepository('ForthicimeDossierBundle:Dossier')->find($id);
                  $synchronizationLine->setMessage("Dossier ID: ".$dossier->getId()." Libelle: ".$dossier->getNom());
                  $em->remove($dossier);
                }catch(\Exception $e){
                  $synchronizationLine->setMessage($e->getMessage());
                  $error = $e->getCode();

                  $logger->err("An error occured during the delete of the Dossier ".$id);
                  $logger->err($e->getMessage());
                } 

               break;    
           default:

              $synchronizationLine->setMessage("L'opération fourni est invalide pour le dossier ".$id.". L'opération attendue est: Ajout, Modif ou Supprime et a obtenue: ".$action);               
              $error = -1;

               break;
       }

        try {
            $synchronizationLine->setCommand($action);
            $synchronizationLine->setTableId($id);     
            $synchronizationLine->setReturnCode($error);                     
            $em->persist($synchronizationLine);
            $em->flush();      
                 
        } catch (\Exception $e) {
            $logger->err("An error occured during the save of the SynchronizationLine.");
            $logger->err($e->getMessage());
            $error = $e->getCode();
        }

        $output->writeln($error);
    }

     private function IsNullOrEmpty($value)
    {
        $isNull = false;

        if (!isset($value))
          $isNull = true;
        elseif(getType($value) === "string" && trim($value) === '')
          $isNull = true;
        elseif(getType($value) === "integer" && $value === 0)
          $isNull = true;
        elseif(getType($value) === "array" && count($value) == 0)
          $isNull = true;

        return $isNull;
    }
}