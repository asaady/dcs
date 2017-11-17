<?php

namespace tzVendor;
use PDO;

class User
{
    private $id;
    private $username;
    private $user_id;
    private $name;
    private $synonym;

    private $is_authorized = false;

    public function __construct($username = null, $password = null)
    {
        $this->username = $username;
        return $this;
    }

    public static function isAuthorized()
    {
        if (!empty($_SESSION["user_id"])) {
            return (bool) $_SESSION["user_id"];
        }
        return false;
    }

    public function passwordHash($password, $salt = null, $iterations = 10)
    {
        $salt || $salt = uniqid();
        $hash = md5(md5($password . md5(sha1($salt))));

        for ($i = 0; $i < $iterations; ++$i) {
            $hash = md5(md5(sha1($hash)));
        }

        return array('hash' => $hash, 'salt' => $salt);
    }

    public function getSalt($username) 
    {
        $sql = "select pv_salt.value as salt from \"CTable\" as ct "
                . "inner join \"MDTable\" as md "
                . "on ct.mdid = md.id "
                . "inner join \"CPropValue_str\" as pv_salt "
                . "inner join \"CProperties\" as cp_salt "
                . "on pv_salt.pid = cp_salt.id "
                . "and cp_salt.name='salt' "
                . "on ct.id = pv_salt.id "
                . "inner join \"CPropValue_str\" as pv_login "
                . "inner join \"CProperties\" as cp_login "
                . "on pv_login.pid = cp_login.id "
                . "and cp_login.name='login' "
                . "on ct.id = pv_login.id "
                . "where pv_login.value = :username and md.name=:mdname limit 1";
        $res = DataManager::dm_query($sql,array('username'=>$username, 'mdname'=>'Users'));
	if(!$res) 
        {
            throw new \Exception("User $username not found", 1);
	}
        $row=$res->fetch(PDO::FETCH_ASSOC);
        if (!$row) 
        {
            return FALSE;
        }
        return $row['salt'];
    }

    public function authorize($username, $password, $remember=false)
    {
        $salt = $this->getSalt($username);

        if (!$salt) {
            return false;
        }
        $hashes = $this->passwordHash($password, $salt);

        $sql = "select ct.id, pv_login.value as login from \"CTable\" as ct  "
                . "inner join \"MDTable\" as md "
                . "on ct.mdid = md.id "
                . "inner join \"CPropValue_str\" as pv_login "
                . "inner join \"CProperties\" as cp_login "
                . "on pv_login.pid = cp_login.id "
                . "and cp_login.name='login' "
                . "on ct.id = pv_login.id "
                . "inner join \"CPropValue_str\" as pv_pass "
                . "inner join \"CProperties\" as cp_pass "
                . "on pv_pass.pid = cp_pass.id "
                . "and cp_pass.name='pass_hash' "
                . "on ct.id = pv_pass.id "
                . "where pv_login.value = :username and pv_pass.value = :password and md.name=:mdname limit 1";

        $res = DataManager::dm_query($sql,array('username'=>$username,'password'=>$hashes['hash'], 'mdname'=>'Users'));
        $this->user = $res->fetch();
        if (!$this->user) 
        {
            $this->is_authorized = false;
        }
        else 
        {
            $this->is_authorized = true;
            $this->user_id = $this->user['id'];
            $this->saveSession($remember);
        }

        return $this->is_authorized;
    }

    public function logout()
    {
        if (!empty($_SESSION["user_id"])) {
            unset($_SESSION["user_id"]);
        }
    }

    public function saveSession($remember = false, $http_only = true, $days = 7)
    {
        $_SESSION["user_id"] = $this->user_id;

        if ($remember) {
            // Save session id in cookies
            $sid = session_id();

            $expire = time() + $days * 24 * 3600;
            $domain = ""; // default domain
            $secure = false;
            $path = "/";

            $cookie = setcookie("sid", $sid, $expire, $path, $domain, $secure, $http_only);
        }
    }

