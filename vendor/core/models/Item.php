<?php
namespace Dcs\Vendor\Core\Models;

class Item extends Model
{
    use T_Item;

    protected $head;
    protected $data;


    public function __construct()
    {
        //todo
    }
    function before_delete() 
    {
        $nval="удалить";
        if (!$this->activity)
        {    
            $nval='снять пометку удаления';
        }   
        return array($this->id=>array('id'=>$this->id,'name'=>"Элемент ".$this->get_head->getsynonym(),'pval'=>$this->name,'nval'=>$nval));
    }    
    function before_save($data) {
        $sql = '';
        $objs = array();
        foreach ($this->properties as $prop)
        {    
            $propid = $prop['id'];
            if ($propid=='id') continue;
            if (!array_key_exists($propid, $data))
            {        
                continue;
            }
            $nval = $data[$prop['id']]['name'];
            $nvalid = $data[$prop['id']]['id'];
            $pval = $this->data[$prop['id']]['name'];
            $pvalid = '';
            if ($prop['type']=='id') 
            {
                $pvalid = $this->data[$prop['id']]['id'];
                if ($pvalid==$nvalid) 
                {
                    continue;
                }    
                if (($pvalid==DCS_EMPTY_ENTITY)&&($nvalid==''))
                {
                    continue;
                }
            }
            elseif ($prop['type']=='date') 
            {
                if (substr($pval,0,19)==substr($nval,0,19)) 
                {
                    continue;
                }    
            } 
            elseif ($prop['type']=='bool') 
            {
                if ((bool)$pval==(bool)$nval) 
                {
                    continue;
                }   
            } 
            else 
            {
                if ($pval==$nval) 
                {
                    continue;
                }    
            }
            $objs[]=array('id'=>$prop['id'], 'name'=>$prop['name'],'pvalid'=>$pvalid, 'pval'=>$pval, 'nvalid'=>$nvalid, 'nval'=>$nval);
        }       
	return $objs;
    }        
  }