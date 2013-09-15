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


       $logger = $this->getContainer()->get('logger');
       $error = 0;

       $logger->info("working on ".$nom." ".$prenom);

       $synchronizationLine = new SynchronizationLine();
       //$serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new JsonEncoder()));

       $em = $this->getContainer()->get('doctrine')->getManager();

       $synchronization = $em->getRepository('ForthicimeAdminBundle:Synchronization')->find($synchronizationID);

       if($this->IsNullOrEmpty($synchronization))
         throw new \Exception("La synchronization en cours ne peut etre récupéré", -201);

       $synchronizationLine->setSynchronization($synchronization);
       $synchronizationLine->setTableName("client");

       switch ($action) {
           case 'Ajout':

              $client = new Client();

               try {                   
                   $client->setId($id);
                   $client->setNom($nom);
                   $client->setPrenom($prenom);
                   $client->setNomPrenom($nomPrenom);
                   $em->persist($client);   
                   $em->flush();                       
               } catch(\Exception $e) {

                  $synchronizationLine->setMessage($e->getMessage());

                  $logger->err("An error occured while adding the following client: ".$nom." ".$prenom);
                  $logger->err($e->getMessage());
                  $error = $e->getCode();                  
               }
              
               break;
           
           case 'Modif':
                // Create a new synchronizationLine
               $client = null;
               $oldValue = "";

               try {

                  $client = $em->getRepository('ForthicimeClientBundle:Client')->find($id);

                  if(!$this->IsNullOrEmpty($client))                                    
                  { 
                    $client->setNom($nom);
                    $client->setPrenom($prenom);
                    $client->setNomPrenom($nomPrenom);
                    $em->flush();  
                  } else {
                    $synchronizationLine->setMessage("Le client avec l'ID ".$id." n'a pas été trouvé et ne peut donc être modifié");
                    $error = -1;
                  }
               } catch(\Exception $e) {  

                  $synchronizationLine->setMessage($e->getMessage());
                  
                  $logger->err("An error occured while modifying the following client: ".$nom." ".$prenom);
                  $logger->err($e->getMessage());
                  $error = $e->getCode();
                } 

              
                  
               break;

           case 'Supprime':

               $client = new Client();
               $serializedClient = "";
               try {
                      $client = $em->getRepository('ForthicimeClientBundle:Client')->find($id);                  
                      if(!$this->IsNullOrEmpty($client))                                    
                      { 
                          $dossiers = $client->getDossiers();

                          foreach ($dossiers as $dossier) {
                       		   $client->removeDossiers($dossier);
                          }

                          $synchronizationLine->setMessage("Client ID: ".$client->getId()." Nom: ".$client->getNomPrenom());

                          $em->remove($client);      
                      } else {
                        $synchronizationLine->setMessage("Le client avec l'ID ".$id." n'a pas été trouvé et ne peut donc être supprimé");
                        $error = -1;
                      }                
                  } catch(\Exception $e) {

                    $synchronizationLine->setMessage($e->getMessage());
                    $logger->err("An error occured while deleting the following client: ".$nom." ".$prenom);
                    $logger->err($e->getMessage());
                    $error = $e->getCode();

                  } 
               break;    
           default:             

               // Create a new synchronizationLine                             
               $synchronizationLine->setMessage("L'opération fourni est invalide pour le client ".$nom." ".$prenom.". L'opération attendue est: Ajout, Modif ou Supprime et a obtenue: ".$action);               
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