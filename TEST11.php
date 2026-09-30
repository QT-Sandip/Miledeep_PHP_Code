<?php
$IsLogin=true;
session_start() ;
session_destroy() ;

$_SESSION["IsLogin"]="LoginPage";
include ($_SERVER['DOCUMENT_ROOT']."/SerpDbConfig/config.php");


if(isset($_POST['submit'])){
		
	
$user_name=$_POST['username'];
$user_password=$_POST['userpassword'];


   $SqlLogin="SELECT ClientName,UserID,UserName,AccessType,Ad.DeptID,SubDeptID,UnitID,IsDev,
CDB.ClientID,DbHost,DbName,DbUserName,DbPassword,StartYear,
(select ServerUrl from AdminConfigDetails order by ConfigID Desc limit 1) as CurrentServerUrl,
Path as CurrentClientPath,
Directory as CurrentDirectory,
SideBarHeaderTbl,SideBarOptionTbl,LoginDtls.Status

FROM AdminLoginDetails as LoginDtls
left join AdminDepartments as Ad on Ad.DeptID=LoginDtls.DeptID
left join AdminClientDbDetails as CDB on CDB.ClientID=LoginDtls.ClientID
left join AdminRegisteredClients As ARC on ARC.ClientAccountID=LoginDtls.ClientID
WHERE UserLoginID='$user_name' and UserPassword='$user_password'";  
 
 

$StmtLogin=$iconn->query($SqlLogin);
$RowLoginData=$StmtLogin->fetch_array();

if($StmtLogin->num_rows==1){
	
$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
		$CurrentDateTime=$dateTime->format("Y-m-d  H:i:s"); 
		$UserID=$RowLoginData["UserID"];
		$Status=$RowLoginData["Status"];
		if($Status==1){
		
	
		$sql_update="UPDATE AdminLoginDetails SET `LastLogin`='$CurrentDateTime'
		WHERE `UserID`='$UserID'";
		
		$up=$iconn->query($sql_update);
	
	$_SESSION["UserID"]=$RowLoginData["UserID"];
	$_SESSION["UserName"]=$RowLoginData["UserName"];
	 $_SESSION["AccessType"]=$RowLoginData["AccessType"]; 
	$_SESSION["DeptID"]=$RowLoginData["DeptID"];
	$_SESSION["SubDeptID"]=$RowLoginData["SubDeptID"];
	$_SESSION["UnitID"]=$RowLoginData["UnitID"];
	$_SESSION["IsDev"]=$RowLoginData["IsDev"];
	$_SESSION["ClientID"]=$RowLoginData["ClientID"];
	$_SESSION["ClientName"]=$RowLoginData["ClientName"];
	
	
	$_SESSION["DbHost"]=$RowLoginData["DbHost"];
	$_SESSION["DbName"]=$RowLoginData["DbName"];
	$_SESSION["DbUserName"]=$RowLoginData["DbUserName"];
	$_SESSION["DbPassword"]=$RowLoginData["DbPassword"];
	$_SESSION["StartYear"]=$RowLoginData["StartYear"];
	
	 $_SESSION["IncludePath"]=$RowLoginData["CurrentClientPath"].$RowLoginData["CurrentDirectory"];
	$_SESSION["ClientPath"]=$RowLoginData["CurrentServerUrl"].$RowLoginData["CurrentClientPath"].$RowLoginData["CurrentDirectory"];  
	$_SESSION["SideBarPath"]=$RowLoginData["CurrentServerUrl"].$RowLoginData["CurrentClientPath"]; 
	
	include ($_SERVER['DOCUMENT_ROOT']."/includes/Authenticater.php");
	
$ClientLID=$RowLoginData["ClientID"];

//ROBO
 
 $SqlValidate="SELECT * from AdminRegisteredClients WHERE ClientAccountID='$ClientLID'  order by ClientID Desc"; 
$StmtValidate=$iconn->query($SqlValidate);
$ValidateRow=$StmtValidate->fetch_array();

//print_r($ValidateRow); 
//exit;
	

	$CurrentDate = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
	  $CurrentDate=$CurrentDate->format("Y-m-d"); 
 	$ExpiryDate=$ValidateRow['ExpiryDate']; 
	 strtotime($CurrentDate); echo "<br>";
	// exit;
//ECHO	 strtotime($ExpiryDate); exit;



if(strtotime($CurrentDate) > strtotime($ExpiryDate) ){
//if(strtotime($ExpiryDate)<strtotime($CurrentDate) ){
	

		header('Location: includes/Validate');
		
	}ELSE{
		
	header('location:'.$_SESSION["ClientPath"]);	
		
	}

//ROBO
	
	
}else {
		echo "<script>alert('UserId or Passsord is Incorrect')</script>";
}	
}else{
	echo "<script>alert('UserId or Passsord is Incorrect')</script>";
}
}

