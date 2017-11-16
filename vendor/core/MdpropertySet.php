<?php
namespace tzVendor;
use PDO;
use tzVendor\Mdentity;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/tz_const.php");

class MdpropertySet extends Model 
{
    protected $mdentity;     
    
    public function __construct($mdid='') 
    {
	if ($mdid=='') 
        {
            throw new Exception("class.MDPropertySet constructor: mdid is empty");
	}
        $this->id = $mdid; 
        $this->mdentity = new Mdentity($mdid);
        $this->name = $this->mdentity->getname()."propertyset"; 
        $this->synonym = $this->mdentity->getsynonym()." (Список реквизитов)"; 
        $this->version = time();
    }
    
    function gettype() 
    {
        return $this->mdentity->gettype();
    }
    function gettypename() 
    {
        return $this->mdentity->gettypename();
    }
    function gettypedescription() 
    {
        return $this->mdentity->gettypedescription();
    }
    
    public static function getMDProperties($mdid, $mode, $strwhere, $byid=false) 
    {
        $mdentity = new Mdentity($mdid);
        if (($mdentity->getmdtypename()=='Cols')||($mdentity->getmdtypename()=='Comps'))
        {
            $objs = self::CProperties($mdid,$mode,$strwhere,$byid);
        }   
        else 
        {
            $objs = self::MDProperties($mdid,$mode,$strwhere,$byid);
        }
        return $objs;
    }
    public static function MDProperties($mdid,$mode,$strwhere,$byid=false) 
    {
        $sql = DataManager::get_select_properties($strwhere);//" WHERE mp.mdid = :mdid AND mp.rank>0 AND mp.ranktoset>0 ");
	$res = DataManager::dm_query($sql,array('mdid'=>$mdid));
        $plist = $res->fetchAll(PDO::FETCH_ASSOC);
        return self::getPropList($plist,$mode,$byid);
    }
    public static function CProperties($mdid, $mode,$strwhere,$byid=false) 
    {
        $sql = DataManager::get_select_cproperties($strwhere);	
	$res = DataManager::dm_query($sql,array('mdid'=>$mdid));
        $plist = $res->fetchAll(PDO::FETCH_ASSOC);
        return self::getCPropList($plist,$mode,$byid);
    }    
    function get_data($mode='') 
    {
        $plist = array(
          'id'=>array('name'=>'id','synonym'=>'ID','class'=>'active'),
          'name'=>array('name'=>'name','synonym'=>'NAME','class'=>'active'),
          'synonym'=>array('name'=>'synonym','synonym'=>'SYNONYM','class'=>'active')
        );
        return array(
          'id'=>$this->id,
          'name'=>$this->name,
          'synonym'=>$this->synonym,
          'version'=>$this->version,
          'PLIST' => $plist,   
          'navlist' => array(
              $this->id=>$this->synonym
            )
          );
    }
    
    public static function getCPropList($plist,$edit_mode,$byid=false)
    {    
        $objs = array();
        if ($edit_mode == 'EDIT')
        {    
            $row = array('id'=>'id','name'=> 'id','synonym'=> 'ID','class'=>'readonly','valmdid'=>'','valmdtypename'=>'str','type'=>'str','rank'=>1);
        }
        else
        {
            $row = array('id'=>'id','name'=> 'id','synonym'=> 'ID','class'=>'hidden','valmdid'=>'','valmdtypename'=>'str','type'=>'str','rank'=>1);
        }    
        $rowname = array('id'=>'name','name'=>'name','synonym'=>'NAME','class'=>'active','valmdid'=>'','valmdtypename'=>'str','type'=>'str','rank'=>2);
        $rowsyn = array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM','class'=>'active','valmdid'=>'','valmdtypename'=>'str','type'=>'str','rank'=>3);
        if ($byid)
        {    
            $objs['id'] = $row;
            $objs['name'] = $rowname;
            $objs['synonym'] = $rowsyn;
        }
        else
        {
            $objs[] = $row;
            $objs[] = $rowname;
            $objs[] = $rowsyn;
        }    
        
        foreach($plist as $row) 
        {
            $rid = $row['id'];
            $row = array('id'=>$rid,'name'=> $row['name'],'synonym'=> $row['synonym'],'class'=>'active','valmdid'=>$row['valmdid'],'valmdtypename'=>$row['valmdtypename'],'type'=>$row['type'],'rank'=>$row['rank']);
            if ($byid)
            {    
                $objs[$rid] = $row;
            }
            else 
            {
                $objs[] = $row;
            }
        }
        return $objs;
    }    
    public static function getPropList($plist,$mode,$byid=false)
    {    
        $objs = array();
        $class = 'hidden';
        if ($mode==='CONFIG')
        {    
            $class = 'readonly';
        }
        $row = array('id'=>'id','name'=> 'id','synonym'=> 'ID','class'=>$class,'valmdid'=>'','valmdtypename'=>'str','type'=>'str','rank'=>1,'ranktostring'=>0,'isedate'=>false,'isenumber'=>false);
        if ($byid)
        {    
            $objs['id'] = $row;
        }
        else
        {
            $objs[] = $row;
        }    
        foreach($plist as $row) 
        {
            $rid = $row['id'];
            $class = 'active';
            if (strtolower($row['name'])==='activity')
            {
                if (!User::isAdmin())
                {    
                    $class = 'hidden';
                }    
            }    
            $prow =  array('id'=> $rid,
                            'name'=> $row['name'],
                            'synonym'=> $row['synonym'],
                            'class'=>$class,
                            'valmdid'=>$row['valmdid'],
                            'valmdtypename'=>$row['valmdtypename'],
                            'type'=>$row['type'],
                            'isedate'=>$row['isedate'],
                            'isenumber'=>$row['isenumber'],
                            'rank'=>$row['rank'],
                            'name_propid'=>$row['name_propid'],
                            'propid'=>$row['propid'],
                            'ranktostring'=>$row['ranktostring']
                            );
            if ($byid)
            {    
                $objs[$rid] = $prow;
            }
            else
            {
                $objs[] = $prow;
            }    
        }
        return $objs;
    }    
    public static function CProperties_Id($mdid, $mode,$strwhere) 
    {
        return self::CProperties($mdid, $mode,$strwhere,true); 
    }
    public static function getCPropList_id($plist,$mode)
    {
        return self::getCPropList($plist,$mode,true);
    }        
    public static function getPropList_id($plist,$mode)
    {    
        return self::getPropList($plist,$mode,true);
    }    
}

