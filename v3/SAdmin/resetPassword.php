<?php

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
*/

$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PageTitle="Create Client";

$PageTitleFolder="Admin";

$AccessType=$_SESSION['AccessType'];
$UserID=$_GET['UserID'];

$UserPwdAct="Reset@camaroo";
$UserPwd = password_hash($UserPwdAct, PASSWORD_DEFAULT);

if($UserID!='' && $AccessType==1){
 $resetPass="update AdminLoginDetails set Hashpassword='$UserPwd' WHERE UserID='$UserID'  "; 
$stmtresetPass=$iconn->query($resetPass);
if($stmtresetPass){
    echo 'New Password "' .$UserPwdAct.'"';
}else{
    echo "Unable To Update Password";
}

}else{
    echo "<script>alert('Unauthorized')</script>";
}

?>
