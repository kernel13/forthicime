<?php

namespace Forthicime\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Forthicime\AdminBundle\Entity\Synchronization;
use Symfony\Component\HttpFoundation\Session\Session;

class StartSynchronizationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('StartSynchronization')
            ->setDescription('Crée une nouvelle entrée dans la table de synchronization');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {      

       $em = $this->getContainer()->get('doctrine')->getManager();
       $synchronization = new Synchronization();
       $synchronization->setStart(new \DateTime());

       $em->persist($synchronization);
       $em->flush();

       $session = new Session();
       $session->start();
       $session->set("SynchronizationID", $synchronization->getId());      
       $output->writeln("SynchronizationID:".$synchronization->getId());
    }
}