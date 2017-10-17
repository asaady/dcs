<?php
namespace tzVendor;
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/tz_const.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/tz_common.php");
use PDO;

class MCollectionSet extends MD {
    function get_data() 
    {
        $arData = CollectionSet::getAllCollections();
        $plist = array(
          'id'=>array('name'=>'id','synonym'=>'ID'),
          'name'=>array('name'=>'name','synonym'=>'NAME'),
          'synonym'=>array('name'=>'synonym','synonym'=>'SYNONYM'),
          'dbtable'=>array('name'=>'dbtable','synonym'=>'DB TABLE')
        );
        return array(
          'id'=>$this->id,
          'name'=>$this->name,
          'synonym'=>$this->synonym,
          'version'=>$this->version,
          'mdtype'=>$this->mditem->getname(),
          'mdtypename'=>$this->mditem->getsynonym(),
          'ardata'=>$arData,
          'plist' => $plist,   
          'navlist' => array(
              $this->id=>$this->synonym
            )
          );
    }
    public static function getAllColbyItem($mditem) {
	$sql = "SELECT id, name, synonym FROM \"MDCollections\" AS mdt WHERE mdt.mditem= :mditem";
        $sth = DataManager::dm_query($sql,array('mditem'=>$mditem));        
        $objs = array();
        while($row = $sth->fetch(PDO::FETCH_ASSOC)) 
        {
            $objs[$row['id']] = $row;
        }
        return $objs;
    }
    
    public function create($data)
    {
        $objs = array();
        if ($this->name=='Cols')
        {
            if (!array_key_exists('dbtable', $data))
            {
                return $objs;
            }    
            if (!DataManager::isTableExist($data['dbtable']))
            {
                return $objs;
            }    
            
            $props= array('name','synonym','dbtable');
            $sql='';
            $fname ='';
            $fval = '';
            foreach ($props as $prop)
            {    
                if (array_key_exists($prop, $data))
                {
                    $fname .=", $prop";
                    $fval .=", :$prop";
                    $params[$prop]=$data[$prop]['name'];
                }    
            }
            $fname = substr($fname,1);
            $fval  = substr($fval,1);
            $objs['status']='NONE';
            if ($fname!='')
            {
                $objs['status']='OK';
                $dbtable = 'MDCollections';
                $sql ="INSERT INTO \"$dbtable\" ($fname) VALUES ($fval) RETURNING \"id\"";
                $res = DataManager::dm_query($sql,$params);
                if(!$res) 
                {
                    $objs['status']='ERROR';
                    $objs['msg']=$sql;
                }
            }
        }
        elseif ($this->name=='Vals')
        {
            $props= array('name','synonym');
            $sql='';
            $fname ='mditem';
            $fval = "'".$this->mditem->getid()."'";
            foreach ($props as $prop)
            {    
                if (array_key_exists($prop, $data))
                {
                    $fname .=", $prop";
                    $fval .=", :$prop";
                    $params[$prop]=$data[$prop]['name'];
                }    
            }
            $objs['status']='NONE';
            if ($fname!='')
            {
                $objs['status']='OK';
                $dbtable = 'MDTable';
                $sql ="INSERT INTO \"$dbtable\" ($fname) VALUES ($fval) RETURNING \"id\"";
                $res = DataManager::dm_query($sql,$params);
                if(!$res) 
                {
                    $objs['status']='ERROR';
                    $objs['msg']=$sql;
                }
            }
        }    
        else
        {
            die("Не обработана команда для MdentitySet");
        }
        return $objs;
    }       
    
}

