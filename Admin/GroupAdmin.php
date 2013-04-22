<?php

namespace Success\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class GroupAdmin extends Admin {
  
  public function getNewInstance()
  {
      $class = $this->getClass();

      return new $class('', array());
  }
  
  
  /**
   * Create and Edit
   * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
   *
   * @return void
   */
  protected function configureFormFields(FormMapper $formMapper) {
    $formMapper
              ->add('name')
              ->add('roles', 'success_security_roles', array(
                  'expanded' => true,
                  'multiple' => true,
                  'required' => false
              ))            
    ;
  }

  /**
   * List
   * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
   *
   * @return void
   */
  protected function configureListFields(ListMapper $listMapper) {
    $listMapper
            ->addIdentifier('name')
            ->add('roles')            
    ;
  }

}