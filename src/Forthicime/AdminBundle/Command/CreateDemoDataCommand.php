<?php

namespace Forthicime\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Forthicime\MedecinBundle\Entity\LoginHistory;
use Forthicime\DossierBundle\Entity\AccessHistory;

class CreateDemoDataCommand extends ContainerAwareCommand
{
    protected function configure()
    {
            $this
            ->setName('CreateDemoData')
            ->setDescription('Creer des donnee aléatoire');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
            
      for ($i=0; $i < 10000; $i++) { 
    
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Create a new LoginHistory input
        $date = new \DateTime();
        do{
            $date = $this->randomDate("2011-01-31", "2013-12-31");
        }while(new \DateTime($date) > new \DateTime());

        $medecin_id = rand(1, 299);
        $medecin = $em->getRepository('ForthicimeMedecinBundle:Medecin')->find($medecin_id);       

        $login = new LoginHistory();         
        $login->setLogin(new \DateTime($date));
        $login->setMedecin($medecin);

        $em->persist($login);
        $em->flush(); 

        // Create a new acces history input
        $dossier_id = rand(1, 999);
        $dossier = $em->getRepository('ForthicimeDossierBundle:Dossier')->find($dossier_id);

        $date = new \DateTime();
        do{
            $date = $this->randomDate("2011-01-31", "2013-12-31");
        }while(new \DateTime($date) > new \DateTime());

        $access = new AccessHistory();
        $access->setDossier($dossier);
        $access->setAccess(new \DateTime($date));

        $em->persist($access);
        $em->flush(); 

      }
    }

    // Find a randomDate between $start_date and $end_date
    private function randomDate($start_date, $end_date)
    {
        // Convert to timetamps
        $min = strtotime($start_date);
        $max = strtotime($end_date);

        // Generate random number using above bounds
        $val = rand($min, $max);

        // Convert back to desired date format
        return date('Y-m-d H:i:s', $val);
    }
}