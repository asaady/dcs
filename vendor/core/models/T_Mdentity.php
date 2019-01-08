<?php

namespace Dcs\Vendor\Core\Models;

trait T_Mdentity {
    public function getplist($context) 
    {
        return array(
            '0'=>array('id'=>'id','name'=>'id','synonym'=>'ID',
                        'rank'=>0,'ranktoset'=>1,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>1),
            '1'=>array('id'=>'name','name'=>'name','synonym'=>'NAME',
                        'rank'=>1,'ranktoset'=>2,'ranktostring'=>1,
                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            '2'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM',
                        'rank'=>2,'ranktoset'=>3,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
             );
    }
    public function getNameFromData($context,$data='')
    {
        if (!$data) {
            return array('name' => $this->name, 'synonym' => $this->synonym);
        } else {
            return array('name' => $data['name']['name'],
                         'synonym' => $data['synonym']['name']);
        }    
    }        
    public function getItemsByName($name) 
    {
        return NULL;
    }
    public function getItems($context) 
    {
        $objs = array();
        foreach ($this->get_items() as $row) {
            $objs[$row['id']] = array(
            'id' => array('id'=>'','name'=>$row['id']),
            'name' => array('id'=>'','name'=>$row['name']),
            'synonym' => array('id'=>'','name'=>$row['synonym']),
            'name_type' => array('id'=>$row['type'],'name'=>$row['name_type']),
            'ranktoset' => array('id'=>'','name'=>$row['ranktoset']),
            'valmdid' => array('id'=>$row['valmdid'],'name'=>$row['name_valmdid']),
            'valmdtypename' => array('id'=>$row['valmdtypename'],'name'=>$row['valmdtypename']),
            'class' => 'active');
        }
        $this->version = time();
        return $objs;
    }
    public function getItemsProp($context) 
    {
        return $this->getProperties(TRUE,'toset');
    }
    public function load_data($context,$data='')
    {
        if (!$data) {
            return array(   'id' => array('id'=>$this->id,'name'=>$this->id),
                        'name' => array('id'=>'','name'=>$this->name),
                        'synonym' => array('id'=>'','name'=>$this->synonym),
                        'mditem' => array('id'=>$this->mditem,'name'=>$this->mditem)
                );
        }
        return array('id'=>array('id'=>$data['id'], 'name'=>$data['id']),
                      'name'=>array('id'=>$data['name'],'name'=>$data['name']),
                      'synonym'=>array('id'=>$data['synonym'], 'name'=>$data['synonym']),
                      'mditem'=>array('id'=>$data['mditem'], 'name'=>$data['mditem'])
            );
    }            
}
