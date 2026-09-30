<?php
$PageTitle = "Rebuild Variety Devi SUPC";
$PageTitleFolder = "Sales";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$run = isset($_GET['run']) && $_GET['run'] == '1' && isset($_GET['confirm']) && $_GET['confirm'] == 'UPDATE_DEVI_SUPC';
$rows = array();
$invalidRows = array();
$validRows = array();
$usedCodes = array();
$nextByScope = array();
$updatedCount = 0;
$unchangedCount = 0;
$willUpdateCount = 0;
$updateQueue = array();

$sql = "SELECT VarietyID, TypeProduct, FreezingType, PreparationType, DeviSUPC, ProductDescription FROM ScrmPIVarietyList ORDER BY VarietyID ASC";
$stmt = $iconn->query($sql);
while ($stmt && $row = $stmt->fetch_array(MYSQLI_ASSOC)) {
    $row['NormalizedFreezingType'] = normalizeSupcFreezingType($row['FreezingType']);
    $row['Rule'] = getDeviSupcCodeRule($row['TypeProduct'], $row['NormalizedFreezingType']);
    $row['IsValid'] = isValidSupcCombination($iconn, $row['TypeProduct'], $row['NormalizedFreezingType'], $row['PreparationType']) && $row['Rule']['CodeStart'] != 'NA';

    if ($row['IsValid']) {
        $validRows[] = $row;
    } else {
        $invalidRows[] = $row;
        if ($row['DeviSUPC'] !== '' && $row['DeviSUPC'] !== null) {
            $usedCodes[(string)$row['DeviSUPC']] = true;
        }
    }
}

foreach ($validRows as $row) {
    $newCode = generateNextRebuildCode($row['Rule'], $nextByScope, $usedCodes);
    $status = ((string)$row['DeviSUPC'] === (string)$newCode) ? 'UNCHANGED' : 'WILL UPDATE';

    if ($status === 'WILL UPDATE') {
        $willUpdateCount++;
        $updateQueue[] = array(
            'VarietyID' => intval($row['VarietyID']),
            'NewDeviSUPC' => $newCode
        );
    } else if ($status === 'UNCHANGED') {
        $unchangedCount++;
    }

    $rows[] = array(
        'VarietyID' => $row['VarietyID'],
        'TypeProduct' => $row['TypeProduct'],
        'FreezingType' => $row['FreezingType'],
        'NormalizedFreezingType' => $row['NormalizedFreezingType'],
        'PreparationType' => $row['PreparationType'],
        'OldDeviSUPC' => $row['DeviSUPC'],
        'NewDeviSUPC' => $newCode,
        'Status' => $status,
        'ProductDescription' => $row['ProductDescription']
    );
}

if ($run && count($updateQueue) > 0) {
    $resultByVariety = array();

    foreach ($updateQueue as $item) {
        $varietyId = intval($item['VarietyID']);
        $tempCode = 900000 + $varietyId;
        $tempSql = "UPDATE ScrmPIVarietyList SET DeviSUPC='$tempCode' WHERE VarietyID='$varietyId'";
        if (!$iconn->query($tempSql)) {
            $resultByVariety[$varietyId] = 'ERROR: ' . mysqli_error($iconn);
        }
    }

    foreach ($updateQueue as $item) {
        $varietyId = intval($item['VarietyID']);
        if (isset($resultByVariety[$varietyId])) {
            continue;
        }

        $newCodeEsc = mysqli_real_escape_string($iconn, $item['NewDeviSUPC']);
        $updateSql = "UPDATE ScrmPIVarietyList SET DeviSUPC='$newCodeEsc' WHERE VarietyID='$varietyId'";
        if ($iconn->query($updateSql)) {
            $resultByVariety[$varietyId] = 'UPDATED';
            $updatedCount++;
        } else {
            $resultByVariety[$varietyId] = 'ERROR: ' . mysqli_error($iconn);
        }
    }

    foreach ($rows as $rowIndex => $rowData) {
        $varietyId = intval($rowData['VarietyID']);
        if (isset($resultByVariety[$varietyId])) {
            $rows[$rowIndex]['Status'] = $resultByVariety[$varietyId];
        }
    }
}
foreach ($invalidRows as $row) {
    $rows[] = array(
        'VarietyID' => $row['VarietyID'],
        'TypeProduct' => $row['TypeProduct'],
        'FreezingType' => $row['FreezingType'],
        'NormalizedFreezingType' => $row['NormalizedFreezingType'],
        'PreparationType' => $row['PreparationType'],
        'OldDeviSUPC' => $row['DeviSUPC'],
        'NewDeviSUPC' => '',
        'Status' => 'SKIPPED - INVALID COMBINATION',
        'ProductDescription' => $row['ProductDescription']
    );
}

