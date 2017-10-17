<?php
namespace tzVendor;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/tz_const.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/tz_common.php");

class PropTemplateSet {
    protected $id;     
    protected $name;     
    protected $synonym;     
    protected $mdtype;     
    protected $mdtypename;
    protected $mdtypedescription;
    protected $arData;     
    protected $version;
    
    
    public function __construct() {
       $this->id = 'proptemplate'; 
        $this->name = 'proptemplateset'; 
        $this->synonym = 'synonym'; 
        $this->mdtype = $arPar['type']; 
        $this->mdtypename = $arPar['mdtypename'];    
        $this->mdtypedescription = $arPar['mdtypedescription'];
        $this->arData = DataManager::getAllProps();
        $this->version = time();
    }
    
    function get_data() {
      $Cols = DataManager::getMDitemByName('Cols');
      return array(
          'id'=>$this->id,
          'name'=>$this->name,
          'synonym'=>$this->synonym,
          'version'=>$this->version,
          'mdtype'=>$this->mdtype,
          'mdtypename'=>$this->mdtypename,
          'mdtypedescription'=>$this->mdtypedescription,
          'ardata'=>$this->arData,
          'plist' => array(
                 'id'=>array('name'=>'id','synonym'=>'ID'),
                 'name'=>array('name'=>'name','synonym'=>'NAME'),
                 'synonym'=>array('name'=>'synonym','synonym'=>'SYNONYM'),
                 'type'=>array('name'=>'type','synonym'=>'TYPE')
             ),   
          'navlist' => array(
              $Cols['id']=>$Cols['synonym'],
              $this->id=>$this->synonym
          )
          );
    }
}

