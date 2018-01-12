<?php
namespace Dcs\Vendor\Core\Models;

trait  T_Item 
{
    protected $data;
    
    function get_data($mode='') 
    {
        $tid = $this->id;
        $tsyn = $this->synonym;
        if ($this->id) {
            $tid = 'new';
            $tsyn = 'Новый';
        }
        return array('id'=>$this->id,      
                    'version'=>$this->version,
                    'PLIST'=>$this->properties,
                    'navlist'=>array(
                    $this->head->getmditem()=>$this->head->getmditemsynonym(),
                    $this->head->getid()=>$this->head->getsynonym(),
                    $tid=>$tsyn
                    )
              );

    }
    function set_data($data) 
    {
	foreach($this->properties as $key => $prop) {
            if ($key == 'id') {
                continue;
            }
	    $v = $prop['id'];
            $this->data[$v]=array();
	    if(array_key_exists($v,$data)) {
                $this->data[$v]['name']=$data[$v]['name'];
                if (($prop['type'] === 'id')||
                    ($prop['type'] === 'cid')||
                    ($prop['type'] === 'propid')||
                    ($prop['type'] === 'mdid')) {
                    if ($data[$v]['id'] !== '') {    
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