?>


<!doctype html>
<html lang="en">

    <head>
        
       <?php
      
	  include ($IncludesPath."title-meta.php");
	   include ($IncludesPath."head-css.php");
		?>


    </head>

    <body class="auth-body-bg">
        <div class="home-btn d-none d-sm-block">
            <a href="index"><i class="mdi mdi-home-variant h2 text-white"></i></a>
        </div>
        <div>
            <div class="container-fluid p-0">
                <div class="row no-gutters">
                    <div class="col-lg-4">
                        <div class="authentication-page-content p-4 d-flex align-items-center min-vh-100">
                            <div class="w-100">
                                <div class="row justify-content-center">
                                    <div class="col-lg-9">
                                        <div>
                                            <div class="text-center">
                                                <div>
                                                    <a href="#" class="logo"><img src="<?php echo $AppLogo ?>" height="100" width="100" alt="logo"></a>
                                                </div>
    
                                                <h4 class="font-size-18 mt-4">Welcome Back !</h4>
                                                <p class="text-muted">Sign in to continue to <?php echo $ClientName ?>.</p>
                                            </div>

                                            <div class="p-2 mt-5">
                                                <form class="form-horizontal" method="post" >
                    
                                                    <div class="form-group auth-form-group-custom mb-4">
                                                        <i class="ri-user-2-line auti-custom-input-icon"></i>
                                                        <label for="username">Username</label>
                                                        <input required type="text" 
														class="form-control" id="username" name="username" placeholder="Enter username">
                                                    </div>
                            
                                                    <div class="form-group auth-form-group-custom mb-4">
                                                        <i class="ri-lock-2-line auti-custom-input-icon"></i>
                                                        <label for="userpassword">Password</label>
                                                        <input required type="password" class="form-control" id="userpassword" name="userpassword" placeholder="Enter password">
                                                    </div>
                            
                                                   <!-- <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" id="customControlInline">
                                                        <label class="custom-control-label" for="customControlInline">Remember me</label>
                                                    </div> -->

                                                    <div class="mt-4 text-center">
                                                        <button class="btn btn-primary w-md waves-effect waves-light" type="submit" name="submit">Log In</button>
                                                    </div>

                                                   <!-- <div class="mt-4 text-center">
                                                        <a href="auth-recoverpw.html" class="text-muted"><i class="mdi mdi-lock mr-1"></i> Forgot your password?</a>
                                                    </div> -->
                                                </form>
                                            </div>

                                            <div class="mt-5 text-center">
                                               <!-- <p>Don't have an account ? <a href="auth-register.html" class="font-weight-medium text-primary"> Register </a> </p> -->
                                                <p><?php echo $CopyRight ?> <i class="mdi mdi-heart text-danger"></i> </p>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div >
						
                            <div class="bg-overlay" style="background: #37517e;">
							<img src="<?php echo $ClientBg ?>" height="100%" width="100%" alt="logo">
							</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
		<?php 
		include ($IncludesPath."vendor-scripts.php");
       ?>
        <script src="<?php echo $BaseDist?>assets/js/app.js"></script>

    </body>
</html>
