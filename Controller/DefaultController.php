<?php

namespace Success\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    public function indexAction()
    {
      return new RedirectResponse($this->container->get('router')->generate('fos_user_security_login'));
    }
}