<?php

namespace Success\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    public function indexAction()
    {
      $user = $this->get('security.context')->getToken()->getUser();
      if($user){
        return new RedirectResponse($this->container->get('router')->generate('sonata_admin_dashboard'));        
      }else{
        return new RedirectResponse($this->container->get('router')->generate('fos_user_security_login'));        
      }
    }
}
