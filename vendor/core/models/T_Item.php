<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

trait T_Item 
{
    protected $data;
    
    function get_data($context) 
    {
        $tid = $this->id;
        $tsyn = $this->synonym;
        if (!$this->id) {
            $tid = 'new';
            $tsyn = 'Новый';
        }
        return array('id'=>$this->id,      
                    'version'=>$this->version,
                    'PSET'=>array(),
                    'PLIST'=>$this->getProperties(FALSE),
                    'navlist'=>array(
                    $this->head->getmditem()->getid()=>$this->head->getmditemsynonym(),
                    $this->head->getid()=>$this->head->getsynonym(),
                    $tid=>$tsyn
                    )
              );

    }
    function getdata() 
    {
        return $this->data;
    }
    function set_data($data) 
    {
	foreach($this->properties as $aritem)
        {
	    $v = $aritem['id'];
            $this->data[$v]=array();
	    if(array_key_exists($v,$data))
            {
                $this->data[$v]['name']=$data[$v]['name'];
                if (($aritem['type'] === 'id')||
                    ($aritem['type'] === 'cid')||
                    ($aritem['type'] === 'mdid')) {
                    if ($data[$v]['id'] !== '')
                    {    
                        $this->data[$v]['id'] = $data[$v]['id'];
                    } else {
                        $this->data[$v]['name'] = '';
                        $this->data[$v]['id'] = DCS_EMPTY_ENTITY;
                    }
                }
	    } else {
                $this->data[$v]['name'] = '';
                $this->data[$v]['id'] = DCS_EMPTY_ENTITY;
	    }  
	}
    }
    public function load_data()
    {
        $artemptable = $this->get_tt_sql_data();
        $sql = "select * from tt_out";
        $sth = DataManager::dm_query($sql);        
        $this->data = array();
        while($row = $sth->fetch(PDO::FETCH_ASSOC)) 
        {
            $this->data['id'] = array('id'=>'','name'=>$row['id']);
            foreach($this->properties as $prow)
            {
                $rowname = str_replace("  ","",$prow['name']);
                $rowname = str_replace(" ","",$rowname);
                $this->data[$prow['id']] = array('id'=>$row["id_$rowname"],'name'=>$row["name_$rowname"]);
            }    
        }
        $this->version = time();
    }
    public function getattr($propid) 
    {
        $val='';
	if(array_key_exists($propid, $this->data))
        {
	  $val=$this->data[$propid]['name'];
	}  
	return $val;
    }
    function getattrid($propid)
    {
        $val='';
	if(array_key_exists($propid, $this->data))
        {
	  $val=$this->data[$propid]['id'];
	}  
	return $val;
    }
    public function setattr($propid,$valname,$valid='') 
    {
        $val='';
	if(array_key_exists($propid, $this->data))
        {
	  $this->data[$propid]['name'] = $valname;
          $this->data[$propid]['id'] = $valid;
	}  
        return $this;
    }
    public function getItemsByFilter($context, $filter)
    {
        $prefix = $context['PREFIX'];
        $action = $context['ACTION'];
        $objs = array();
        $objs['PSET'] = array();
        $objs['PLIST'] = $this->getProperties(TRUE);
        $objs['SDATA'] = array();
        $objs['SDATA'][$this->id] = $this->data;
        $objs['actionlist']= DataManager::getActionsbyItem($context['CLASSNAME'],$prefix,$action);
        return $objs;
    }
    public function update($data)     
    {
        $res = $this->update_properties($data);
        if ($res['status']=='OK')
        {
            $res1 = $this->update_dependent_properties($res['objs']);
            if (is_array($res1['objs'])) {
                $res['objs'] += $res1['objs'];
            }
        }    
        return $res;
    }
}

