<?php

namespace Forthicime\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Forthicime\MedecinBundle\Entity\Medecin;

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
            ->addArgument('password', InputArgument::REQUIRED, 'Who do you want to greet?');
           # ->addArgument('path', InputArgument::REQUIRED, 'Who do you want to greet?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
       $id = $input->getArgument('id');
       $name = $input->getArgument('name');
       $username = $input->getArgument('username');
       $password = $input->getArgument('password');
       $action = $input->getArgument('action');

       $em = $this->getContainer()->get('doctrine')->getManager();

       switch ($action) {
           case 'Ajout':
               $medecin = new Medecin();
               $medecin->setId($id);
               $medecin->setNom($name);
               $medecin->setIdentifiant($username);
               $medecin->setPassword($password);

               $em->persist($medecin);
               $em->flush();

               break;
           
           case 'Modif':
               $medecin = $em->getRepository('ForthicimeMedecinBundle:Medecin')->find($id);
               $medecin->setNom($name);
               $medecin->setIdentifiant($username);
               $medecin->setPassword($password);
               
               $em->flush();

               break;

           case 'Supprime':
               $medecin = $em->getRepository('ForthicimeMedecinBundle:Medecin')->find($id);

               $em->remove($medecin);
               $em->flush();               

               break;    
           default:
               
               break;
       }

      # unlink($file);
    }
}