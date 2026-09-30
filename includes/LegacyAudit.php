<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/ApiV2/Global/Audit/CentralAuditLogger.php';

if (!function_exists('legacyAuditGetModuleName')) {
    function legacyAuditGetModuleName()
    {
        $scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
        if ($scriptName && preg_match('#/v3/([^/]+)/#i', str_replace('\\', '/', $scriptName), $matches)) {
            return $matches[1];
        }

        return 'LegacyV3';
    }
}

if (!function_exists('legacyAuditGetPdoConnection')) {
    function legacyAuditGetPdoConnection()
    {
        static $legacyAuditPdo = null;

        if ($legacyAuditPdo instanceof PDO) {
            return $legacyAuditPdo;
        }

        global $DbHost, $DbName, $DbUserName, $DbPassword;

        try {
            $legacyAuditPdo = new PDO(
                'mysql:host=' . $DbHost . ';dbname=' . $DbName,
                $DbUserName,
                $DbPassword
            );
            $legacyAuditPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $legacyAuditPdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $legacyAuditPdo->exec('set names utf8');
        } catch (Exception $exception) {
            error_log('Legacy audit PDO connection failed: ' . $exception->getMessage());
            return null;
        }

        return $legacyAuditPdo;
    }
}

if (!function_exists('legacyAuditGetLogger')) {
    function legacyAuditGetLogger($moduleName = null)
    {
        static $loggers = array();

        if ($moduleName === null || $moduleName === '') {
            $moduleName = legacyAuditGetModuleName();
        }

        if (isset($loggers[$moduleName])) {
            return $loggers[$moduleName];
        }

        $pdo = legacyAuditGetPdoConnection();
        if (!($pdo instanceof PDO)) {
            return null;
        }

        $loggers[$moduleName] = new CentralAuditLogger($pdo, $moduleName);
        return $loggers[$moduleName];
    }
}

if (!function_exists('legacyAuditBuildRequestBody')) {
    function legacyAuditResolveClientIp($requestBody = array())
    {
        $requestBody = getrequestbody();
        $candidates = array();
        if (is_array($requestBody)) {
            $requestKeys = array(
                'IPAddress',
                'IpAddress',
                'ClientIP',
                'ClientIp',
                'ipaddress',
                'ip',
            );
            foreach ($requestKeys as $key) {
                if (isset($requestBody[$key]) && !is_array($requestBody[$key]) && !is_object($requestBody[$key])) {
                    $candidates[] = $requestBody[$key];
                }
            }

            if (isset($requestBody['AuthPayload']) && is_array($requestBody['AuthPayload'])) {
                foreach ($requestKeys as $key) {
                    if (isset($requestBody['AuthPayload'][$key]) && !is_array($requestBody['AuthPayload'][$key]) && !is_object($requestBody['AuthPayload'][$key])) {
                        $candidates[] = $requestBody['AuthPayload'][$key];
                    }
                }
            }
        }

        $serverKeys = array(
            'HTTP_CF_CONNECTING_IP',
            'HTTP_TRUE_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_CLIENT_IP',
            'HTTP_X_REAL_IP',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        );
        foreach ($serverKeys as $key) {
            if (isset($_SERVER[$key])) {
                $candidates[] = $_SERVER[$key];
            }
        }

        foreach ($candidates as $candidate) {
            if (!is_string($candidate) && !is_numeric($candidate)) {
                continue;
            }

            foreach (preg_split('/,/', (string) $candidate) as $part) {
                $part = trim($part);
                if ($part === '') {
                    continue;
                }

                if (stripos($part, 'for=') === 0) {
                    $part = trim(substr($part, 4), " \t\n\r\0\x0B\"'[]");
                }

                if (filter_var($part, FILTER_VALIDATE_IP)) {
                    return $part;
                }
            }
        }

        return null;
    }
}

