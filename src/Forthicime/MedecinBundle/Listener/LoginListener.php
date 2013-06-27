<?php
 
namespace Forthicime\MedecinBundle\Listener;
 
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
 
/**
 * Custom login listener.
 */
class LoginListener
{
	/** @var \Symfony\Component\Security\Core\SecurityContext */
	private $securityContext;
	
	/** @var \Doctrine\ORM\EntityManager */
	private $em;
	
	/**
	 * Constructor
	 * 
	 * @param SecurityContext $securityContext
	 * @param Doctrine        $doctrine
	 */
	public function __construct(SecurityContext $securityContext, Doctrine $doctrine)
	{
		$this->securityContext = $securityContext;
		$this->em              = $doctrine->getEntityManager();
	}
	
	/**
	 * Do the magic.
	 * 
	 * @param InteractiveLoginEvent $event
	 */
	public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
	{
		$user = $event->getAuthenticationToken()->getUser();

		if ($this->securityContext->isGranted('IS_AUTHENTICATED_FULLY') || 
			$this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			// user has just logged in
			$this->UpdateLastLoggedIn($user);
		}
		
		if ($this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			// user has logged in using remember_me cookie
			
		}
		
				
	}

	private function UpdateLastLoggedIn($usr)
	{
		if (!$this->securityContext->isGranted('ROLE_SUPER_ADMIN'))
		{
			// Update last logged in
	      	$login = new \Forthicime\MedecinBundle\Entity\LoginHistory();
	      	$login->setLogin(new \Datetime());
	      	$login->setMedecin($usr);

	      	$this->em->persist($login);      	
	      	$this->em->flush();
        }
	}
}