<?php
namespace dcs\vendor\core;

class Authorization
{
    public $actions = array(
        "login" => "login",
        "logout" => "logout"
    );
    protected $username='';
    protected $password='';
    protected $rememberme='';
    
    function __construct($data) {
        $this->username = $data["username"]['name'];
        $this->password = $data["password"]['name'];
        $this->rememberme = $data["remember-me"]['name'];
    }

    public function login()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            // Method Not Allowed
            http_response_code(405);
            header("Allow: POST");
            $this->setFieldError("main", "Method Not Allowed");
            return;
        }
        setcookie("sid", "");


        if (empty($username)) {
            $this->setFieldError("username", "Enter the username");
            return;
        }

        if (empty($password)) {
            $this->setFieldError("password", "Enter the password");
            return;
        }

        $user = new \dcs\vendor\core\User();
        $auth_result = $user->authorize($username, $password, $remember);

        if (!$auth_result) {
            $this->setFieldError("password", "Invalid username or password");
            return;
        }

        $this->status = "ok";
        $this->setResponse("redirect", ".");
        $this->message = sprintf("Hello, %s! Access granted.", $username);
    }

    public function logout()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            // Method Not Allowed
            http_response_code(405);
            header("Allow: POST");
            $this->setFieldError("main", "Method Not Allowed");
            return;
        }

        $user = new \dcs\vendor\core\User();
        $user->logout();

        $this->setResponse("redirect", ".");
        $this->status = "ok";
    }

}

