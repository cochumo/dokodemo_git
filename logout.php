<?php
session_start();

//セッション情報を削除
$_SESSION = array();
if (ini_get("session.use_cookies")){
    $params = session_get_cookie_params();
    setcookie(session_name(),'',time() - 42000,
    $params["path"],$params["domain"],
    $params["secure"],$params["httponly"]
    );
}
session_destroy();

//cookie情報も削除
setcookie('email','',time() - 3600);
setcookie('password','',time() - 3600);

header('Location: login.php');
exit();
?>