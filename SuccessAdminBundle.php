<?php

namespace Success\AdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SuccessAdminBundle extends Bundle {

  public function getParent() {
    return 'FOSUserBundle';
  }

}
