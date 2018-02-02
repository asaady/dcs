<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

trait T_Set {
    public function getItemsByName($name)
    {
        return NULL;
    }
    public static function txtsql_set_access($ra_tbl = 'RoleAccess', $type = 'mdid') 
    {
        $sql = "SELECT pv.value as id, cp_rd.name as name, ct_rd.value as val "
                . "FROM \"CPropValue_$type\" as pv 
		   inner join \"CTable\" as ct
			inner join \"MDTable\" as md_ra
			on ct.mdid = md_ra.id
			and md_ra.name='" . $ra_tbl . "'
			inner join \"CPropValue_cid\" as pv_rol
				inner join \"CProperties\" as cp_rol
				on pv_rol.pid=cp_rol.id
				and cp_rol.name='role_kind'
				inner join \"CPropValue_cid\" as pv_usrol
					inner join \"CProperties\" as cp_usrol
					on pv_usrol.pid=cp_usrol.id
					and cp_usrol.name='role'
					inner join \"CPropValue_cid\" as pv_usr
                                            inner join \"CProperties\" as cp_usr
                                            on pv_usr.pid=cp_usr.id
                                            and cp_usr.name='user'
					on pv_usrol.id=pv_usr.id
				on pv_rol.value=pv_usrol.value
				and pv_rol.id<>pv_usrol.id
			on ct.id = pv_rol.id
                        inner join \"CPropValue_bool\" as ct_rd
				inner join \"CProperties\" as cp_rd
				on ct_rd.pid=cp_rd.id
			on ct.id = ct_rd.id
			AND ct_rd.value 
		on pv.id=ct.id
                where pv_usr.value = :userid and pv.value = :id";
        return $sql;
    }
}
