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

       $synchronizationLine = new SynchronizationLine();

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

               try {
                   $dossier->setId($id);

                   $m = $em->getRepository('ForthicimeMedecinBundle:Medecin')->find($medecin);
                   $dossier->setMedecin($m);
                   $c = $em->getRepository('ForthicimeClientBundle:Client')->find($client);               
                   $dossier->setClient($c);
                   $dossier->setNumeric($numeric);
                   $dossier->setLibelle($libelle);  

                   $em->persist($dossier);
                   $em->flush();
                   $synchronizationLine->setReturnCode(0);
                   $synchronizationLine->setTableId($dossier->getId());

                } catch(\Exception $e) {
                  $synchronizationLine->setReturnCode($e->getCode());
                  $synchronizationLine->setMessage($e->getMessage());
                } //finally {
                  $synchronizationLine->setCommand($action);                  
                  //$synchronizationLine->setColumnValues($serializer->serialize($dossier, 'json'));
                  $em->persist($synchronizationLine);
                  $em->flush();
                //}
               break;
           
           case 'Modif':
               
               $dossier = null;
               $oldValue = "";

               try{                
                   $dossier = $em->getRepository('\Forthicime\DossierBundle\Entity\Dossier')->find($id);
                   //$oldValue = $serializer->serialize($dossier, 'json');
                   $m = $em->getRepository('ForthicimeMedecinBundle:Medecin')->find($medecin);            
                   $dossier->setMedecin($m);
                   $c = $em->getRepository('ForthicimeClientBundle:Client')->find($client);
                   $dossier->setClient($c);
                   $dossier->setNumeric($numeric);
                   $dossier->setLibelle($libelle);                   
                   $synchronizationLine->setReturnCode(0);        
                   $synchronizationLine->setTableId($dossier->getId());       
              } catch(\Exception $e) {
                  $synchronizationLine->setReturnCode($e->getCode());
                  $synchronizationLine->setMessage($e->getMessage());
              } // finally {
                  $synchronizationLine->setCommand($action);
                  //$synchronizationLine->setColumnValues($serializer->serialize($dossier, 'json'));
                 // $synchronizationLine->setOldColumnValues($oldValue);

                  $em->persist($synchronizationLine);
                  $em->flush();
            //  }

               break;

           case 'Supprime':
               
               $dossier = null;

               try{
                  $dossier = $em->getRepository('ForthicimeDossierBundle:Dossier')->find($id);

                  $em->remove($dossier);
                  $synchronizationLine->setReturnCode(0);      
                  $synchronizationLine->setTableId($dossier->getId());                    
                }catch(\Exception $e){
                  $synchronizationLine->setReturnCode($e->getCode());
                  $synchronizationLine->setMessage($e->getMessage());
                } //finally {
                  $synchronizationLine->setCommand($action);
                //    $synchronizationLine->setOldColumnValues($serializer->serialize($dossier, 'json'));

                  $em->persist($synchronizationLine);
                  $em->flush();
               // }

               break;    
           default:
               $dossier = $em->getRepository('\Forthicime\DossierBundle\Entity\Dossier')->find($id);
               $m = $em->getRepository('ForthicimeMedecinBundle:Medecin')->find($medecin);            
               $dossier->setMedecin($m);
               $c = $em->getRepository('ForthicimeClientBundle:Client')->find($client);
               $dossier->setClient($c);
               $dossier->setNumeric($numeric);
               $dossier->setLibelle($libelle); 

               # Create a new synchronizationLine               
               $synchronizationLine->setCommand("InvalidCommand");
               $synchronizationLine->setReturnCode(-1);  
               //$synchronizationLine->setColumnValues($serializer->serialize($dossier, 'json'));           
               $synchronizationLine->setMessage("L'opération fourni est invalide. Attendue: Ajout, Modif ou Supprime et a obtenue: ".$action);
               $synchronizationLine->setTableId($dossier->getId());

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