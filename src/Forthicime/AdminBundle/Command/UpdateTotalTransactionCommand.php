<?php

namespace Forthicime\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Forthicime\MedecinBundle\Entity\Synchronization;
use Symfony\Component\HttpFoundation\Session\Session;
use Forthicime\AdminBundle\Entity\transaction;

class UpdateTotalTransactionCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('UpdateTotalTransaction')
            ->setDescription('Met á jour le le nombre de transaction qui sera effectué pour cette synchronization')
            ->addArgument('nbTransaction', InputArgument::REQUIRED, 'Number of transaction')
            ->addArgument('synchronizationID', InputArgument::REQUIRED, 'Who do you want to greet?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {      
       // Get argument number of transaction	
       $nbTransaction = $input->getArgument('nbTransaction');
       $synchronizationID = $input->getArgument('synchronizationID');

       // Get manager
       $em = $this->getContainer()->get('doctrine')->getManager();
            
       $synchronization = $em->getRepository('ForthicimeAdminBundle:Synchronization')->find($synchronizationID);

       if($this->IsNullOrEmpty($synchronization))
         throw new \Exception("La synchronization en cours ne peut etre récupéré", -201);

       //Set number of transaction     
       $synchronization->setNbTransaction($nbTransaction);

       $em->flush();
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