<?php
namespace Dcs\Vendor\Core\Models;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

use PDO;

class Mdentity extends Head implements I_Head, I_Property
{
    use T_EProperty;
    
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
          'PSET' => $this->getProperties(TRUE,'toset'),
          'navlist' => array(
              $this->mditem->getid() => $this->mditem->getsynonym(),
              $this->id => $this->synonym
            )
          );
    }
    public function getProperties($byid = FALSE, $filter = '') 
    {
        
        $objs = array();
        if (is_callable($filter)) {
            $f = $filter;
        } else {
            if (strtolower($filter) == 'toset') {
                $f = function($item) {
                    return $item['ranktoset'] > 0;
                };
            } elseif (strtolower($filter) == 'tostring') {
                $f = function($item) {
                    return $item['ranktostring'] > 0;
                };
            } else {
                $f = NULL;
            }
        }
        $plist = $this->getplist();
        $key = -1;    
        foreach($plist as $prop) 
        {
            $rid = $prop['id'];
            if (($rid !== 'id')&&($f !== NULL)&&(!$f($prop))) {
                continue;
            }
            if ($byid) {    
                $key = $rid;
            } else {
                $key++;
            }    
            $objs[$key] = $prop;
            $objs[$key]['class'] = 'active';
            if ($key == 'id') {
                $objs[$key]['class'] = 'hidden';
            }
        }
        return $objs;
    }
    public function item() 
    {
        return new Mdproperty($this->id);
    }
    public function head($mdid='') 
    {
        return NULL;
    }
    function getItemsByFilter($context, $filter)
    {
        $mode = $context['MODE'];
        $action = $context['ACTION'];
        $objs = array();
        $objs['id'] = $this->id;
        $objs['name'] = $this->name;
        $objs['synonym'] = $this->synonym;
        $objs['version'] = $this->version;
        $objs['LDATA']=array();
        $objs['PSET'] = $this->getProperties(TRUE,'toset');
//        die(var_dump($objs['PSET']).var_dump($this->properties));
        $objs['actionlist'] = DataManager::getActionsbyItem('Mdentity',$mode,$action);
        foreach ($this->properties as $row) {
            $objs['LDATA'][$row['id']] = array();
            foreach ($objs['PSET'] as $pkey=>$prow)
            {
                $objs['LDATA'][$row['id']][$pkey]=array('name'=>$row[$prow['id']],'id'=>'');
            }    
        }
        return $objs;
    }
    function update($data) 
    {
        $sql = '';
        $objs = array();
        $params = array();
        if (array_key_exists('name', $data))
        {
            if ($this->name!=$data['name']['name']) 
            {
                $sql .= ", name=:name";
                $params['name']=$data['name']['name'];
            }    
        }    
        if (array_key_exists('synonym', $data))
        {
            if ($this->synonym!=$data['synonym']['name']) 
            {
                $sql .= ", synonym=:synonym";
                $params['synonym']=$data['synonym']['name'];
            }    
        }    
        $objs['status']='NONE';
        if ($sql!='')
        {
            $objs['status']='OK';
            $sql = substr($sql,1);
            $sql = "UPDATE \"MDTable\" SET$sql WHERE id=:id";
            $params['id']=$this->id;
            $res = DataManager::dm_query($sql,$params);
            if(!$res) 
            {
                $objs['status']='ERROR';
                $objs['msg']=$sql;
            }
        }
        $objs['id']=$this->id;
	return $objs;
    }
    /*
     * Создание свойства метаданного
     */
    function create_property($data) 
    {
        if (($this->mdentityset->getname()=='Cols')||($this->mdentityset->getname()=='Comps')||($this->mdentityset->getname()=='Regs'))
        {
            $props= array(
                'id'=>array('name'=>'id','type'=>'str'),
                'name'=>array('name'=>'name','type'=>'str'),
                'synonym'=>array('name'=>'synonym','type'=>'str'),
                'type'=>array('name'=>'type','type'=>'str'),
                'length'=>array('name'=>'length','type'=>'int'),
                'prec'=>array('name'=>'prec','type'=>'int'),
                'rank'=>array('name'=>'rank','type'=>'int'),
                'ranktoset'=>array('name'=>'ranktoset','type'=>'int'),
                'valmdid'=>array('name'=>'valmdid','type'=>'mdid')
            );
            $dbtable = 'CProperties';
        }    
        else 
        {
            $props= array(
                'id'=>array('name'=>'id','type'=>'str'),
                'name'=>array('name'=>'name','type'=>'str'),
                'synonym'=>array('name'=>'synonym','type'=>'str'),
                'type'=>array('name'=>'type','type'=>'cid'),
                'length'=>array('name'=>'length','type'=>'int'),
                'prec'=>array('name'=>'prec','type'=>'int'),
                'rank'=>array('name'=>'rank','type'=>'int'),
                'ranktostring'=>array('name'=>'ranktostring','type'=>'int'),
                'ranktoset'=>array('name'=>'ranktoset','type'=>'int'),
                'isedate'=>array('name'=>'isedate','type'=>'bool'),
                'valmdid'=>array('name'=>'valmdid','type'=>'mdid'),
                'propid'=>array('name'=>'propid','type'=>'mdid')
            );
            $dbtable = 'MDProperties';
        }
        $sql='';
        $objs = array();
        $fname ='';
        $fval = '';
        $params=array();
        foreach ($props as $key=>$prop)
        {    
            if ($key=='id') 
            {
                continue;
            }    
            if (array_key_exists($key, $data))
            {
                if ($prop['type']=='mdid')
                {    
                    $par=$data[$prop['name']]['id'];
                } 
                elseif ($prop['type']=='cid')
                {    
                    $par=$data[$prop['name']]['id'];
                } 
                else
                {
                    $par=$data[$prop['name']]['name'];
                }
                if ($par=='')
                {
                    continue;
                }    
                $params[$prop['name']]= $par;    
                $fname .=", $prop[name]";
                $fval .=", :$prop[name]";
            }    
        }
        $fname = substr($fname,1);
        $fval = substr($fval,1);
        $objs['status']='NONE';
        if ($fname!='')
        {
            $objs['status']='OK';
            $params['id']=$this->id;
            $sql = "INSERT INTO \"$dbtable\" ($fname, mdid) VALUES ($fval,:id) RETURNING \"id\";";
            $res = DataManager::dm_query($sql,$params);
            if(!$res) 
            {
                $objs['status']='ERROR';
                $objs['msg']=$sql;
            }
            else 
            {
                $row = $res->fetch(PDO::FETCH_ASSOC);
                $objs['id']= $row['id'];
            }
        }
	return $objs;
    }
    function before_save($data) 
    {
        if (array_key_exists('name', $data))
        {
            if ($this->name!=$data['name']['name']) 
            {
                $objs[]=array('name'=>'Name', 'pval'=>$this->name, 'nval'=>$data['name']['name']);
            }    
        }    
        if (array_key_exists('synonym', $data))
        {
            if ($this->synonym!=$data['synonym']['name']) 
            {
                $sql .= ", synonym=:synonym";
                $objs[]=array('name'=>'Synonym', 'pval'=>$this->synonym, 'nval'=>$data['synonym']['name']);
            }    
        }    
	return $objs;
    }
    public function getItemsByName($name) 
    {
        return NULL;
    }
}
