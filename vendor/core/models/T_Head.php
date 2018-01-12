<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;
use Exception;

trait T_Head {
    function get_data($mode='') 
    {
        return array(
          'id'=>$this->id,
          'name'=>$this->name,
          'synonym'=>$this->synonym,
          'mdtype'=>$this->mditem->getname(),
          'mditem'=>$this->mditem->getid(),
          'mditemsynonym'=>$this->mditem->getsynonym(),
          'version'=>$this->version,
          'PSET' => $this->get_properties($mode),
          'navlist' => array(
              $this->mditem->getid() => $this->mditem->getsynonym(),
              $this->id => $this->synonym
            )
          );
    }
    function create($data) 
    {
        $entity = $this->get_item();
        $entity->set_data($data);
        return $entity->save_new();
    }
}
