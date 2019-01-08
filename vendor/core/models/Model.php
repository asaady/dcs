<?php
namespace Dcs\Vendor\Core\Models;
use Exception;

abstract class Model implements I_Model
{
    protected $id;
    protected $name;
    protected $synonym;
    protected $version;
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
      if ($this->id == '') 
	$this->id = $val;
      else
	throw new DcsException('You may not alter the value of the ID field!');
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
            throw new DcsException("Неверное имя свойства \"$propertyName\"!");
	}
    }
    function __toString() 
    {
      return $this->synonym;
    }
}