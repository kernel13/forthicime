<?php

namespace Forthicime\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Forthicime\ClientBundle\Entity\Client;
use Forthicime\AdminBundle\Entity\SynchronizationLine;


class UpdateClientsCommand extends ContainerAwareCommand
{
    private $synchronizationLine;
    private $em;
    private $logger;
    private $error;

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

			  $this->synchronizationLine = new SynchronizationLine();
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->logger = $this->getContainer()->get('logger');
        $this->error = 0;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
       $id = $input->getArgument('id');
       $nom = $input->getArgument('nom');
       $prenom = $input->getArgument('prenom');
       $nomPrenom = $input->getArgument('nomPrenom');
       $action = $input->getArgument('action');
       $synchronizationID = $input->getArgument('synchronizationID');

       $this->logger->info("working on ".$nom." ".$prenom);

       // Find current synchronization
       $synchronization = $this->em->getRepository('ForthicimeAdminBundle:Synchronization')->find($synchronizationID);

       if($this->IsNullOrEmpty($synchronization))
         throw new \Exception("La synchronization en cours ne peut etre récupéré", -201);

       // Initialize synchronization line
       $this->synchronizationLine->setSynchronization($synchronization);
       $this->synchronizationLine->setTableName("client");

       $this->logger->info("Action: ".$action);

       // Run action
       switch ($action) {
           case 'Ajout':

              $client = new Client();

               try {      

                   $c = $this->em->getRepository('ForthicimeClientBundle:Client')->find($client);    
                  
                  if( $this->IsNullOrEmpty($c) ){
                   $client->setId($id);
                   $client->setNom($nom);
                   $client->setPrenom($prenom);
                   $client->setNomPrenom($nomPrenom);
                   $this->em->persist($client);   
                   $this->em->flush(); 
                 } else {
                    $this->synchronizationLine->setMessage("Le patient avec l'ID ".$id." existe déjà");
                 }
                                          
               } catch(\Exception $e) {
                  $this->logger->err("An error occured while adding the following client: ".$nom." ".$prenom);
                  $this->logger->err($e->getMessage());
                  $this->error = $e->getCode();                  
               }
              
               break;
           
           case 'Modif':
                // Create a new synchronizationLine
               $client = null;
               $oldValue = "";

               try {

                  $client = $this->em->getRepository('ForthicimeClientBundle:Client')->find($id);

                  if(!$this->IsNullOrEmpty($client))                                    
                  { 
                    $client->setNom($nom);
                    $client->setPrenom($prenom);
                    $client->setNomPrenom($nomPrenom);
                    $this->em->flush();  
                  } else {
                    $this->synchronizationLine->setMessage("Le client avec l'ID ".$id." n'a pas été trouvé et ne peut donc être modifié");
                    $this->error = -1;
                  }
               } catch(\Exception $e) {  

                  $this->logger->err("An error occured while modifying the following client: ".$nom." ".$prenom);
                  $this->logger->err($e->getMessage());
                  $this->error = $e->getCode();
                } 
       
               break;

           case 'Supprime':

               $client = new Client();
               $serializedClient = "";
               try {
                      $client = $this->em->getRepository('ForthicimeClientBundle:Client')->find($id);                  
                      if(!$this->IsNullOrEmpty($client))                                    
                      { 
                          $dossiers = $client->getDossiers();

                          foreach ($dossiers as $dossier) {
                       		   $client->removeDossiers($dossier);
                          }

                          $this->synchronizationLine->setMessage("Client ID: ".$client->getId()." Nom: ".$client->getNomPrenom());

                          $this->em->remove($client);      
                      } else {
                        $this->synchronizationLine->setMessage("Le client avec l'ID ".$id." n'a pas été trouvé et ne peut donc être supprimé");
                        $this->error = -1;
                      }                
                  } catch(\Exception $e) {

                    $this->logger->err("An error occured while deleting the following client: ".$nom." ".$prenom);
                    $this->logger->err($e->getMessage());
                    $this->error = $e->getCode();

                  } 
               break;    
           default:             
               $this->logger->err("Invalid  action ".$action);
               // Create a new synchronizationLine                             
               $this->synchronizationLine->setMessage("L'opération fourni est invalide pour le client ".$nom." ".$prenom.". L'opération attendue est: Ajout, Modif ou Supprime et a obtenue: ".$action);               
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
} 