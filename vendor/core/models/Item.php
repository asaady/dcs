<?php
namespace Dcs\Vendor\Core\Models;

use Dcs\Vendor\Core\Models\Entity;
use PDO;
use DateTime;
use Dcs\Vendor\Core\Models\DcsException;

class Item extends Entity implements I_Sheet, I_Property 
{
    use T_Sheet;
    use T_Entity;
    use T_Item;
    use T_Property;
    use T_EProperty;
    public function setnamesynonym()
    {
        $this->name = $this->gettoString();
        $this->synonym = $this->name;
    }        
    public function getaccessright_id()
    {
        return $this->get_head()->get_head()->get_mdid();
    }        
    public function get_set_by_item()
    {
        $sql = "SELECT parentid, childid, rank FROM \"SetDepList\" "
                . "where childid = :itemid";
        $res = DataManager::dm_query($sql,array('itemid'=>$this->id));
        if(!$res) {
            return DCS_EMPTY_ENTITY;
        }
        $row = $res->fetch(PDO::FETCH_ASSOC);
        if(!count($row)) 
        {
            return DCS_EMPTY_ENTITY;
        }
        return $row['parentid'];
    }
    function head() 
    {
        return new Sets($this->get_set_by_item());
    }
    function item() 
    {
        return NULL;
    }
    public function gettoString() 
    {
        $artoStr = array();
        foreach($this->properties as $prop) {
            if ($prop['ranktostring'] > 0) {
              $artoStr[$prop['id']] = $prop['ranktostring'];
            }
        }
        if (!count($artoStr)) {
            foreach($this->properties as $prop) {
                if ($prop['rank'] > 0) {
                  $artoStr[$prop['id']] = $prop['rank'];
                }  
            }
            if (count($artoStr)) {
              asort($artoStr);
              array_splice($artoStr,1);
            }  
        } else {
            asort($artoStr);
        }
        if (count($artoStr)) {
            $res = '';
            foreach($artoStr as $prop => $rank) {
                $name = $this->data[$prop]['name'];
                if ($this->properties[$prop]['name_type'] == 'date') {
                    $name = substr($name,0,10);
                }
                $res .= ' '.$name;
            }
            if ($res != '') {
                $res = substr($res, 1);
            }    
            return $res;
        } else {
            return $this->name;
        }
    }
    public function createItem($name,$prefix='')
    {
        $arSetItemProp = self::getMDSetItem($this->mdentity->getid());
        $mdid = $arSetItemProp['valmdid'];
        $objs = array();
        $objs['PSET'] = $this->getProperties(true,'toset');
        $sql = "INSERT INTO \"ETable\" (mdid, name) VALUES (:mdid, :name) RETURNING \"id\"";
        $params = array();
        $params['mdid']=$this->head->getid;
        $params['name']= str_replace('Set','Item', $name);
        $res = DataManager::dm_query($sql,$params); 
        if ($res)
        {
            $row = $res->fetch(PDO::FETCH_ASSOC);
            $childid = $row['id'];
            
            $rank = DataManager::saveItemToSetDepList($this->id,$childid);
            if ($rank>=0)
            {    
                $item = new Entity($childid);
                $arPropsUse = self::getPropsUse($item->head->getmditem());
                $irank=0;
                foreach ($arPropsUse as $prop)
                {
                    $irank++;
                    $row = $this->isExistTheProp($prop['propid']);
                    if (!$row)
                    {    
                        $data = array();
                        $data['name'] = $prop['name'];
                        $data['synonym'] = $prop['synonym'];
                        $data['mdid']=$item->mdentity->getid();
                        $data['rank']=$irank;
                        $data['ranktoset']=$irank;
                        $data['ranktostring']=$irank;
                        if (isset($prop['length']))
                        {
                            $data['length'] = $prop['length'];
                        }   
                        if (isset($prop['prec']))
                        {
                            $data['prec'] = $prop['prec'];
                        }   
                        $data['pid'] = $prop['propid'];
                        if ($prop['name_type']=='date')
                        {    
                            $data['isedate']='true';
                        }
                        $row = $this->createProperty($data);
                    }    
                    if ($row)
                    {
                        if (strtolower($prop['name'])==='rank')
                        {
                            $sql="INSERT INTO \"IDTable\" (entityid, propid, userid) VALUES (:entityid, :propid, :userid) RETURNING \"id\"";
                            $params = array();
                            $params['entityid']=$childid;
                            $params['propid']=$row['id'];
                            $params['userid']=$_SESSION["user_id"];
                            $res = DataManager::dm_query($sql,$params); 
                            $rowid = $res->fetch(PDO::FETCH_ASSOC);
                            if ($rowid)
                            {
                                $sql="INSERT INTO \"PropValue_int\" (id, value) VALUES (:id, :value)";
                                $params = array();
                                $params['id']=$rowid['id'];
                                $params['value']=$rank;
                                $res = DataManager::dm_query($sql,$params); 
                                $rowid = $res->fetch(PDO::FETCH_ASSOC);
                            }    
                        }    
                    }    
                }    
            }    
        }    
    }        
//    public function get_navlist($context)
//    {
//        $navlist = array();
//        $strkey = 'new';
//        $strval = 'Новый';
//        if ($this->id) {
//            $strkey = $this->id;
//            $strval = sprintf("%s",$this);
//        }    
//        if (isset($context['DATA']['docid'])) {
//            $docid = $context['DATA']['docid']['id'];
//            $doc = new Entity($docid);
//            if (isset($context['DATA']['propid'])) {
//                $propid = $context['DATA']['propid']['id'];
//                $doc->add_navlist($navlist);
//                $navlist[] = array('id'=>$docid,'name'=>sprintf("%s",$doc));
//                $prop = $doc->getProperty($propid);
//                $navlist[] = array('id'=>"$docid?propid=".$propid,'name' => $prop['synonym']);
//                $strkey .= "?docid=$docid&propid=$propid";
//            }
//        }
//        if (!count($navlist)) {    
//            $this->add_navlist($navlist);
//        }    
//        $navlist[] = array('id' => $strkey,'name' => sprintf("%s",$strval));
//        return $navlist;
//    }        
}