<?php
namespace Dcs\Vendor\Core\Models;
use Exception;

interface iModel 
{
    public function get_data($mode='');
    public function getid();
    public function getname();
    public function getsynonym();
    public function getversion();
}

class Model implements iModel
{
    protected $id;
    protected $name;
    protected $synonym;
    protected $version;
    
    function __construct()
    {
    }
    // метод выборки данных
    public function get_data($mode='')
    {
            // todo
    }
    function getid() 
    {
      return $this->id;
    }
    function getname() 
    {
      return $this->name;
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
}