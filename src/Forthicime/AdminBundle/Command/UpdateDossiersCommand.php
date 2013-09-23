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
    private $_synchronizationLine;
    private $_em;
    private $_logger;
    private $_error;

    protected function configure()
    {
        $this->setName('UpdateDossiers')
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

       $this->_synchronizationLine = new SynchronizationLine();
       $this->_em = $this->getContainer()->get('doctrine')->getManager();
       $this->_logger = $this->getContainer()->get('logger');
       $this->_error = 0;
    
       $this->_logger->info("Working on ".$libelle);
      
       // Retrieve the current synchronization
       $synchronization = $this->_em->getRepository('ForthicimeAdminBundle:Synchronization')->find($synchronizationID);

       if($this->IsNullOrEmpty($synchronization))
         throw new \Exception("La synchronization en cours ne peut etre récupéré", -201);

       // Initialize _synchronizationLine object
       $this->_synchronizationLine->setSynchronization($synchronization);
       $this->_synchronizationLine->setTableName("dossier");

       $this->_logger->info("Action: ".$action);

       switch ($action) 
       {
           case 'Ajout':

               $dossier = new Dossier();
               $m = $this->GetMedecin($medecin);
               $c = $this->GetClient($client);

               if( !$this->IsNullOrEmpty($m) && !$this->IsNullOrEmpty($c) )
               {
                   // Add the dossier
                   try {
                       $d = $this->_em->getRepository('ForthicimeDossierBundle:Dossier')->find($id);
                       if( $this->IsNullOrEmpty($d) ) { 
                           $dossier->setId($id);
                           $dossier->setMedecin($m);              
                           $dossier->setClient($c);
                           $dossier->setNumeric($numeric);
                           $dossier->setLibelle($libelle);  

                           $this->_em->persist($dossier);
                           $this->_em->flush();               
                       } else {
                           if( $d->getLibelle() == $libelle ) {
                                $this->_synchronizationLine->setMessage("Analyse déjà présente dans la base.");
                           } else {
                              $this->_synchronizationLine->setMessage("Une analyse avec le même identifiant existe. ID: ".$id." libellé: ".$d->getLibelle());
                              $this->_error = -1;   
                            }
                           
                       } 
                    } catch(\Exception $e) {                     
                      $this->_logger->err("An _error occured during the save of the analysis ".$id);
                      $this->_logger->err($e->getMessage());
                    } 
                } else {
                      $msg = "";
                      if ( $this->IsNullOrEmpty($m) )
                          $msg = "Le Medecin  n'a pas été trouvé dans la base de donnée. ID Medecin: ".$medecin;

                      if ( $this->IsNullOrEmpty($c) )
                          $msg = "Le Patient n'a pas été trouvé dans la base de donnée. ID Patient: ".$client;

                      if( $this->IsNullOrEmpty($m) && $this->IsNullOrEmpty($c) )
                          $msg = "Le patient (ID: ".$client.") et le medecin (ID: ".$medecin.") n'ont pas été trouvé dans la base";

                      $this->_synchronizationLine->setMessage($msg);
                      $this->_error = -1;                      
                }

               break;
           
           case 'Modif':
               
               $dossier = null;

              $m = $this->GetMedecin($medecin);
              $c = $this->GetClient($client);

              if( !$this->IsNullOrEmpty($m) && !$this->IsNullOrEmpty($c))
              {
                 // Modify the dossier
                 try{                
                     $dossier = $this->_em->getRepository('\Forthicime\DossierBundle\Entity\Dossier')->find($id);        

                     if(!$this->IsNullOrEmpty($dossier))                                    
                     { 
                       $dossier->setMedecin($m);                   
                       $dossier->setClient($c);
                       $dossier->setNumeric($numeric);
                       $dossier->setLibelle($libelle);   

                       $this->_em->flush();                  
                     } else {
                        $this->_synchronizationLine->setMessage("L'analyse avec l'ID ".$id." n'a pas été trouvé et ne peut donc être modifié");
                        $this->_error = -1;
                     }

                } catch(\Exception $e) {                  
                    $this->_logger->err("An _error occured during the save of the analysis ".$id);
                    $this->_logger->err($e->getMessage());
                } 
              }

              break;

           case 'Supprime':
               
               $dossier = null;

               try{
                  $dossier = $this->_em->getRepository('ForthicimeDossierBundle:Dossier')->find($id);

                  if(!$this->IsNullOrEmpty($dossier))                                    
                  { 
                     $this->_synchronizationLine->setMessage("Dossier ID: ".$dossier->getId()." Libelle: ".$dossier->getNom());
                     $this->_em->remove($dossier);                     
                     $this->_em->flush();  
                  } else {
                    $this->_synchronizationLine->setMessage("L'analyse avec l'ID ".$id." n'a pas été trouvé et ne peut donc être supprimé");
                    $this->_error = -1;
                  }
                }catch(\Exception $e){
                  $this->_logger->err("An _error occured during the delete of the Dossier ".$id);
                  $this->_logger->err($e->getMessage());
                  $this->_error = $e->getCode();
                } 

               break;    
           default:
              $this->_logger->err("Invalid  action ".$action);
              $this->_synchronizationLine->setMessage("L'opération fourni est invalide pour le dossier ".$id.". L'opération attendue est: Ajout, Modif ou Supprime et a obtenue: ".$action);               
              $this->_error = -1;

               break;
       }

        try {
            
            $this->_synchronizationLine->setCommand($action);
            $this->_synchronizationLine->setTableId($id);     
            $this->_synchronizationLine->setReturnCode($this->_error);                     
            $this->_em->persist($this->_synchronizationLine);
            $this->_em->flush();      
                 
        } catch (\Exception $e) {
            $this->_logger->err("An _error occured during the save of the _synchronizationLine.");
            $this->_logger->err($e->getMessage());
            $this->_error = $e->getCode();
        }

        $output->writeln($this->_error);
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
            $m = $this->_em->getRepository('ForthicimeMedecinBundle:Medecin')->find($medecin);

            if( $this->IsNullOrEmpty($m)){
               $this->_synchronizationLine->setMessage("Le medecin avec l'ID ".$medecin." n'a pas été trouvé");
               $this->_error = -1;  
            }
                                
       } catch (\Exception $e) {                  
            $this->_logger->err($e->getMessage());
       }  

       return $m;  
    }

    //Find a client by ID
    private function GetClient($client)
    {
      $c = null;

      try {        
            $c = $this->_em->getRepository('ForthicimeClientBundle:Client')->find($client);    

            if( $this->IsNullOrEmpty($c) ){
                $this->_synchronizationLine->setMessage("Client avec l'ID ".$client." n'a pas été trouvé");  
                $this->_error = -1;
            }
            
       } catch (\Exception $e) {                  
          $this->_logger->err($e->getMessage());
       }

       return $c;
    }
}