<?php

namespace Forthicime\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Forthicime\ClientBundle\Entity\Client;

class UpdateClientsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('UpdateClients')
            ->setDescription('Recupere les meta donnee des analyse et met a jour la base de donnee')
            ->addArgument('action', InputArgument::REQUIRED, 'Who do you want to greet?')
            ->addArgument('id', InputArgument::REQUIRED, 'Who do you want to greet?')
            ->addArgument('nom', InputArgument::REQUIRED, 'Who do you want to greet?')
            ->addArgument('prenom', InputArgument::REQUIRED, 'Who do you want to greet?')
            ->addArgument('nomPrenom', InputArgument::REQUIRED, 'Who do you want to greet?');
			    #  ->addArgument('path', InputArgument::REQUIRED, 'Who do you want to greet?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
       $id = $input->getArgument('id');
       $nom = $input->getArgument('nom');
       $prenom = $input->getArgument('prenom');
       $nomPrenom = $input->getArgument('nomPrenom');
       $action = $input->getArgument('action');

       $em = $this->getContainer()->get('doctrine')->getManager();

       switch ($action) {
           case 'Ajout':
               $client = new Client();
               $client->setId($id);
               $client->setNom($nom);
               $client->setPrenom($prenom);
               $client->setNomPrenom($nomPrenom);
       
               $em->persist($client);
               $em->flush();

               break;
           
           case 'Modif':
               $client = $em->getRepository('ForthicimeClientBundle:Client')->find($id);
               $client->setNom($nom);
               $client->setPrenom($prenom);
               $client->setNomPrenom($nomPrenom);
               
               $em->flush();

               break;

           case 'Supprime':
               $client = $em->getRepository('ForthicimeClientBundle:Client')->find($id);
               $dossiers = $client->getDossiers();

               foreach ($dossiers as $dossier) {
               		$client->removeDossiers($dossier);
               }

               $em->remove($client);
               $em->flush();               

               break;    
           default:
               
               break;
       }

       #unlink($file);
    }
} 