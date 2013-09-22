<?php

namespace Forthicime\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Forthicime\ClientBundle\Entity\Client;
use Forthicime\AdminBundle\Entity\SynchronizationLine;
use Symfony\Component\HttpKernel\Log\LoggerInterface;


class UpdateClientsCommand extends ContainerAwareCommand
{
    private $_synchronizationLine;
    private $_em;
    private $_logger;
    private $_error;

    protected function configure()
    {
        $this
            ->setName('UpdateClients')
            ->setDescription('Recupere les meta donnee des analyse et met a jour la base de donnee')
            ->addArgument('action', InputArgument::REQUIRED, 'La commande qui sera effectué.')
            ->addArgument('id', InputArgument::REQUIRED, 'Id du client')
            ->addArgument('nom', InputArgument::REQUIRED, 'Nom du client')
            ->addArgument('prenom', InputArgument::REQUIRED, 'Prenom du client')
            ->addArgument('nomPrenom', InputArgument::REQUIRED, 'nom prenom du client')
            ->addArgument('synchronizationID', InputArgument::REQUIRED, 'identifant de la synchronization en cours');		
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
       $id = $input->getArgument('id');
       $nom = $input->getArgument('nom');
       $prenom = $input->getArgument('prenom');
       $nomPrenom = $input->getArgument('nomPrenom');
       $action = $input->getArgument('action');
       $synchronizationID = $input->getArgument('synchronizationID');


       $this->_synchronizationLine = new SynchronizationLine();
       $this->_em = $this->getContainer()->get('doctrine')->getManager();
       $this->_logger = $this->getContainer()->get('logger');
       $this->_error = 0;

       $this->_logger->info("working on ".$nom." ".$prenom);

       // Find current synchronization
       $synchronization = $this->_em->getRepository('ForthicimeAdminBundle:Synchronization')->find($synchronizationID);

       if($this->IsNullOrEmpty($synchronization))
         throw new \Exception("La synchronization en cours ne peut etre récupéré", -201);

       // Initialize synchronization line
       $this->_synchronizationLine->setSynchronization($synchronization);
       $this->_synchronizationLine->setTableName("client");

       $this->_logger->info("Action: ".$action);

       // Run action
       switch ($action) {
           case 'Ajout':

              $client = new Client();

               try {      

                   $c = $this->_em->getRepository('ForthicimeClientBundle:Client')->find($client);    
                  
                  if( $this->IsNullOrEmpty($c) ){
                   $client->setId($id);
                   $client->setNom($nom);
                   $client->setPrenom($prenom);
                   $client->setNomPrenom($nomPrenom);
                   $this->_em->persist($client);   
                   $this->_em->flush(); 
                 } else {
                    $this->_synchronizationLine->setMessage("Le patient avec l'ID ".$id." existe déjà");
                 }
                                          
               } catch(\Exception $e) {
                  $this->_logger->err("An _error occured while adding the following client: ".$nom." ".$prenom);
                  $this->_logger->err($e->getMessage());
                  $this->_error = $e->getCode();                  
               }
              
               break;
           
           case 'Modif':
                // Create a new _synchronizationLine
               $client = null;
               $oldValue = "";

               try {

                  $client = $this->_em->getRepository('ForthicimeClientBundle:Client')->find($id);

                  if(!$this->IsNullOrEmpty($client))                                    
                  { 
                    $client->setNom($nom);
                    $client->setPrenom($prenom);
                    $client->setNomPrenom($nomPrenom);
                    $this->_em->flush();  
                  } else {
                    $this->_synchronizationLine->setMessage("Le client avec l'ID ".$id." n'a pas été trouvé et ne peut donc être modifié");
                    $this->_error = -1;
                  }
               } catch(\Exception $e) {  

                  $this->_logger->err("An _error occured while modifying the following client: ".$nom." ".$prenom);
                  $this->_logger->err($e->getMessage());
                  $this->_error = $e->getCode();
                } 
       
               break;

           case 'Supprime':

               $client = new Client();
               $serializedClient = "";
               try {
                      $client = $this->_em->getRepository('ForthicimeClientBundle:Client')->find($id);                  
                      if(!$this->IsNullOrEmpty($client))                                    
                      { 
                          $dossiers = $client->getDossiers();

                          foreach ($dossiers as $dossier) {
                       		   $client->removeDossiers($dossier);
                          }

                          $this->_synchronizationLine->setMessage("Client ID: ".$client->getId()." Nom: ".$client->getNomPrenom());

                          $this->_em->remove($client);      
                      } else {
                        $this->_synchronizationLine->setMessage("Le client avec l'ID ".$id." n'a pas été trouvé et ne peut donc être supprimé");
                        $this->_error = -1;
                      }                
                  } catch(\Exception $e) {

                    $this->_logger->err("An _error occured while deleting the following client: ".$nom." ".$prenom);
                    $this->_logger->err($e->getMessage());
                    $this->_error = $e->getCode();

                  } 
               break;    
           default:             
               $this->_logger->err("Invalid  action ".$action);
               // Create a new _synchronizationLine                             
               $this->_synchronizationLine->setMessage("L'opération fourni est invalide pour le client ".$nom." ".$prenom.". L'opération attendue est: Ajout, Modif ou Supprime et a obtenue: ".$action);               
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
} 