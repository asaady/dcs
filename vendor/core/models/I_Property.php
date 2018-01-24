<?php
namespace Dcs\Vendor\Core\Models;

interface I_Property
{
    //sql request text returning properties entity
    public function txtsql_getproperties();
    public function txtsql_getproperty();
    public function loadProperties();
    public function getplist();
}