    public function create($data) 
    {
        $sql = "SELECT pt.id, pt.name, pt.synonym, pt.mdid, pt.type FROM \"CProperties\" AS pt WHERE pt.mdid = :mdid";
        $res = DataManager::dm_query($sql,array('mdid'=> $data['itemid']['name']));        
        $plist = $res ->fetchAll(PDO::FETCH_ASSOC);
        $username = '';
        $password = '';
        $username_id = '';
        $password_id =  '';
        $salt_id =  '';
        foreach ($plist as $f)
        {
            if ($f['name']=='salt') 
            {
                $salt_id = $f['id'];
            }
            elseif ($f['name']=='pass_hash') 
            {
                $password_id = $f['id'];
            }
            if (!array_key_exists($f['id'],$data)) continue;
            if ($f['name']=='login')
            {
                $username = $data[$f['id']]['name'];
                $username_id = $f['id'];
            }    
            if ($f['name']=='pass_hash') 
            {
                $password = $data[$f['id']]['name'];
            }
        }    
        if ($username == '')
        {
            return array('status'=>'ERROR', 'msg'=>'не указан login');
        }
        $user_exists = $this->getSalt($username);
        if ($user_exists) 
        {
            throw new \Exception("User exists: " . $username, 1);
        }
        
        $hashes = $this->passwordHash($password);
        
        DataManager::dm_beginTransaction();
        try 
        {
            $sql ="INSERT INTO \"CTable\" (name, synonym, mdid) VALUES (:name, :synonym, :mdid) RETURNING \"id\"";
            $params = array('name' => $data['name']['name'], 'synonym'=>$data['synonym']['name'],'mdid'=> $data['itemid']['name']);
            $res = DataManager::dm_query($sql,$params);
            $row = $res ->fetch(PDO::FETCH_ASSOC);
            $id = $row['id'];
            $params = array();
            $params['id']=$id;
            $params['userid']=$_SESSION['user_id'];
            $sql = "insert into \"CPropValue_str\" (id, pid, value, userid) values (:id, :pid, :value, :userid) returning \"id\"";
            $params['pid']=$username_id;
            $params['value']=$username;
            $result = DataManager::dm_query($sql,$params);
            $params['pid']=$password_id;
            $params['value']=$hashes['hash'];
            $result = DataManager::dm_query($sql,$params);
            $params['pid']=$salt_id;
            $params['value']=$hashes['salt'];
            $result = DataManager::dm_query($sql,$params);
            DataManager::dm_commit();
        } 
        catch (\PDOException $e) 
        {
            DataManager::dm_rollback();
            echo "Database error: " . $e->getMessage();
            die();
        }
        if (!$result) 
        {
            printf("Database error");
            die();
        } 
        $this->name = $data['name']['name'];
        $this->synonym = $data['synonym']['name'];
        $this->username = $username;
        $this->user_id = $id;
        return array('status'=>'OK', 'id'=>$id);
    }
    public function update($data) 
    {
        $id = $data['itemid']['name'];
        try 
        {
            $col = new CollectionItem($id);
        } 
        catch (Exception $ex) 
        {
            echo "bad user id : ".$id." : ". $e->getMessage();
            die();
        }
        $sql = "SELECT pt.id, pt.name, pt.synonym, pt.mdid, pt.type FROM \"CProperties\" AS pt WHERE pt.mdid = :mdid";
        $res = DataManager::dm_query($sql,array('mdid'=> $col->getcollectionset()->getid()));        
        $plist = $res ->fetchAll(PDO::FETCH_ASSOC);
        $username = '';
        $password = '';
        $username_id = '';
        $password_id =  '';
        $salt_id =  '';
        foreach ($plist as $f)
        {
            if ($f['name']=='salt') 
            {
                $salt_id = $f['id'];
            }
            elseif ($f['name']=='pass_hash') 
            {
                $password_id = $f['id'];
                $password = $data[$f['id']]['name'];
            }
            elseif ($f['name']=='login')
            {
                $username = $data[$f['id']]['name'];
                $username_id = $f['id'];
            }    
        }    
        if ($username == '')
        {
            return array('status'=>'ERROR', 'msg'=>'не указан login');
        }
        
        $hashes = $this->passwordHash($password);
        
        DataManager::dm_beginTransaction();
        try 
        {
            $sql ="UPDATE \"CTable\" SET name=:name, synonym=:synonym WHERE id=:id";
            $params = array('name' => $data['name']['name'], 'synonym'=>$data['synonym']['name'],'id'=> $id);
            $res = DataManager::dm_query($sql,$params);
            $params = array();
            $params['id']=$id;
            $params['userid']=$_SESSION['user_id'];
            $sql = "update \"CPropValue_str\" set value=:value, userid=:userid where id=:id AND pid=:pid";
            $params['pid']=$username_id;
            $params['value']=$username;
            $result = DataManager::dm_query($sql,$params);
            $params['pid']=$password_id;
            $params['value']=$hashes['hash'];
            $result = DataManager::dm_query($sql,$params);
            $params['pid']=$salt_id;
            $params['value']=$hashes['salt'];
            $result = DataManager::dm_query($sql,$params);
            DataManager::dm_commit();
        } 
        catch (\PDOException $e) 
        {
            DataManager::dm_rollback();
            echo "Database error: " . $e->getMessage();
            die();
        }
        if (!$result) 
        {
            printf("Database error");
            die();
        } 
        $this->name = $data['name']['name'];
        $this->synonym = $data['synonym']['name'];
        $this->username = $username;
        $this->user_id = $id;
        return array('status'=>'OK', 'id'=>$id);
    }
    
