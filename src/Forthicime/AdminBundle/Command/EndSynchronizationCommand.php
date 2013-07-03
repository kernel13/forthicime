<?php

namespace Forthicime\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Forthicime\AdminBundle\Entity\Synchronization;

class EndSynchronizationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('EndSynchronization')
            ->setDescription('termine une synchronization end cours')
            ->addArgument('synchronizationID', InputArgument::REQUIRED, 'identifant de la synchronization en cours');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {      
       $synchronizationID = $input->getArgument('synchronizationID');

       // Get manager
       $em = $this->getContainer()->get('doctrine')->getManager();

       $syncRepo = $em->getRepository('ForthicimeAdminBundle:Synchronization');
       $synchronization = $em->getRepository('ForthicimeAdminBundle:Synchronization')->find($synchronizationID);

       

        $synchronizationLine     = $em->getRepository('ForthicimeAdminBundle:synchronizationLine');

        $nb_success      =  $synchronizationLine->createQueryBuilder("t")
                                       ->select("COUNT(t.id)")
                                       ->where("t.synchronization = :tid")
                                       ->andWhere("t.returnCode = 0")
                                       ->setParameter("tid", $synchronization->getId())
                                       ->getQuery()
                                       ->getSingleScalarResult();

        $nb_failure     =  $synchronizationLine->createQueryBuilder("t")
                                       ->select("COUNT(t.id)")
                                       ->where("t.synchronization = :tid")
                                       ->andWhere("t.returnCode <> 0")
                                       ->setParameter("tid", $synchronization->getId())
                                       ->getQuery()
                                       ->getSingleScalarResult();

        $synchronization->setEnd(new \DateTime());
        $synchronization->setNbSuccess($nb_success);
        $synchronization->setNbFailure($nb_failure);

       $em->persist($synchronization);
       $em->flush();
    }
}