function normalizeSupcFreezingType($FreezingType)
{
    return ($FreezingType == 'SIQF') ? 'IQF' : $FreezingType;
}

function isValidSupcCombination($iconn, $TypeProduct, $FreezingType, $PreparationType)
{
    $TypeProduct = mysqli_real_escape_string($iconn, $TypeProduct);
    $FreezingType = mysqli_real_escape_string($iconn, $FreezingType);
    $PreparationType = mysqli_real_escape_string($iconn, $PreparationType);
    $sql = "SELECT 1 FROM ScrmPreparationMaster WHERE ShortCode='$PreparationType' AND ItemType='$TypeProduct' AND FType='$FreezingType' LIMIT 1";
    $stmt = $iconn->query($sql);
    return ($stmt && $stmt->num_rows > 0);
}

function getDeviSupcCodeRule($TypeProduct, $FreezingType)
{
    if ($TypeProduct == 'COOKED') {
        return array('CodeStart' => 3, 'Scope' => 'COOKED|' . $FreezingType);
    }
    if ($TypeProduct == 'BLANCHED') {
        return array('CodeStart' => 4, 'Scope' => 'BLANCHED|' . $FreezingType);
    }
    if ($FreezingType == 'IQF') {
        return array('CodeStart' => 2, 'Scope' => $TypeProduct . '|IQF');
    }
    if ($FreezingType == 'BLOCK') {
        return array('CodeStart' => 1, 'Scope' => $TypeProduct . '|BLOCK');
    }
    return array('CodeStart' => 'NA', 'Scope' => 'NA');
}

function generateNextRebuildCode($rule, &$nextByScope, &$usedCodes)
{
    $scope = $rule['Scope'];
    $prefix = intval($rule['CodeStart']);

    if (!isset($nextByScope[$scope])) {
        $nextByScope[$scope] = 1;
    }

    $sequence = $nextByScope[$scope];
    $code = formatDeviSupcCode($prefix, $sequence);
    while (isset($usedCodes[(string)$code])) {
        $sequence++;
        $code = formatDeviSupcCode($prefix, $sequence);
    }

    $usedCodes[(string)$code] = true;
    $nextByScope[$scope] = $sequence + 1;
    return $code;
}

function formatDeviSupcCode($prefix, $sequence)
{
    return intval((string)$prefix . str_pad((string)$sequence, 3, '0', STR_PAD_LEFT));
}
?>
<!doctype html>
<html>
<head>
    <title>Rebuild Variety Devi SUPC</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; padding: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
        .warn { color: #b45309; font-weight: bold; }
        .ok { color: #166534; font-weight: bold; }
        .bad { color: #991b1b; font-weight: bold; }
    </style>
</head>
<body>
    <h3>Rebuild Variety Devi SUPC</h3>
    <p><b>Mode:</b> <?php echo $run ? '<span class="bad">UPDATE EXECUTED</span>' : '<span class="warn">DRY RUN ONLY</span>'; ?></p>
    <p>To execute updates, open: <code>UpdateAllVarietyDeviSupcScript.php?run=1&amp;confirm=UPDATE_DEVI_SUPC</code></p>
    <p><b>Updated:</b> <?php echo $updatedCount; ?> | <b>Will update:</b> <?php echo $willUpdateCount; ?> | <b>Unchanged:</b> <?php echo $unchangedCount; ?> | <b>Invalid skipped:</b> <?php echo count($invalidRows); ?></p>
    <table>
        <thead>
            <tr>
                <th>VarietyID</th>
                <th>Type Product</th>
                <th>Freezing</th>
                <th>Normalized Freezing</th>
                <th>Preparation</th>
                <th>Old SUPC</th>
                <th>New SUPC</th>
                <th>Status</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['VarietyID']); ?></td>
                    <td><?php echo htmlspecialchars($row['TypeProduct']); ?></td>
                    <td><?php echo htmlspecialchars($row['FreezingType']); ?></td>
                    <td><?php echo htmlspecialchars($row['NormalizedFreezingType']); ?></td>
                    <td><?php echo htmlspecialchars($row['PreparationType']); ?></td>
                    <td><?php echo htmlspecialchars($row['OldDeviSUPC']); ?></td>
                    <td><?php echo htmlspecialchars($row['NewDeviSUPC']); ?></td>
                    <td><?php echo htmlspecialchars($row['Status']); ?></td>
                    <td><?php echo htmlspecialchars($row['ProductDescription']); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>