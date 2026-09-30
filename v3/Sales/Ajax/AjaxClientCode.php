<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$TypeProduct = isset($_POST['TypeProduct']) ? trim($_POST['TypeProduct']) : '';
$FreezingType = isset($_POST['FreezingType']) ? trim($_POST['FreezingType']) : '';
$PreparationType = isset($_POST['PreparationType']) ? trim($_POST['PreparationType']) : '';
$VarietyID = isset($_POST['VarietyID']) ? intval($_POST['VarietyID']) : 0;

$Data = array('Error' => null, 'Code' => '');

$normalizedFreezingType = normalizeSupcFreezingType($FreezingType);
$codeRule = getDeviSupcCodeRule($TypeProduct, $normalizedFreezingType);

if ($TypeProduct == '' || $normalizedFreezingType == '' || $PreparationType == '') {
    $Data['Error'] = 'Invalid Combination';
    echo json_encode($Data);
    exit;
}

if (!isValidSupcCombination($iconn, $TypeProduct, $normalizedFreezingType, $PreparationType)) {
    $Data['Error'] = 'InValid Combination for  ' . $normalizedFreezingType . ' and ' . $TypeProduct . ' and ' . $PreparationType;
    echo json_encode($Data);
    exit;
}

if ($codeRule['CodeStart'] == 'NA') {
    $Data['Error'] = 'InValid Combination for  ' . $normalizedFreezingType . ' and ' . $TypeProduct . ' and ' . $PreparationType;
    echo json_encode($Data);
    exit;
}

if ($VarietyID > 0) {
    $existingSql = "SELECT TypeProduct, FreezingType, PreparationType, DeviSUPC FROM ScrmPIVarietyList WHERE VarietyID='$VarietyID'";
    $existingStmt = $iconn->query($existingSql);
    $existingRow = $existingStmt ? $existingStmt->fetch_array() : null;

    if ($existingRow) {
        $existingFreezingType = normalizeSupcFreezingType($existingRow['FreezingType']);
        if ($existingRow['TypeProduct'] == $TypeProduct && $existingFreezingType == $normalizedFreezingType && $existingRow['PreparationType'] == $PreparationType && isDeviSupcInRuleRange($existingRow['DeviSUPC'], $codeRule)) {
            $Data['Code'] = $existingRow['DeviSUPC'];
            echo json_encode($Data);
            exit;
        }
    }
}

$Data['Code'] = generateNextDeviSupc($iconn, $codeRule, $VarietyID);
if ($Data['Code'] === '') {
    $Data['Error'] = 'Unable to generate Devi SUPC for this combination';
}
echo json_encode($Data);

function normalizeSupcFreezingType($FreezingType)
{
    if ($FreezingType == 'SIQF') {
        return 'IQF';
    }
    return $FreezingType;
}

function isValidSupcCombination($iconn, $TypeProduct, $FreezingType, $PreparationType)
{
    $TypeProduct = mysqli_real_escape_string($iconn, $TypeProduct);
    $FreezingType = mysqli_real_escape_string($iconn, $FreezingType);
    $PreparationType = mysqli_real_escape_string($iconn, $PreparationType);

    $SqlClientCode = "SELECT 1 FROM ScrmPreparationMaster
        WHERE ShortCode='$PreparationType'
          AND ItemType='$TypeProduct'
          AND FType='$FreezingType'
        LIMIT 1";
    $StmtProduction = $iconn->query($SqlClientCode);
    return ($StmtProduction && $StmtProduction->num_rows > 0);
}

function getDeviSupcCodeRule($TypeProduct, $FreezingType)
{
    $rule = array('CodeStart' => 'NA', 'Where' => '');

    if ($TypeProduct == 'COOKED') {
        $rule['CodeStart'] = 3;
        $rule['Where'] = "WHERE FreezingType='$FreezingType' AND TypeProduct='COOKED'";
    } else if ($TypeProduct == 'BLANCHED') {
        $rule['CodeStart'] = 4;
        $rule['Where'] = "WHERE FreezingType='$FreezingType' AND TypeProduct='BLANCHED'";
    } else if ($FreezingType == 'IQF') {
        $rule['CodeStart'] = 2;
        $rule['Where'] = "WHERE FreezingType='IQF' AND TypeProduct='$TypeProduct'";
    } else if ($FreezingType == 'BLOCK') {
        $rule['CodeStart'] = 1;
        $rule['Where'] = "WHERE FreezingType='BLOCK' AND TypeProduct='$TypeProduct'";
    }

    return $rule;
}

function generateNextDeviSupc($iconn, $codeRule, $excludeVarietyID = 0)
{
    $where = $codeRule['Where'];
    $prefix = intval($codeRule['CodeStart']);
    $excludeSql = $excludeVarietyID > 0 ? " AND VarietyID <> '$excludeVarietyID'" : '';
    $SqlClientCode = "SELECT MAX(CAST(SUBSTRING(CAST(DeviSUPC AS CHAR), 2) AS UNSIGNED)) AS LastSequence
        FROM ScrmPIVarietyList
        $where
          AND CAST(DeviSUPC AS CHAR) REGEXP '^{$prefix}[0-9]{3,}$'
          $excludeSql";
    $StmtProduction = $iconn->query($SqlClientCode);
    $RowData = $StmtProduction ? $StmtProduction->fetch_array() : array('LastSequence' => 0);
    $sequence = intval($RowData['LastSequence']) + 1;

    if ($sequence <= 0) {
        $sequence = 1;
    }

    $Code = formatDeviSupcCode($prefix, $sequence);

    while (isDeviSUPCExists($iconn, $Code, $excludeVarietyID)) {
        $sequence++;
        $Code = formatDeviSupcCode($prefix, $sequence);
    }

    return $Code;
}

function formatDeviSupcCode($prefix, $sequence)
{
    return intval((string)$prefix . str_pad((string)$sequence, 3, '0', STR_PAD_LEFT));
}

function isDeviSupcInRuleRange($DeviSUPC, $codeRule)
{
    $Code = trim((string)$DeviSUPC);
    $prefix = (string)$codeRule['CodeStart'];

    if ($Code == '' || strlen($Code) < 4 || substr($Code, 0, 1) !== $prefix) {
        return false;
    }

    $sequence = substr($Code, 1);
    return ctype_digit($sequence) && intval($sequence) > 0;
}

function isDeviSUPCExists($iconn, $DeviSUPC, $excludeVarietyID = 0)
{
    $excludeSql = $excludeVarietyID > 0 ? " AND VarietyID <> '$excludeVarietyID'" : '';
    $SqlCheck = "SELECT COUNT(*) AS count FROM ScrmPIVarietyList WHERE DeviSUPC='$DeviSUPC' $excludeSql";
    $StmtCheck = $iconn->query($SqlCheck);
    $RowCheck = $StmtCheck ? $StmtCheck->fetch_array() : array('count' => 0);
    return ($RowCheck['count'] > 0);
}
?>