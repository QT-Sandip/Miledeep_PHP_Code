<?php

include_once __DIR__.'/../config/BaseUrl.php';
$BaseUrlConfig = new BaseUrl;
$BaseUrlConfig = $BaseUrlConfig->getBaseUrl(true);
$BaseUrl = $BaseUrlConfig->protocol."://$BaseUrlConfig->host";

if (!isset($_SESSION['Token']) || empty($_SESSION['Token'])) {

    session_unset();
    session_destroy();
    $workingUrl = $BaseUrl;
    header('Location: '.$workingUrl);
    exit();

}else{

    //Device wise token verify logic
    require_once __DIR__.'/../ApiV2/Config/Database.php';
    $databaseApi = new Database(); 
    
    $connApi = $databaseApi->getAuthConnection();
    if($connApi){
        $checkLoginQry = "Select session_token from AdminLoginDetails where UserID = '".$_SESSION["UserID"]."'";
        $smtChkLgnQry = $connApi->query($checkLoginQry);  
        $rowChkLgn = $smtChkLgnQry->fetch(PDO::FETCH_ASSOC);
    }

    if( isset($_SESSION['session_token']) && ($_SESSION['session_token'] !== $rowChkLgn['session_token']) ){
        if( $_SESSION["UserID"] != 314 ){
            /*session_unset();
            session_destroy();        
            $workingUrl = $BaseUrl;
            echo "<script>
            alert('You\\'ve already signed in on another device.');
            window.location.href = '$workingUrl';
          </script>";
            exit();*/
        }
    }
if($_SESSION['ClientID'] != 'serp'){
    $clientApi = $databaseApi->getConnection($_SESSION['Token']);

    $getAdminSideBarOptionsQry = "Select * from AdminSideBarOptions where 
        AccessType = '".$_SESSION["AccessType"]."' AND
        DeptID = '".$_SESSION["DeptID"]."' AND
        UserRoleID = '".$_SESSION["UserRoleID"]."' 
    ";
    //echo $getAdminSideBarOptionsQry;
    
    $smtAdminSideBarOptionsQry = $clientApi->query($getAdminSideBarOptionsQry);  
    $acceurls = array();
    
    while( $rowAdminSideBarOptions = $smtAdminSideBarOptionsQry->fetch(PDO::FETCH_ASSOC) ){
        $acceurls[] = $rowAdminSideBarOptions['PathUrl'];
    }

    function normalizePath($path) {
        // Remove leading slash and version prefix like /v3/
        $path = preg_replace('#^/?v\d+/#', '', ltrim($path, '/'));

        // Remove .php extension if exists
        $path = preg_replace('#\.php$#i', '', $path);

        // If it's a folder root (e.g., Sales), treat as Sales/index
        if (strpos($path, '/') === false) {
            $path .= '/index';
        } elseif (substr($path, -1) === '/') {
            $path .= 'index';
        }

        return strtolower($path); // case-insensitive match
    }

    function userHasAccessToPage($sessionData, $currentPagePath) {
        if (!isset($sessionData['MenuList']) || !is_array($sessionData['MenuList'])) {
            return false;
        }

        $normalizedPath = normalizePath($currentPagePath);

        foreach ($sessionData['MenuList'] as $menuGroup) {            
            if (isset($menuGroup->SubMenu)) {                
                foreach ($menuGroup->SubMenu as $submenu) {
                    //echo "<div>"; echo normalizePath($submenu->PathUrl). " = ". $normalizedPath; echo "</div>";
                    // Normalize and compare the path
                    //echo "<div>"; echo normalizePath($submenu->PathUrl). " = ". $currentPagePath; echo "</div>";
                    if (trim(strtolower(normalizePath($submenu->PathUrl))) == trim(strtolower($normalizedPath))) {
                        return true;
                    }
                }
                
            }
        }
        //exit;
        return false;
    }

    $sessionData = $_SESSION; 
    $currentPage = $_SERVER['REQUEST_URI']; 
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

    if($_SESSION['ClientID'] != 'serp'){
        if (!$isAjax && !userHasAccessToPage($sessionData, $currentPage) && "/".$_SESSION['IncludePath'] != $currentPage ) { 
                $cur_path = parse_url($currentPage, PHP_URL_PATH); 
                $currentPageBase = basename($cur_path);
                //$currentPageBase = basename($currentPage);
                $accessAllowed = false;
                foreach ($acceurls as $url) {
                    $urlPath = parse_url($url, PHP_URL_PATH);
                    $currentPath = parse_url($currentPage, PHP_URL_PATH);

                    if ($urlPath === false || $currentPath === false) {
                        continue; // skip malformed URLs
                    }

                    // Normalize paths
                    $urlPath = preg_replace('/\.php$/', '', trim($urlPath, '/'));
                    $currentPath = preg_replace('/\.php$/', '', trim($currentPath, '/'));

                    if (strpos($currentPath, $urlPath) !== false) {
                        $accessAllowed = true;
                        break;
                    }
                }

                if (!$accessAllowed && $currentPageBase != 'get-access-urls.php') {

                    $sqlInsetQuery = "INSERT INTO 
                        `AdminSideBarOptions` (
                        `PathUrl`, 
                        `DeptID`, 
                        `UserRoleID`, 
                        `AccessType`
                    ) VALUES (
                        '".htmlspecialchars($currentPageBase)."',                 
                        '".$_SESSION["DeptID"]."', 
                        '".$_SESSION["UserRoleID"]."',
                        '".$_SESSION["AccessType"]."'
                    )";
                    $clientApi->query($sqlInsetQuery);
                    echo "<script>
                        //alert('You\\'re not autherized to access this page.');
                        //window.location.href = '$workingUrl';
                      </script>";
                        //exit();

                }
            }
        }
    }
}

?>