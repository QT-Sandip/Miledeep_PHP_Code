<?php 

include_once ROOT.'config/config.php';

function BackgroundApiCall($api, $method, $headerContent, $bodyContent) {
    $url = BASEURL . $api;

    // Send immediate success to client
    header('HTTP/1.1 200 OK');
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Request is being processed']);
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request(); // Finish the response to the client
    }

    // Proceed with background API call
    $curl = curl_init($url);
    $Token = 'Bearer ' . $_SESSION["Token"];
    $headers = ["Authorization:" . $Token];

    if (!empty($headerContent)) {
        $headers = array_merge($headerContent, $headers);
    }

    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    if (isset($bodyContent) && !empty($bodyContent)) {
        $jsonBody = json_encode($bodyContent);           
        $jsonBody = ltrim(rtrim($jsonBody, ']'), '[');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonBody);
    }

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
    curl_setopt($curl, CURLOPT_TIMEOUT_MS, 100);

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
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if (json_decode($response)) {
        $data = json_decode($response); 
    } else {
        $data = null;
        $data['Error'] = $response;
        $response = json_encode($data);
        $data = json_decode($response);
    }

    curl_close($curl);

    // Log the response or handle errors here
    // For example, log to a file
    // file_put_contents(ROOT . 'logs/api_response.log', json_encode(['url' => $url, 'response' => $response], JSON_PRETTY_PRINT), FILE_APPEND);
}

?>
