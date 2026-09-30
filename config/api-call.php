<?php 

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once ROOT.'config/config.php';
require_once __DIR__.'/../ApiV2/Config/Database.php';
$databaseApi = new Database(); 
$connApi = $databaseApi->getAuthConnection();

function resolvecsClientIp()
    {
        $candidates = [
            isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null,
            isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : null,
            isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : null,
            isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
        ];

        foreach ($candidates as $candidate) {
            if ($candidate === null || $candidate === '') {
                continue;
            }

            $parts = explode(',', $candidate);
            foreach ($parts as $part) {
                $ip = trim($part);
                if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return null;
    }

function CallAPI($api, $method, $headerContent, $bodyContent) {
    $start = microtime(true);
    $url = BASEURL.$api;

    $Token = 'Bearer ' . $_SESSION["Token"];

    $headers = array("Content-Type: application/json");
    $headers = array("Authorization:".$Token);

    if(!empty($headerContent)){
        $headers = array_merge($headerContent, $headers);
    }

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);  // Headers Content

    if(isset($bodyContent) && !empty($bodyContent)){
        if ($api != 'HumanResource/BioMetricData') {
            if(!isset($bodyContent['ClientID'])){
                $bodyContent['ClientID'] = $_SESSION['ClientID'];
            }

            $bodyContent['UserID'] = $_SESSION['UserID'];
            $bodyContent['IPAddress'] = resolvecsClientIp();
            $bodyContent['BrowserInfo'] = trim($_SERVER['HTTP_USER_AGENT']);
        }
        $jsonBody = json_encode($bodyContent);

        $jsonBody = ltrim(rtrim($jsonBody, ']'),'[');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonBody); // Body Content
    }
    curl_setopt($curl, CURLOPT_HEADERFUNCTION,
        function($curl, $header) use (&$responseHeaders) {
            $len = strlen($header);
            $headerParts = explode(":", $header, 2);
            if (count($headerParts) < 2) return $len;
            $responseHeaders[strtolower(trim($headerParts[0]))][] = trim($headerParts[1]);
            return $len;
        }
    );
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

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

    // Retry logic for 429 Too Many Requests
    $maxRetries = 3;
    $retryCount = 0;
    do {
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($httpCode == 429) {
            $retryAfter = isset($responseHeaders['retry-after'][0]) ? $responseHeaders['retry-after'][0] : 5;
            sleep((int)$retryAfter);
            $retryCount++;
        } else {
            break;
        }
    } while ($retryCount < $maxRetries);

    $end = microtime(true);
    $responseTimeMs = round(($end - $start) * 1000);
    $responseSize = strlen($response);
    $requestSize = isset($jsonBody) ? strlen($jsonBody) : 0;
    error_log($httpCode);

    if ($httpCode >= 400 || $responseTimeMs > 500) {
        log_api_call([
            'script_name' => $_SERVER['SCRIPT_NAME'] ?$_SERVER['SCRIPT_NAME']: 'unknown',
            'endpoint' => $url,
            'method' => $method,
            'request_body' => json_encode($bodyContent),
            'response_body' => $response,
            'response_headers' => json_encode($responseHeaders ?$responseHeaders: []),
            'http_code' => $httpCode,
            'ip_address' => getLocalIP(),
            'response_time_ms' => $responseTimeMs,
            'response_size' => $responseSize,
            'request_size' => $requestSize
        ]);
    }

    if(json_decode($response)){
    $data = json_decode($response);
    }else{

        $data=null;
        $data['Error'] = $response;
        $response= json_encode($data);
        $data = json_decode($response);

    }

    // Check the HTTP Status code
    switch ($httpCode) {
        case 200:
            $error_status = "200: Success";
            return ($data);
            // break;
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
            $error_status = "Undocumented error : " . $httpCode . " : " . curl_error($curl);
            break;
    }
    curl_close($curl);
    echo $error_status;
    die;
}

function log_api_call($logData){
    global $connApi;
    $insSql = "
        INSERT INTO api_logs
        (script_name, endpoint, method, request_body, response_body, response_headers, http_code, ip_address, response_time_ms, response_size, request_size)
        VALUES (
            '".$logData['script_name']."',
            '".$logData['endpoint']."',
            '".$logData['method']."',
            '".$logData['request_body']."',
            '".$logData['response_body']."',
            '".$logData['response_headers']."',
            '".$logData['http_code']."',
            '".$logData['ip_address']."',
            '".$logData['response_time_ms']."',
            '".$logData['response_size']."',
            '".$logData['request_size']."'
            )
    ";
    if (!$connApi) {
        error_log("DB connection is null. Cannot insert.".$logData['script_name']);
        return; // or exit; depending on context
    }

    try {
        $connApi->query($insSql);
    } catch (PDOException $e) {
        error_log("Insert failed: " . $e->getMessage());
    }
    $connApi = null;
}

function getLocalIP() {
    $hostname = gethostname();
    $ip = gethostbyname($hostname);
    // If it returns the hostname again, it's unresolved
    if ($ip === $hostname) {
        return 'IP not found';
    }
    return $ip;
}
?>
