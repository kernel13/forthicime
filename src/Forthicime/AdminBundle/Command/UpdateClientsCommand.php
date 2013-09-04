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
                   
                   $synchronizationLine->setReturnCode(0);
                   $synchronizationLine->setTableId($client->getId());            
               } catch(\Exception $e) {
                  $synchronizationLine->setReturnCode($e->getCode());
                  $synchronizationLine->setMessage($e->getMessage());
               } //finally {
                  $synchronizationLine->setCommand($action);

                  
                  //$serializedClient = $serializer->serialize($client, 'json');              
                  //$synchronizationLine->setColumnValues($serializedClient);                  

                  $em->persist($synchronizationLine);
                  $em->flush();
              // }

               break;
           
           case 'Modif':
                // Create a new synchronizationLine
               $client = null;
               $oldValue = "";

               try {
                  $client = $em->getRepository('ForthicimeClientBundle:Client')->find($id);
                 // $oldValue = $serializer->serialize($client, 'json');
                  $client->setNom($nom);
                  $client->setPrenom($prenom);
                  $client->setNomPrenom($nomPrenom);
               } catch(\Exception $e) {
                                        
                  $synchronizationLine->setReturnCode($e->getCode());
                  $synchronizationLine->setMessage($e->getMessage());
                                  
                } //finally {
                  $synchronizationLine->setCommand($action);   
                  $synchronizationLine->setTableId($client->getId());            
                  //$serializedClient = $serializer->serialize($client, 'json');                 
                  //$synchronizationLine->setColumnValues($serializedClient);
                 // $synchronizationLine->setOldColumnValues($oldValue);
                   
                  $em->persist($synchronizationLine);
                  $em->flush();
                //}

               break;

           case 'Supprime':

               $client = new Client();
               $serializedClient = "";
               try {
                      $client = $em->getRepository('ForthicimeClientBundle:Client')->find($id);
                      //$serializedClient = $serializer->serialize($client, 'json');  
                      $dossiers = $client->getDossiers();

                      foreach ($dossiers as $dossier) {
                   		   $client->removeDossiers($dossier);
                      }

                      $em->remove($client);                      
                      $synchronizationLine->setReturnCode(0);
                  } catch(\Exception $e) {

                    $synchronizationLine->setReturnCode($e->getCode());
                    $synchronizationLine->setMessage($e->getMessage());

                  } //finally {
                     $synchronizationLine->setCommand($action);
                     $synchronizationLine->setTableId($client->getId());            
                     //$synchronizationLine->setOldColumnValues($serializedClient);
                     $em->persist($synchronizationLine);
                     $em->flush();      
                 // }           

               break;    
           default:

               $client = new Client();
               $client->setId($id);
               $client->setNom($nom);
               $client->setPrenom($prenom);
               $client->setNomPrenom($nomPrenom);

               // Create a new synchronizationLine              
               $synchronizationLine->setCommand("InvalidCommand");
               $synchronizationLine->setReturnCode(-1); 
               //$serializedClient = $serializer->serialize($client, 'json');  
               //$synchronizationLine->setColumnValues(json_encode($serializedClient)); 
               $synchronizationLine->setMessage("L'opération fourni est invalide. Attendue: Ajout, Modif ou Supprime et a obtenue: ".$action);
               $synchronizationLine->setTableId($client->getId());            
               $em->persist($synchronizationLine);
               $em->flush();  

               break;
       }

       #unlink($file);
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