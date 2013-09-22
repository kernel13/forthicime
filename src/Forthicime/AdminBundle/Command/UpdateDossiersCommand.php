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
    private $synchronizationLine;
    private $em;
    private $logger;
    private $error;

    function __construct() {
      $this->synchronizationLine = new SynchronizationLine();
      $this->em = $this->getContainer()->get('doctrine')->getManager();
      $this->logger = $this->getContainer()->get('logger');
      $this->error = 0;
    }

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

       $m = null;
       $c = null;
     
       $this->logger->info("Working on ".$libelle);
      
       // Retrieve the current synchronization
       $synchronization = $this->em->getRepository('ForthicimeAdminBundle:Synchronization')->find($synchronizationID);

       if($this->IsNullOrEmpty($synchronization))
         throw new \Exception("La synchronization en cours ne peut etre récupéré", -201);

       // Initialize synchronizationLine object
       $this->synchronizationLine->setSynchronization($synchronization);
       $this->synchronizationLine->setTableName("dossier");

       $this->logger->info("Action: ".$action);

       switch ($action) {
           case 'Ajout':

               $dossier = new Dossier();

               if( !$this->IsNullOrEmpty($this->GetMedecin($medecin)) && 
                    !$this->IsNullOrEmpty($this->GetClient($client)) )
               {
                   // Add the dossier
                   try {
                       $d = $this->em->getRepository('ForthicimeDossierBundle:Dossier')->find($id);
                       if( $this->IsNullOrEmpty($d) ) { 
                           $dossier->setId($id);
                           $dossier->setMedecin($m);              
                           $dossier->setClient($c);
                           $dossier->setNumeric($numeric);
                           $dossier->setLibelle($libelle);  

                           $this->em->persist($dossier);
                           $this->em->flush();               
                       } else {
                           $this->synchronizationLine->setMessage("Le dossier avec l'ID ".$id." existe déjá");
                       } 
                    } catch(\Exception $e) {                     
                      $this->logger->err("An error occured during the save of the analysis ".$id);
                      $this->logger->err($e->getMessage());
                    } 
                }

               break;
           
           case 'Modif':
               
               $dossier = null;

              if( !$this->IsNullOrEmpty($this->GetMedecin($medecin)) && 
                  !$this->IsNullOrEmpty($this->GetClient($client)))
              {
                 // Modify the dossier
                 try{                
                     $dossier = $this->em->getRepository('\Forthicime\DossierBundle\Entity\Dossier')->find($id);        

                     if(!$this->IsNullOrEmpty($dossier))                                    
                     { 
                       $dossier->setMedecin($m);                   
                       $dossier->setClient($c);
                       $dossier->setNumeric($numeric);
                       $dossier->setLibelle($libelle);   

                       $this->em->flush();                  
                     } else {
                        $this->synchronizationLine->setMessage("L'analyse avec l'ID ".$id." n'a pas été trouvé et ne peut donc être modifié");
                        $this->error = -1;
                     }

                } catch(\Exception $e) {                  
                    $this->logger->err("An error occured during the save of the analysis ".$id);
                    $this->logger->err($e->getMessage());
                } 
              }

              break;

           case 'Supprime':
               
               $dossier = null;

               try{
                  $dossier = $this->em->getRepository('ForthicimeDossierBundle:Dossier')->find($id);

                  if(!$this->IsNullOrEmpty($dossier))                                    
                  { 
                     $this->synchronizationLine->setMessage("Dossier ID: ".$dossier->getId()." Libelle: ".$dossier->getNom());
                     $this->em->remove($dossier);
                     $this->em->flush();  
                  } else {
                    $this->synchronizationLine->setMessage("L'analyse avec l'ID ".$id." n'a pas été trouvé et ne peut donc être supprimé");
                    $this->error = -1;
                  }
                }catch(\Exception $e){
                  $this->logger->err("An error occured during the delete of the Dossier ".$id);
                  $this->logger->err($e->getMessage());
                  $this->error = $e->getCode();
                } 

               break;    
           default:
              $this->logger->err("Invalid  action ".$action);
              $this->synchronizationLine->setMessage("L'opération fourni est invalide pour le dossier ".$id.". L'opération attendue est: Ajout, Modif ou Supprime et a obtenue: ".$action);               
              $this->error = -1;

               break;
       }

        try {
            
            $this->synchronizationLine->setCommand($action);
            $this->synchronizationLine->setTableId($id);     
            $this->synchronizationLine->setReturnCode($this->error);                     
            $this->em->persist($this->synchronizationLine);
            $this->em->flush();      
                 
        } catch (\Exception $e) {
            $this->logger->err("An error occured during the save of the SynchronizationLine.");
            $this->logger->err($e->getMessage());
            $this->error = $e->getCode();
        }

        $output->writeln($this->error);
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

    //Find a medecin by id
    private function GetMedecin($medecin)
    {
        $m = null;

       try {
            $m = $this->em->getRepository('ForthicimeMedecinBundle:Medecin')->find($medecin);

            if( $this->IsNullOrEmpty($m)){
               $this->synchronizationLine->setMessage("Le medecin avec l'ID ".$medecin." n'a pas été trouvé");
               $this->error = -1;  
            }
                                
       } catch (\Exception $e) {                  
            $this->logger->err($e->getMessage());
       }  

       return $m   
    }

    //Find a client by ID
    private function GetClient($client)
    {
      $c = null;

      try {        
            $c = $this->em->getRepository('ForthicimeClientBundle:Client')->find($client);    

            if( $this->IsNullOrEmpty($c) ){
                $this->synchronizationLine->setMessage("Client avec l'ID ".$client." n'a pas été trouvé");  
                $this->error = -1;
            }
            
       } catch (\Exception $e) {                  
          $this->logger->err($e->getMessage());
       }

       return $c;
    }
}