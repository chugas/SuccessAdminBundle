<?php

namespace Success\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class UserAdmin extends Admin {
  
  /**
   * Create and Edit
   * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
   *
   * @return void
   */
  protected function configureFormFields(FormMapper $formMapper) {
      $formMapper
          ->with('General')
              ->add('username')
              ->add('email')
              ->add('plainPassword', 'text', array('required' => false, 'label' => 'Nuevo Password'))
              //->add('locked', null, array('required' => false))
              //->add('expired', null, array('required' => false))
              ->add('enabled', null, array('required' => false))
              //->add('credentialsExpired', null, array('required' => false))              
          ->end()
          ->with('Groups')
              ->add('groups', 'sonata_type_model', array('required' => false, 'expanded' => true, 'multiple' => true))
          ->end()
      ;

      if (!$this->getSubject()->hasRole('ROLE_ADMIN')) {
          $formMapper
              ->with('Management')
                  ->add('roles', 'success_security_roles', array(
                      'expanded' => true,
                      'multiple' => true,
                      'required' => false
                  ))
              ->end()
          ;
      }

/*      $formMapper
          ->with('Security')
              ->add('token', null, array('required' => false))
              ->add('twoStepVerificationCode', null, array('required' => false))
          ->end()
      ;*/
  }

  /**
   * List
   * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
   *
   * @return void
   */
  protected function configureListFields(ListMapper $listMapper) {
      $listMapper
          ->addIdentifier('username')
          ->add('email')
          ->add('groups')
          ->add('enabled', null, array('editable' => true))
          ->add('locked', null, array('editable' => true))
          ->add('createdAt')
      ;

      if ($this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
          $listMapper
              ->add('impersonating', 'string', array('template' => 'SonataUserBundle:Admin:Field/impersonating.html.twig'))
          ;
      }
  }
  
  protected function configureDatagridFilters(DatagridMapper $filterMapper)
  {
      $filterMapper
          ->add('id')
          ->add('username')
          ->add('locked')
          ->add('email')
          ->add('groups')
      ;
  }  
  

}