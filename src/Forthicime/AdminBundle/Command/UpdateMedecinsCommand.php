<?php

namespace Forthicime\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Forthicime\MedecinBundle\Entity\Medecin;
use Forthicime\AdminBundle\Entity\SynchronizationLine;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class UpdateMedecinsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('UpdateMedecins')
            ->setDescription('Recupere les meta donnee des analyse et met a jour la base de donnee')
            ->addArgument('action', InputArgument::REQUIRED, 'Who do you want to greet?')
            ->addArgument('id', InputArgument::REQUIRED, 'Who do you want to greet?')
            ->addArgument('name', InputArgument::REQUIRED, 'Who do you want to greet?')
            ->addArgument('username', InputArgument::REQUIRED, 'Who do you want to greet?')
            ->addArgument('password', InputArgument::REQUIRED, 'Who do you want to greet?')
            ->addArgument('synchronizationID', InputArgument::REQUIRED, 'Who do you want to greet?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
       $id = $input->getArgument('id');
       $name = $input->getArgument('name');
       $username = $input->getArgument('username');
       $password = $input->getArgument('password');
       $action = $input->getArgument('action');
       $synchronizationID = $input->getArgument('synchronizationID');

       $logger = $this->getContainer()->get('logger');
       $error = 0;

       $logger->info("working on ".$name);

       $synchronizationLine = new SynchronizationLine();
       $em = $this->getContainer()->get('doctrine')->getManager();
       $synchronization = $em->getRepository('ForthicimeAdminBundle:Synchronization')->find($synchronizationID);

       if($this->IsNullOrEmpty($synchronization))
         throw new \Exception("La synchronization en cours ne peut etre récupéré", -201);

       $synchronizationLine->setSynchronization($synchronization);
       $synchronizationLine->setTableName("medecin");   

       switch ($action) {
           case 'Ajout':
              
               $medecin = new Medecin();

               try{                 

                 $medecin->setId($id);
                 $medecin->setNom($name);                 
                 $medecin->setIdentifiant($username);                 
                 $medecin->setPassword($password);
                 $em->persist($medecin);

                } catch(\Exception $e) {
                  
                  $synchronizationLine->setMessage($e->getMessage());
                  $error = $e->getCode(); 

                  $logger->err("An error occured while adding the following medecin: ".$nom);
                  $logger->err($e->getMessage());
                             
                } 

               break;
           
           case 'Modif':
              
               $medecin = null;

               try {

                 $medecin = $em->getRepository('ForthicimeMedecinBundle:Medecin')->find($id);
                 $medecin->setNom($name);
                 $medecin->setIdentifiant($username);
                 $medecin->setPassword($password);
                                 
               } catch(\Exception $e) {
                  
                  $synchronizationLine->setMessage($e->getMessage());
                  $error = $e->getCode(); 

                  $logger->err("An error occured while modifying the following medecin: ".$nom);
                  $logger->err($e->getMessage());
               } 

               break;

           case 'Supprime':
              
               $medecin = null;

               try {
                  $medecin = $em->getRepository('ForthicimeMedecinBundle:Medecin')->find($id);
                  $synchronizationLine->setMessage("Medecin ID: ".$medecin->getId()." Nom: ".$medecin->getNom());
                  $em->remove($medecin);                  
                } catch(\Exception $e) {

                  $synchronizationLine->setMessage($e->getMessage());
                  $error = $e->getCode(); 

                  $logger->err("An error occured while deleting the following medecin: ".$nom);
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