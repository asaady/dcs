<?php
namespace Dcs\Vendor\Core\Models;
use Exception;

abstract class Model implements I_Model
{
    protected $id;
    protected $name;
    protected $synonym;
    protected $version;
    protected $properties;
    
    function __construct()
    {
    }
    function getid() 
    {
      return $this->id;
    }
    function getname() 
    {
      return $this->name;
    }
    function getshortname()
    {
        $res = $this->name;
        if (strlen($res)>55)
        {    
            $res = substr($res, 0, 55);
            $end = strlen(strrchr($res, ' ')); // длина обрезка 
            $res = substr($res, 0, -$end) . '...';         
        }    
        return $res;
    }
    function getsynonym() 
    {
      return $this->synonym;
    }
    function getversion() 
    {
      return $this->version;
    }
    function setid($val) 
    {
      if ($this->id=='') 
	$this->id=$val;
      else
	throw new Exception('You may not alter the value of the ID field!');
    }
    function setname($name) 
    {
	$this->name=$name;
    }
    function setsynonym($val) 
    {
	$this->synonym=$val;
    }
    function __get($propertyName) 
    {
	if(method_exists($this, 'get' . $propertyName)) 
        {
	    return call_user_func(array($this, 'get' . $propertyName));
	} 
        else 
        {
            throw new Exception("Неверное имя свойства \"$propertyName\"!");
	}
    }
    public function get_properties() 
    {
	return $this->properties;
    }
    public function getProperty($id) 
    {
        $res = array();
        if ($this->isExistTheProp($id))
        {
            $res = $this->properties[$id];
        }
        return $res;
    }
    public function isExistTheProp($id) 
    {
        return isset($this->properties[$id]);
    }
    public function setproperty($propid,$val)
    {
        $this->properties[$propid] = $val;
    }
    public function getplist() 
    {
        return array(
            'id'=>array('id'=>'id','name'=>'id','synonym'=>'ID','type'=>'str'),
            'name'=>array('id'=>'name','name'=>'name','synonym'=>'NAME','type'=>'str'),
            'synonym'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM','type'=>'str'),
            'rank'=>array('id'=>'rank','name'=>'rank','synonym'=>'RANK','type'=>'int'),
            'type'=>array('id'=>'type','name'=>'type','synonym'=>'TYPE','type'=>'str'),
            'class'=>array('id'=>'class','name'=>'class','synonym'=>'CLASS','type'=>'str'),
            'field'=>array('id'=>'field','name'=>'field','synonym'=>'FIELD','type'=>'int')
            );        
    }
}