<?php

namespace Forthicime\ClientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('ForthicimeClientBundle:Default:index.html.twig', array('name' => $name));
    }
}
