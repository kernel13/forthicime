<?php

namespace Forthicime\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Forthicime\MedecinBundle\Entity\Medecin;
use Forthicime\ClientBundle\Entity\Client;
use Forthicime\DossierBundle\Entity\Dossier;

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
            ->addArgument('libelle', InputArgument::REQUIRED, 'Who do you want to greet?');
            #->addArgument('path', InputArgument::REQUIRED, 'Who do you want to greet?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
       $id = $input->getArgument('id');
       $medecin = $input->getArgument('medecin');
       $client = $input->getArgument('client');
       $numeric = $input->getArgument('numeric');
       $libelle = $input->getArgument('libelle');
       $action = $input->getArgument('action');

       $em = $this->getContainer()->get('doctrine')->getManager();

       switch ($action) {
           case 'Ajout':
               $dossier = new Dossier();

               $dossier->setId($id);

               $m = $em->getRepository('ForthicimeMedecinBundle:Medecin')->find($medecin);
               $dossier->setMedecin($m);

               $c = $em->getRepository('ForthicimeClientBundle:Client')->find($client);               
               $dossier->setClient($c);

               $dossier->setNumeric($numeric);

               $dossier->setLibelle($libelle);  

               $em->persist($dossier);
               $em->flush();

               break;
           
           case 'Modif':
               $dossier = $em->getRepository('\Forthicime\DossierBundle\Entity\Dossier')->find($id);

               $m = $em->getRepository('ForthicimeMedecinBundle:Medecin')->find($medecin);            
               $dossier->setMedecin($m);

               $c = $em->getRepository('ForthicimeClientBundle:Client')->find($client);
               $dossier->setClient($c);

               $dossier->setNumeric($numeric);
               $dossier->setLibelle($libelle);
               
               $em->flush();

               break;

           case 'Supprime':
               $dossier = $em->getRepository('ForthicimeDossierBundle:Dossier')->find($id);

               $em->remove($dossier);
               $em->flush();               

               break;    
           default:
               
               break;
       }

       #unlink($file);
    }
}