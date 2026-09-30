<?php

//define("BASEURL", "https://serp.silverlights.in/ApiV2/");
define("BASEURL", "https://camaroo.in/ApiV2/");	

define("PostNewLot", "Api?f=createLot"); // AjaxPostSubLots.php 
define("PostSubLots", "Api?f=PostSubLots"); // AjaxPostSubLots.php 
define("PostGrading", "Api?f=postHlGradingReport"); // AjaxPostSubLots.php 
define("PostNewRmBooking", "RmPurchase/RmBooking"); // AjaxPostSubLots.php 
define("PostProfromaPrint", "Exports/ProformaPrint"); // ClearingAgentsList.php 


function CallAPI($api, $method, $headerContent, $bodyContent)
{

    $url = BASEURL . $api;

    $curl = curl_init($url);
     $Token = 'Bearer ' . $_SESSION['Token'];
    //var_dump($url);
    // $Token = 'Bearer eyJhbGdvIjoiSFMyNTYiLCJ0eXBlIjoiSldUIiwiZXhwaXJlIjoxNjkyNDQ4MTIwfQ==.eyJVc2VySUQiOiIxNTEiLCJDbGllbnRJRCI6IlNMUzIzMDAwMiIsInRpbWUiOjE2OTIzNDgxMjB9.OTY3Mjc5MzFhNzVlZTUwZTM3ZDQ2MDM2MDBiNmQxMmViMjhhMjUyMzg3YTg1NTM5MTFlNjJkMjUyMTAyMWMyMg==';
    $headers = array("Content-Type: application/json");
    $headers = array("Authorization: $Token");
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

    /* Check for 404 (file not found). */
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

?>