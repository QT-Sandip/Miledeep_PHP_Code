<?php 

include_once 'config.php';

function CallAPI($api, $method, $headerContent, $bodyContent) {
    
        $url = BASEURL.$api;

   //var_dump($bodyContent);

    $curl = curl_init($url);
    
    $Token = 'Bearer ' . $_SESSION["Token"];

    
    
    $headers = array("Content-Type: application/json");
    $headers = array("Authorization:".$Token);
    
    if(!empty($headerContent)){
        $headers = array_merge($headerContent, $headers);
    }
    
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);  // Headers Content    
    
    if(isset($bodyContent) && !empty($bodyContent)){
        $jsonBody = json_encode($bodyContent);           
        $jsonBody = ltrim(rtrim($jsonBody, ']'),'[');
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
            header("Location: https://camaroo.in/error.php?error=".$error_status."&url=". $url);
            break;
        case 500:
            $error_status = "500: servers replied with an error.";
            header("Location: https://camaroo.in/error.php?error=" . $error_status . "&url=" . $url);
            break;
        case 502:
            $error_status = "502: servers may be down or being upgraded. Hopefully they'll be OK soon!";
            header("Location: https://camaroo.in/error.php?error=" . $error_status . "&url=" . $url);
            break;
        case 503:
            $error_status = "503: service unavailable. Hopefully they'll be OK soon!";
            header("Location: https://camaroo.in/error.php?error=" . $error_status . "&url=" . $url);
            break;
        default:
            $error_status = "Undocumented error: " . $httpCode . " : " . curl_error($curl);
            break;
    }
    curl_close($curl);
    echo $error_status;
    die;
}

?>