if (!function_exists('legacyAuditResolveBrowserInfo')) {
    function legacyAuditResolveBrowserInfo($requestBody = array())
    {
        $requestBody = getrequestbody();
        $candidates = array();
        if (is_array($requestBody)) {
            $requestKeys = array(
                'BrowserInfo',
                'Browser',
                'BrowserName',
                'UserAgent',
                'userAgent',
                'browserinfo',
            );
            foreach ($requestKeys as $key) {
                if (isset($requestBody[$key]) && !is_array($requestBody[$key]) && !is_object($requestBody[$key])) {
                    $candidates[] = $requestBody[$key];
                }
            }

            if (isset($requestBody['AuthPayload']) && is_array($requestBody['AuthPayload'])) {
                foreach ($requestKeys as $key) {
                    if (isset($requestBody['AuthPayload'][$key]) && !is_array($requestBody['AuthPayload'][$key]) && !is_object($requestBody['AuthPayload'][$key])) {
                        $candidates[] = $requestBody['AuthPayload'][$key];
                    }
                }
            }
        }

        $serverKeys = array(
            'HTTP_SEC_CH_UA',
            'HTTP_USER_AGENT',
        );
        foreach ($serverKeys as $key) {
            if (isset($_SERVER[$key])) {
                $candidates[] = $_SERVER[$key];
            }
        }

        foreach ($candidates as $candidate) {
            if ($candidate === null || is_array($candidate) || is_object($candidate)) {
                continue;
            }

            $candidate = trim((string) $candidate);
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }
}

if (!function_exists('legacyAuditBuildRequestBody')) {
    function legacyAuditBuildRequestBody($requestBody = array())
    {
        $requestBody = getrequestbody();
        if (!is_array($requestBody)) {
            $requestBody = array();
        }

        if (!empty($_POST) && is_array($_POST)) {
            $requestBody = array_merge($_POST, $requestBody);
        }

        if (isset($_SESSION['UserID']) && !isset($requestBody['UserID'])) {
            $requestBody['UserID'] = $_SESSION['UserID'];
        }

        if (isset($_SESSION['ClientID']) && !isset($requestBody['ClientID'])) {
            $requestBody['ClientID'] = $_SESSION['ClientID'];
        }

        if (!isset($requestBody['IPAddress'])) {
            $requestBody['IPAddress'] = legacyAuditResolveClientIp($requestBody);
        }

        if (!isset($requestBody['BrowserInfo'])) {
            $requestBody['BrowserInfo'] = legacyAuditResolveBrowserInfo($requestBody);
        }

        return $requestBody;
    }
}

if (!function_exists('legacyAuditFetchRowById')) {
    function legacyAuditFetchRowById($tableName, $idField, $recordId, $moduleName = null)
    {
        $logger = legacyAuditGetLogger($moduleName);
        if ($logger === null) {
            return null;
        }

        return $logger->fetchRowById($tableName, $idField, $recordId);
    }
}

if (!function_exists('legacyAuditLogInsertById')) {
    function legacyAuditLogInsertById($tableName, $idField, $recordId, $requestBody = array(), $options = array(), $moduleName = null)
    {
        $requestBody = getrequestbody();
        $logger = legacyAuditGetLogger($moduleName);
        if ($logger === null) {
            return;
        }

        $newRow = $logger->fetchRowById($tableName, $idField, $recordId);
        $requestBody = legacyAuditBuildRequestBody($requestBody);
        if (!isset($options['ModuleName'])) {
            $options['ModuleName'] = $moduleName ? $moduleName : legacyAuditGetModuleName();
        }
        $logger->logInsert($tableName, $recordId, $newRow, $requestBody, $options);
    }
}

if (!function_exists('legacyAuditLogUpdateById')) {
    function legacyAuditLogUpdateById($tableName, $idField, $recordId, $oldRow = null, $requestBody = array(), $options = array(), $moduleName = null)
    {
        $requestBody = getrequestbody();
        $logger = legacyAuditGetLogger($moduleName);
        if ($logger === null) {
            return;
        }

        if ($oldRow === null) {
            $oldRow = $logger->fetchRowById($tableName, $idField, $recordId);
        }

        $newRow = $logger->fetchRowById($tableName, $idField, $recordId);
        $requestBody = legacyAuditBuildRequestBody($requestBody);
        if (!isset($options['ModuleName'])) {
            $options['ModuleName'] = $moduleName ? $moduleName : legacyAuditGetModuleName();
        }
        $logger->logUpdate($tableName, $recordId, $oldRow, $newRow, $requestBody, $options);
    }
}

if (!function_exists('legacyAuditLogDeleteById')) {
    function legacyAuditLogDeleteById($tableName, $idField, $recordId, $oldRow = null, $requestBody = array(), $options = array(), $moduleName = null)
    {
        $requestBody = getrequestbody();
        $logger = legacyAuditGetLogger($moduleName);
        if ($logger === null) {
            return;
        }

        if ($oldRow === null) {
            $oldRow = $logger->fetchRowById($tableName, $idField, $recordId);
        }

        $requestBody = legacyAuditBuildRequestBody($requestBody);
        if (!isset($options['ModuleName'])) {
            $options['ModuleName'] = $moduleName ? $moduleName : legacyAuditGetModuleName();
        }
        $logger->logDelete($tableName, $recordId, $oldRow, $requestBody, $options);
    }
}

if (!function_exists('getrequestbody')) {
    function getrequestbody()
    {
        $ipaddr = '';

        // Direct client IP
        if (isset($_SERVER['REMOTE_ADDR']) &&
            filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
            $ipaddr = $_SERVER['REMOTE_ADDR'];
        }

        // If your server is behind a trusted proxy/load balancer,
        // use X-Forwarded-For instead.
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $forwardedIps = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            foreach ($forwardedIps as $ip) {
                $ip = trim($ip);

                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    $ipaddr = $ip;
                    break;
                }
            }
        }

        // X-Real-IP fallback
        if ($ipaddr === '' && !empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = trim($_SERVER['HTTP_X_REAL_IP']);

            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                $ipaddr = $ip;
            }
        }

        $bodyContent = array(
            'ClientID'    => isset($_SESSION['ClientID']) ? $_SESSION['ClientID'] : '',
            'UserID'      => isset($_SESSION['UserID']) ? $_SESSION['UserID'] : '',
            'IPAddress'   => $ipaddr,
            'BrowserInfo' => isset($_SERVER['HTTP_USER_AGENT'])
                                ? trim($_SERVER['HTTP_USER_AGENT'])
                                : ''
        );

        return $bodyContent;
    }
}

