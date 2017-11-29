<?php
namespace tzVendor;
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/tz_const.php");
use PDO;
use tzVendor\Md;

class MdentitySet extends Mditem {
    function get_data($mode='') 
    {
        $clsid='active';
        if ($mode!='CONFIG')
        {
            $clsid='hidden';
        }    
        $plist = array(
          'id'=>array('name'=>'id','synonym'=>'ID','class'=>$clsid),
          'name'=>array('name'=>'name','synonym'=>'NAME','class'=>'active'),
          'synonym'=>array('name'=>'synonym','synonym'=>'SYNONYM','class'=>'active')
        );
        return array(
          'id'=>$this->id,
          'name'=>$this->name,
          'synonym'=>$this->synonym,
          'version'=>$this->version,
          'PSET' => $plist,   
          'navlist' => array(
              $this->id=>$this->synonym
            )
          );
    }
    function getMdentity($mode,$edit_mode)
    {
        
	$sql = "SELECT md.id, md.name, md.synonym FROM \"MDTable\" AS md WHERE md.mditem= :mditem";
        $params = array('mditem'=>$this->id);
        $dop = DataManager::get_md_access_text($edit_mode);
        if ($dop!='')
        {    
            $params['userid'] = $_SESSION['user_id'];
            $sql .= " AND ".$dop;
        }    
        $sth = DataManager::dm_query($sql,$params);        
        $plist = array();
        if ($mode!='CONFIG')
        {
            $plist['id'] = array('name'=>'id','synonym'=>'ID','class'=>'hidden');
        } 
        else 
        {
            $plist['id'] = array('name'=>'id','synonym'=>'ID','class'=>'active');
        }    
        $plist['name'] = array('name'=>'name','synonym'=>'NAME','class'=>'active');
        $plist['synonym'] = array('name'=>'synonym','synonym'=>'SYNONYM','class'=>'active');
        $objs = array();
        $objs['PLIST'] = $plist;
        $objs['PSET'] = $plist;
        $objs['SDATA'] = array();
        $objs['SDATA'][$this->id] = array();
        $objs['SDATA'][$this->id]['id'] = array('id'=>$this->id,'name'=>'');
        $objs['SDATA'][$this->id]['name'] = array('id'=>'','name'=>$this->name);
        $objs['SDATA'][$this->id]['synonym'] = array('id'=>'','name'=>$this->synonym);
        $objs['actionlist'] = DataManager::getActionsbyItem('EntitySet',$mode,$edit_mode);          
        $objs['LDATA'] = array();
        while($row = $sth->fetch(PDO::FETCH_ASSOC)) 
        {
            $objs['LDATA'][$row['id']] = array();
            $objs['LDATA'][$row['id']]['id'] = array('id'=>'','name'=>$row['id']);
            $objs['LDATA'][$row['id']]['name'] = array('id'=>'','name'=>$row['name']);
            $objs['LDATA'][$row['id']]['synonym'] = array('id'=>'','name'=>$row['synonym']);
        }
        return $objs;
    }
    public function create($data)
    {
        $objs = array();
        $params = array();
        $props= array('name','synonym');
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
            $sql ="INSERT INTO \"MDTable\" ($fname, mditem) VALUES ($fval,:mditem) RETURNING \"id\"";
            $params['mditem']=$this->id;
            $res = DataManager::dm_query($sql,$params);
            if(!$res) 
            {
                $objs['status']='ERROR';
                $objs['msg']=$sql;
            }
            else 
            {
                $row = $res->fetch(PDO::FETCH_ASSOC); 
                $objs['id'] = $row['id'];
            }
        }
        if ($objs['status']=='OK')
        {
            Mdproperty::CreateMustBeProperty($this->id,$objs['id']);
        }
            
        return $objs;
    }       
    
}

