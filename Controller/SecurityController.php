<?php

namespace Success\AdminBundle\Controller;

use Doctrine\ORM\EntityManager;

use FOS\UserBundle\Controller\SecurityController as BaseController;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * The security controller will handle the login procedure
 */
class SecurityController extends BaseController
{
    public function indexAction()
    {
      $user = $this->container->get('security.context')->getToken()->getUser();
      if($user){
        return new RedirectResponse($this->container->get('router')->generate('sonata_admin_dashboard'));        
      }else{
        return new RedirectResponse($this->container->get('router')->generate('usuario_admin_login'));        
      }
    }

    /**
     * Handle login action
     *
     * @return string
     */
    public function loginAction()
    {
        /* @var Request $request */
        $request = $this->container->get('request');
        /* @var EntityManager $em */
        $em = $this->container->get('doctrine')->getManager();
        $session = $request->getSession();

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        if ($error) {
            // @todo log $session->get(SecurityContext::LAST_USERNAME) . " tried to login to the cms but got error: " . $error     
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContext::LAST_USERNAME);

        $csrfToken = $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate');

        return $this->container->get('templating')->renderResponse(
            'SuccessAdminBundle:Security:login.html.' . $this->container->getParameter('fos_user.template.engine'),
            array(
                'last_username' => $lastUsername,
                'error'         => $error,
                'csrf_token'    => $csrfToken,
                'base_template' => $this->container->get('sonata.admin.pool')->getTemplate('layout'),
                'admin_pool'    => $this->container->get('sonata.admin.pool')                
            )
        );
    }
}
