<?php 


ob_start();

    session_start();
    
 include_once 'config/config.php';

 

    /* Get the client IP */
    function get_client_ip() {
        $ip = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ip = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ip = $_SERVER['REMOTE_ADDR'];
        else
            $ip = 'UNKNOWN';
        return $ip;
    }
    
    $ip = explode(',', get_client_ip())[0];


function LoginAPI($api, $method, $headerContent, $bodyContent)
{

    $url = BASEURL . $api;

    $curl = curl_init($url);

    $headers = array("Content-Type: application/json");
    
    if (!empty($headerContent)) {
        $headers = array_merge($headerContent, $headers);
    }

    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers); // Headers Content    

    if (isset($bodyContent) && !empty($bodyContent)) {
        $jsonBody = json_encode($bodyContent);
        $jsonBody = ltrim(rtrim($jsonBody, ']'), '[');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonBody); // Body Content
    }

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    switch ($method) {
        case "GET":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
            break;
        case "POST":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            break;
        case "DELETE":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
            break;
    }
    $response = curl_exec($curl);
    $data = json_decode($response);

    // Check for 404 (file not found). 
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    // Check the HTTP Status code
    switch ($httpCode) {
        case 200:
            $error_status = "200: Success";
            return ($data);
            break;
        case 404:
            $error_status = "404: API Not found";
            break;
        case 500:
            $error_status = "500: servers replied with an error.";
            break;
        case 502:
            $error_status = "502: servers may be down or being upgraded. Hopefully they'll be OK soon!";
            break;
        case 503:
            $error_status = "503: service unavailable. Hopefully they'll be OK soon!";
            break;
        default:
            $error_status = "Undocumented error: " . $httpCode . " : " . curl_error($curl);
            break;
    }
    curl_close($curl);
    echo $error_status;
    die;
}



if(isset($_POST['submit'])){
    ob_start();
    $username = trim($_POST["txtUsername"]);
    $password = trim($_POST["txtPassword"]);
    
    $api = API_LOGIN;
    
    $bodyContent = array(
        "LoginID"=>$username,
        "Password"=>$password,
        "IpAddress"=>$ip);

    $resLogin = LoginAPI($api, "POST", "", $bodyContent);

    $resData = $resLogin->Data->Data;
    // $_SESSION["Directory"] = $resData->Directory;
    $_SESSION["Directory"] = $resData->CurrentClientPath.$resData->Directory;
    $CurrentClientPath ='';//Cause Same For All Clients
    $_SESSION["Token"] = $resData->Token;

    $_SESSION["ClientName"]=$resData->ClientName;
    $_SESSION["UserID"]=$resData->AuthUserID;
    $_SESSION["UserName"]=$resData->AuthUserName;

    $_SESSION["AccessType"]=$resData->AuthAccessType;
    $_SESSION["UnitID"]=$resData->UnitID;
    $_SESSION["IsDev"]=$resData->IsDev;
    $_SESSION["UserRoleID"] = $resData->AuthUserRoleID;
    $_SESSION["DeptID"] = $resData->DeptID;
    $_SESSION["ShortName"] = $resData->ShortName;
    $_SESSION["ClientID"] = $resData->ClientID;
    $_SESSION["StartYear"] = $resData->StartYear;

    $_SESSION["CurrentClientPath"] = $resData->CurrentClientPath;
    $_SESSION["AuthUserPassword"] = $resData->AuthUserPassword;
    $_SESSION["AuthUserStatus"] = $resData->AuthUserStatus;
    $_SESSION["SessionTime"] = $resData->SessionTime;
    $_SESSION["TokenTime"] = $resData->TokenTime;

    $_SESSION["Error"] = $resData->Error;

    if(isset($_SESSION["Error"]) && !empty($_SESSION["Error"])){
        header('location:login');
    }else{
        header('location:' . $_SESSION["Directory"]);
    }
    

    // $_SESSION["Directory"] = $Directory;
    // var_dump($resData);



    // $Directory=strtolower($Directory);

   /* if($Directory=='sales/'){
        $Directory='exports';
    }

    */

    }
?>

<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>Login | SilverLight</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesdesign" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/silverlight_logo.png">

    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

</head>

<body class="auth-body-bg">
    <div>
        <div class="container-fluid p-0">
            <div class="row g-0">
                <div class="col-lg-4">
                    <div class="authentication-page-content p-4 d-flex align-items-center min-vh-100">
                        <div class="w-100">
                            <div class="row justify-content-center">
                                <div class="col-lg-9">
                                    <div>
                                        <div class="text-center">
                                            <div>
                                                <a href="index.html" class="">
                                                    <img src="assets/images/silverlight_logo.png" alt="" height="90"
                                                        class="auth-logo logo-dark mx-auto">
                                                    <!-- <img src="assets/images/logo-light.png" alt="" height="20" class="auth-logo logo-light mx-auto"> -->
                                                </a>
                                            </div>

                                            <h4 class="font-size-18 mt-4">Welcome Back !</h4>
                                            <p class="text-muted">Sign in to continue to SilverLight.</p>
                                        </div>

                                        <div class="p-2 mt-5">
                                            <?php 
                                                    if(isset($_SESSION["Error"]) && !empty($_SESSION["Error"])) { 
                                                        $_SESSION["Error"] = "";
                                                        echo '<div class="error">'.$_SESSION["Error"].'</div>';
                                                    } 
                                                 ?>
                                            <form method="post">

                                                <div class="mb-3 auth-form-group-custom mb-4">
                                                    <i class="ri-user-2-line auti-custom-input-icon"></i>
                                                    <label for="txtUsername" class="fw-semibold">Username</label>
                                                    <input type="text" class="form-control" name="txtUsername"
                                                        id="txtUsername" placeholder="Enter username">
                                                </div>

                                                <div class="mb-3 auth-form-group-custom mb-4">
                                                    <i class="ri-lock-2-line auti-custom-input-icon"></i>
                                                    <label for="txtPassword">Password</label>
                                                    <input type="password" class="form-control" name="txtPassword"
                                                        id="txtPassword" placeholder="Enter password">
                                                </div>

                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input"
                                                        id="customControlInline">
                                                    <label class="form-check-label" for="customControlInline">Remember
                                                        me</label>
                                                </div>

                                                <div class="mt-4 text-center">
                                                    <!-- <button class="btn btn-primary w-md waves-effect waves-light" type="submit">Log In</button> -->
                                                    <button type="submit" name="submit" id="submit"
                                                        class="btn btn-primary w-md waves-effect waves-light"
                                                        type="submit">Log In</button>
                                                </div>

                                                <div class="mt-4 text-center">
                                                    <a href="#" class="text-muted"><i class="mdi mdi-lock me-1"></i>
                                                        Forgot your password?</a>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="mt-5 text-center">
                                            <!-- <p>Don't have an account ? <button name="submit" id="submit" class="fw-medium text-primary"> Register </submit> </p> -->
                                            <p>© <script>
                                                document.write(new Date().getFullYear())
                                                </script> SilverLight</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="authentication-bg" style="background-image: url('assets/images/ship.jpg');">
                        <div class="bg-overlay" style="opacity: .5"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- JAVASCRIPT -->
    <script src="assets/libs/jquery/jquery.min.js"></script>
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/libs/metismenu/metisMenu.min.js"></script>
    <script src="assets/libs/simplebar/simplebar.min.js"></script>
    <script src="assets/libs/node-waves/waves.min.js"></script>

    <script src="assets/js/app.js"></script>

    <script>

    </script>

</body>

</html>