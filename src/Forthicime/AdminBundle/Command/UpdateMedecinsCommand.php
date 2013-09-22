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

       $this->logger->info("working on ".$name);

       // Get current synchronizaiton
       $synchronization = $this->em->getRepository('ForthicimeAdminBundle:Synchronization')->find($synchronizationID);

       if($this->IsNullOrEmpty($synchronization))
         throw new \Exception("La synchronization en cours ne peut etre récupéré", -201);

       // Initialize synchronization line
       $this->synchronizationLine->setSynchronization($synchronization);
       $this->synchronizationLine->setTableName("medecin");   

       $this->logger->info("Action: ".$action);

       // Execute action
       switch ($action) {
           case 'Ajout':
              
               $medecin = new Medecin();

               try{                 

                 $m = $this->em->getRepository('ForthicimeMedecinBundle:Medecin')->find($medecin);

                 if( $this->IsNullOrEmpty($m) ){
                     $medecin->setId($id);
                     $medecin->setNom($name);                 
                     $medecin->setIdentifiant($username);                 
                     $medecin->setPassword($password);
                     $this->em->persist($medecin);
                     $this->em->flush();   
                 } else {
                    $this->synchronizationLine->setMessage("Le medecin avec l'ID ".$id." existe déjà");
                 }
                 
                } catch(\Exception $e) {
                   
                  $this->logger->err("An error occured while adding the following medecin: ".$nom);
                  $this->logger->err($e->getMessage());                
                  $this->error = $e->getCode();        

                } 

               break;
           
           case 'Modif':
              
               $medecin = null;

               try {

                 $medecin = $this->em->getRepository('ForthicimeMedecinBundle:Medecin')->find($id);

                if(!$this->IsNullOrEmpty($medecin))                                    
                { 
                    $medecin->setNom($name);
                    $medecin->setIdentifiant($username);
                    $medecin->setPassword($password);
                    $this->em->flush();          
                } else {
                  $this->synchronizationLine->setMessage("Le Medecin avec l'ID ".$id." n'a pas été trouvé et ne peut donc être modifié");
                  $this->error = -1;
                }
               } catch(\Exception $e) {
                  
                  $this->logger->err("An error occured while modifying the following medecin: ".$nom);
                  $this->logger->err($e->getMessage());
                  $this->error = $e->getCode();    

               } 

               break;

           case 'Supprime':
              
               $medecin = null;

               try {
                  $medecin = $this->em->getRepository('ForthicimeMedecinBundle:Medecin')->find($id);

                  if(!$this->IsNullOrEmpty($medecin))                                    
                  {
                      $this->synchronizationLine->setMessage("Medecin ID: ".$medecin->getId()." Nom: ".$medecin->getNom());
                      $this->em->remove($medecin); 
                      $this->em->flush();                   
                  } else {
                    $this->synchronizationLine->setMessage("Le medecin avec l'ID ".$id." n'a pas été trouvé et ne peut donc être supprimé");
                    $this->error = -1;
                  }
                } catch(\Exception $e) {

                  $this->logger->err("An error occured while deleting the following medecin: ".$nom);
                  $this->logger->err($e->getMessage());
                } 

               break;    
           default:
               $this->logger->err("Invalid  action ".$action);
               $this->synchronizationLine->setMessage("L'opération fourni est invalide pour le dossier ".$id.". L'opération attendue est: Ajout, Modif ou Supprime et a obtenue: ".$action);               
               $this->error = -1;

               break;
       }

       // Save synchronization line
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