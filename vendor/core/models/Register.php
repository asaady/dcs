<?php
namespace Dcs\Vendor\Core\Models;

//use dcs\vendor\core\Mditem;
use PDO;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

class Register extends Model {
    protected $mditem;
    
    public function __construct($id='') 
    {
	if ($id=='') 
        {
            die("empty id register");
	}
        $arPar = self::getMDCollection($id);
        if ($arPar)
        {
            $this->id = $id; 
            $this->name = $arPar['name']; 
            $this->synonym = $arPar['synonym']; 
            $this->mditem = new MDitem($arPar['mditem']);
        }    
        else 
        {
            $this->id = ''; 
            $this->name = ''; 
            $this->synonym = ''; 
            $this->mditem = new MDitem($id);
        }
        $this->version = time();
    }
    function getmditem() 
    {
      return $this->mditem;
    }
    function get_data($mode='') 
    {
        $plist = MdpropertySet::getMDProperties($this->id,$mode," WHERE mp.mdid = :mdid ",true);
        if ($this->id=='')
        {
            $navlist = array($this->mditem->getid()=>$this->mditem->getsynonym(),'new'=>'Новый');
        }   
        else
        {
            $navlist = array($this->mditem->getid()=>$this->mditem->getsynonym(),$this->id=>$this->synonym);
        }    
        return array(
            'id'=>$this->id,
            'name'=>$this->name,
            'synonym'=>$this->synonym,
            'version'=>$this->version,
            'mditem'=>$this->mditem->getid(),
            'mdtypename'=>$this->mditem->getname(),
            'mdtypedescription'=>$this->mditem->getsynonym(),
            'PLIST' => $plist,   
            'navlist' => $navlist
            );
    }
    function create($data) {
      $reg = new Register($this->id);
      return $reg->create($data);
    }
    public static function getAllRegs() 
    {
	$sql = "SELECT md.id, md.name, md.synonym FROM \"MDTable\" as md 		    
                    INNER JOIN \"CTable\" as tp
                    ON md.mditem = tp.id
                    and tp.name='Regs'";
        $sth = DataManager::dm_query($sql);        
        $objs = array();
        while($row = $sth->fetch(PDO::FETCH_ASSOC)) 
        {
            $objs[$row['id']] = $row;
        }
        return $objs;
    }
    public static function getMDReg($id) 
    {
	$sql = "SELECT md.id, md.name, md.synonym, tp.id as mditem, tp.name as mdtypename FROM \"MDTable\" as md 		    
                    INNER JOIN \"CTable\" as tp
                    ON md.mditem = tp.id
                    and (tp.name='Regs')
                WHERE md.id=:id";
        $sth = DataManager::dm_query($sql,array('id'=>$id));        
        $res = $sth->fetch(PDO::FETCH_ASSOC);
        return $res;
    }
}

