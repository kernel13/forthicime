<?php

namespace Forthicime\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Forthicime\MedecinBundle\Entity\Medecin;
use Forthicime\AdminBundle\Entity\SynchronizationLine;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

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

       $synchronizationLine = new SynchronizationLine();

       //$serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new JsonEncoder()));

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
                 $synchronizationLine->setReturnCode(0);   
                 $synchronizationLine->setTableId($medecin->getId());            
                } catch(Exception $e) {
                  $synchronizationLine->setReturnCode($e->getCode());
                  $synchronizationLine->setMessage($e->getMessage());
                } //finally {
                  $synchronizationLine->setCommand($action);
                 // $synchronizationLine->setColumnValues($serializer->serialize($medecin, 'json'));
                  $em->persist($synchronizationLine);
                  $em->flush();
                //}

               break;
           
           case 'Modif':
              
               $medecin = null;
               $oldValue = "";

               try {
                 $medecin = $em->getRepository('ForthicimeMedecinBundle:Medecin')->find($id);
                // $oldValue = $serializer->serialize($medecin, 'json');
                 $medecin->setNom($name);
                 $medecin->setIdentifiant($username);
                 $medecin->setPassword($password);
                 
                 $synchronizationLine->setReturnCode(0);
                 $synchronizationLine->setTableId($medecin->getId());            
               } catch(\Exception $e) {
                  $synchronizationLine->setReturnCode($e->getCode());
                  $synchronizationLine->setMessage($e->getMessage());
               } //finally {
                  $synchronizationLine->setCommand($action);
                //  $synchronizationLine->setColumnValues($serializer->serialize($medecin, 'json'));
                //  $synchronizationLine->setOldColumnValues($oldValue);
                  $em->persist($synchronizationLine);
                  $em->flush();
               //}

               break;

           case 'Supprime':
              
               $medecin = null;

               try {
                  $medecin = $em->getRepository('ForthicimeMedecinBundle:Medecin')->find($id);

                  $em->remove($medecin);
                  $synchronizationLine->setReturnCode(0);   
                  $synchronizationLine->setTableId($medecin->getId());                        
                } catch(\Exception $e) {
                  $synchronizationLine->setReturnCode($e->getCode());
                  $synchronizationLine->setMessage($e->getMessage());
                } //finally {
                  $synchronizationLine->setCommand($action);
                //  $synchronizationLine->setOldColumnValues($serializer->serialize($medecin, 'json'));
                  $em->persist($synchronizationLine);
                  $em->flush();
               // }

               break;    
           default:
               $medecin = new medecin();
               $medecin->setId($id);
               $medecin->setNom($name);
               $medecin->setIdentifiant($username);     

               # Create a new synchronizationLine
              
               $synchronizationLine->setCommand("InvalidCommand");
               $synchronizationLine->setReturnCode(-1); 
               $synchronizationLine->setTableId($medecin->getId());                          
              // $synchronizationLine->setColumnValues($serializer->serialize($medecin, 'json'));           
               $synchronizationLine->setMessage("L'opération fourni est invalide. Attendue: Ajout, Modif ou Supprime et a obtenue: ".$action);

               $em->persist($synchronizationLine);
               $em->flush();  

               break;
       }

      # unlink($file);
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