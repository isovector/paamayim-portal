<?php

function Info_Login() {
    return array(   "depends" => array("auth", "tpl", "httpe", "msg"),
                    "provides" => array("login"),
                    "name" => "HTTP Login",
                    "version" => "1.0.0",
                    "author" => "Sandy Maguire",
                    "category" => CAT_ACCESS,
                    "about" => "Provides a graphical environment for a user login."
                 );
}

function Load_Login() {
class Login extends Module {
    public function authenticate($permissions) {
        if ($this->depends["auth"]->uid == 1 && !isset($_POST["submit"]))
            $this->show_login();
        else if ($this->depends["auth"]->uid == 1 && isset($_POST["submit"]))
            if (service("auth", "login", $_POST["username"], md5($_POST["password"])) === false) {
                service("auth", "logout");
                $this->show_login();
            } else
#FIXME: this doesn't do an array combine
//                if (in_array($permissions, $this->depends["auth"]->allowed))
                    header("Location: " . $_SERVER['REQUEST_URI']);
//                else
//                    service("httpe", "error", 403);
    }

    private function show_login() {
        service("tpl", "assign", "here", $_SERVER["REQUEST_URI"]);
        service("tpl", "display", "admin/login.tpl");
        die();
    }
}
}

?>
