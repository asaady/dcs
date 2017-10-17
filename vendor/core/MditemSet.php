<?php
namespace tzVendor;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/tz_const.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/tz_common.php");

class MditemSet extends Model {
    protected $description;
    
    public function __construct() {
        $arData = DataManager::getMainSettingsByName('Home');
        $this->id = $arData['id'];
        $this->name = $arData['name'];
        $this->synonym = $arData['synonym'];
        $this->description = $arData['description'];
        $this->version = time();
    }
    
    function get_data($mode='') {
      $arData = DataManager::getAllMDitems();
      return array(
          'id'=>$this->id,
          'name'=>$this->name,
          'synonym'=>$this->synonym,
          'description'=>$this->description,
          'version'=>$this->version,
          'ardata'=>$arData,
          'plist' => array(
                        'id'=>'ID',
                        'name'=>'NAME',
                        'synonym'=>'SYNONYM',
                        'type'=>'TYPE'
                    ),   
          'navlist' => array(
              $this->id=>$this->synonym
            ),
          'actionlist'=>array()
          );
    }
}

