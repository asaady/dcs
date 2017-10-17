<?php
namespace tzVendor;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/tz_const.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/tz_common.php");

class ActionSet {
    protected $id;     
    protected $name;     
    protected $synonym;     
    protected $icon;     
    protected $arData;     
    protected $version;     
    
    public function __construct() {
        $this->id = 'action'; 
        $this->name = 'actionset'; 
        $this->synonym = 'Действия'; 
        $this->dbtable = 'Actions'; 
        $this->arData = DataManager::TableSelect($this->dbtable);
        $this->version = time();
    }
    
    function get_data() {
      $Cols = DataManager::getMDitemByName('Cols');
      return array(
          'id'=>$this->id,
          'name'=>$this->name,
          'synonym'=>$this->synonym,
          'version'=>$this->version,
          'ardata'=>$this->arData,
          'plist' => array(
              'id'=>array('name'=>'id','synonym'=>'ID'),
              'name'=>array('name'=>'name','synonym'=>'NAME'),
              'synonym'=>array('name'=>'synonym','synonym'=>'SYNONYM'),
              'icon'=>array('name'=>'icon','synonym'=>'ICON'),
          ),   
          'navlist' => array(
              $Cols['id']=>$Cols['synonym'],
              $this->id=>$this->synonym
            ),
          'actionlist'=>array(
              array('id'=>'b0b456b5-ecc0-4057-8a95-27428d04245a','name'=>'create','synonym'=>'Новый', 'icon'=>'add_box')
          )
          );
    }
}

