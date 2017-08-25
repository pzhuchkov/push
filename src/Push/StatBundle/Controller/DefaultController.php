<?php

namespace Push\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('PushStatBundle:Default:index.html.twig');
    }
}
