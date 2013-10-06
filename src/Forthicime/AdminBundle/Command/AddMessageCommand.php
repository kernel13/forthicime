<?php

namespace Forthicime\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Forthicime\AdminBundle\Entity\Synchronization;
use Symfony\Component\HttpFoundation\Session\Session;
use Forthicime\AdminBundle\Entity\SynchronizationLine;

class AddMessageCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('AddMessage')
            ->setDescription('add a new message in synchronizationLine table')
            ->addArgument('action', InputArgument::REQUIRED, 'La commande qui sera effectué.')
            ->addArgument('tableName', InputArgument::REQUIRED, 'table name')
            ->addArgument('synchronizationID', InputArgument::REQUIRED, 'synchronization id')
            ->addArgument('message', InputArgument::REQUIRED, 'message to record');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {   

        // Get doctrine manager
        $em = $this->getContainer()->get('doctrine')->getManager();   
        $logger = $this->getContainer()->get('logger');

        try {

          $logger->info("Searching for current synchronization");
          // Get current synchornization
          $synchronizationID = $input->getArgument('synchronizationID');
          $synchronization = $em->getRepository('ForthicimeAdminBundle:Synchronization')
                                  ->find($synchronizationID);

          //$logger->info("Found synchronization ".$synchornization->getId());

          // Get action
          $action = $input->getArgument('action');

          // Get message
          $message = $input->getArgument('message');

          // Get table name
          $tableName = $input->getArgument('tableName');

          // Add a new synchronization line
          $logger->info("Add new synchornization line");
          $synchronizationLine = new SynchronizationLine();
          $synchronizationLine->setCommand($action);        
          $synchronizationLine->setReturnCode(-1);   
          $synchronizationLine->setMessage($message);
          $synchronizationLine->setSynchronization($synchronization);
          $synchronizationLine->setTableName($tableName);
          $em->persist($synchronizationLine);
          $em->flush();    
          $logger->info("synchronizationLine added");

        } catch(\Exception $e){          
          $logger->err($e->getMessage());          
        }
        
    }
}