    public static function getUserName($id)
    {
        $sql = "select name,synonym from \"CTable\" where id = :id";
        $sth = DataManager::dm_query($sql,array(":id" => $id));
        $row = $sth->fetch();
        if (!$row)
        {
            return "Anonymous";
        }
        return $row["synonym"];
    }
    public static function isAdmin()
    {
        if (array_key_exists('user_id', $_SESSION))
        {        
            $user_role = self::getUserRole();
            return (array_search('admin', array_column($user_role, 'name'))!==false);
        }
        return false;
    }
    public static function getUserRole()
    {
        $sql = "select pv_usrol.value as id, ct_rol.name, ct_rol.synonym from \"CPropValue_cid\" as pv_usrol
                inner join \"CProperties\" as cp_usrol
                on pv_usrol.pid=cp_usrol.id
                and cp_usrol.name='role'
                inner join \"CPropValue_cid\" as pv_usr
                        inner join \"CProperties\" as cp_usr
                        on pv_usr.pid=cp_usr.id
                        and cp_usr.name='user'
                on pv_usrol.id=pv_usr.id
                inner join \"CTable\" as ct_rol
                on pv_usrol.value = ct_rol.id
                where pv_usr.value = :userid";
        $res = DataManager::dm_query($sql,array('userid'=>$_SESSION['user_id']));
        return $res->fetchAll(PDO::FETCH_ASSOC);    
    }        
    public static function getUserInterface()
    {
        $sql = "select pv_usint.value as id, ct_int.name, ct_int.synonym from \"CPropValue_cid\" as pv_usint
                inner join \"CProperties\" as cp_usint
                on pv_usint.pid=cp_usint.id
                and cp_usint.name='interface'
                inner join \"CPropValue_cid\" as pv_usr
                        inner join \"CProperties\" as cp_usr
                        on pv_usr.pid=cp_usr.id
                        and cp_usr.name='user'
                on pv_usint.id=pv_usr.id
                inner join \"CTable\" as ct_int
                on pv_usint.value = ct_int.id
                where pv_usr.value = :userid";
        $res = DataManager::dm_query($sql,array('userid'=>$_SESSION['user_id']));
        
        while($row = $res->fetch(PDO::FETCH_ASSOC))
        {
            return $row['id'];
        }
        return false;    
    }        
}
