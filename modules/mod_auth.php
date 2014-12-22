<?php

function Info_Auth() {
    return array(   "depends" => array("users", "groups", "msg"),
                    "provides" => array("auth"),
                    "name" => "Authorization",
                    "version" => "1.0.0",
                    "author" => "Sandy Maguire",
                    "category" => CAT_CORE,
                    "about" => "Provides generalized authorization by delegating to srv:users and srv:groups. Handles persistance."
                );
}

function Load_Auth() {
class Auth extends Module {
    public $uid;
    public $groups = array();
    public $user;
    private $pass;
    public $allowed = array();

    public function __construct() {
        if (@isset($_SESSION["auth"]))
		    $this->login($_SESSION["auth"]["user"], $_SESSION["auth"]["pass"]);
        else
            $this->anon();
    }

    public function login($user, $pass) {
        if (!service("users", "verify", $user, $pass)) {
            $this->logout();
            return false;
        }
        $this->uid = service("users", "get_uid", $user);
        $this->user = $user;
        $this->pass = $pass;
        $this->groups = service("users", "get_groups", $user);
#FIXME: fix this
//        $this->allowed = service("group", "allowed", $this->gid); 
        $_SESSION["auth"] = array("user" => $user, "pass" => $pass, "uid" => $this->uid);
        return $this->uid;
    }

    private function anon() {
        $this->uid = 1;
        $this->user = service("users", "user_name", 1);
        $this->pass = md5(null);
        $this->groups = array(1);
//        $this->allowed = service("groups", "allowed", $this->gid);
    }

    public function logout() {
        unset($_SESSION["auth"]);
        $this->anon();
    }
}
}

?>
