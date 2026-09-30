<?php
/**
 * Read database values before using a layout's existing static fallback.
 * Cache only for this connection and order during this report request.
 */
function saimeReportFacts($connection, $poId)
{
    static $cache = array();
    $key = spl_object_hash($connection) . ':' . (int) $poId;
    if (isset($cache[$key])) return $cache[$key];
    $sql = "SELECT SPD.UnitCurrency, SPD.QtyUnit, CM.CurrencyName, CM.CurrenySymbol AS CurrencySymbol, POL.Name AS LoadingPort,
        PROC.ExpName AS ProcessorName, PROC.ExpAddress AS ProcessorAddress,
        EXP.ExpAddress AS ExporterAddress,
        PROC.ExpCountry AS ProcessorCountry, EXP.ExpCountry AS ExporterCountry,
        PROC.ExpPhoneNumber AS ProcessorPhone, PROC.ExpFax AS ProcessorFax, PROC.ExpMailID AS ProcessorEmail,
        ST.StationName, ST.StationAddress,
        SIR.ImporterName, SIR.ImporterAddress, SIR.ImporterCode, BUY.BuyerIRSNumber,
        SNP.NotifyPartyName AS BrokerName, SNP.NPartyAddress AS BrokerAddress,
        SCI.TotalGrossWt, SCI.CInvDate, EXP.LutNumber,
        LM.Name AS ConsolidatorName, LM.Address AS ConsolidatorAddress,
        SF.ExpName AS StuffingName, SF.ExpAddress AS StuffingAddress, ISF.StuffingLocation
        FROM ScrmPoData SPD
        LEFT JOIN ScrmCurrencyMaster CM ON CM.CurrencyCode = SPD.UnitCurrency
        LEFT JOIN AdminExporterDetails PROC ON PROC.ExpID = SPD.ProcessorID
        LEFT JOIN AdminExporterDetails EXP ON EXP.ExpID = SPD.ExporterID
        LEFT JOIN RmProcessingStations ST ON ST.ProcessingStationID = PROC.ProcessingStationID
        LEFT JOIN ScrmPortCities POL ON POL.PortId = SPD.PortofLoading
        LEFT JOIN ScrmImporterOfRecord SIR ON SIR.ImporterID = SPD.ImporterID
        LEFT JOIN ScrmBuyerDetails BUY ON BUY.BuyerID = SPD.BuyerID
        LEFT JOIN ScrmNotifyparty SNP ON SNP.NotifyPartyID = SPD.NotifyPartyID
        LEFT JOIN ScrmCustomInvoices SCI ON SCI.PiID = SPD.PoID
        LEFT JOIN ScrmISFDetails ISF ON ISF.PIID = SPD.PoID
        LEFT JOIN ScrmLogisticsMaster LM ON LM.ID = ISF.ConsolidatorNameAddress
        LEFT JOIN AdminExporterDetails SF ON SF.ExpID = ISF.StuffingLocation
        WHERE SPD.PoID = " . (int) $poId . " ORDER BY SCI.CInvID DESC LIMIT 1";
    $result = $connection->query($sql);
    if (!$result) throw new RuntimeException('Unable to load SAIME report facts.');
    $row = $result->fetch_assoc() ?: array();
    foreach ($row as $field => $value) $row[$field] = trim((string) $value);
    $row['CountryOfOrigin'] = !empty($row['ProcessorCountry']) ? $row['ProcessorCountry'] : (isset($row['ExporterCountry']) ? $row['ExporterCountry'] : '');
    foreach (array('ProcessorName' => 'StationName', 'ProcessorAddress' => 'StationAddress', 'ImporterCode' => 'BuyerIRSNumber') as $field => $fallback) {
        if (empty($row[$field]) && !empty($row[$fallback])) $row[$field] = $row[$fallback];
    }
    $importerIdentity = preg_replace('/[^A-Z0-9]/', '', strtoupper(isset($row['ImporterName']) ? $row['ImporterName'] : ''));
    $importerAddressIdentity = preg_replace('/[^A-Z0-9]/', '', strtoupper(isset($row['ImporterAddress']) ? $row['ImporterAddress'] : ''));
    $importerAddressHasLocation = preg_match('/[0-9,\\r\\n]/', $row['ImporterAddress']);
    if (($row['ImporterAddress'] === '' || ($importerIdentity !== '' && $importerAddressIdentity === $importerIdentity) || !$importerAddressHasLocation)
        && !empty($row['ExporterAddress'])) {
        $row['ImporterAddress'] = $row['ExporterAddress'];
    }
    foreach (array('ProcessorName', 'StationName', 'StuffingName', 'ConsolidatorName') as $nameField) {
        if (isset($row[$nameField])) $row[$nameField] = saimeProcessorDisplayName($row[$nameField]);
    }    if (!empty($row['StuffingLocation']) && !ctype_digit($row['StuffingLocation'])) {
        $row['StuffingName'] = $row['StuffingLocation'];
        $row['StuffingAddress'] = '';
    }
    $cache[$key] = $row;
    return $row;
}
function saimeProcessorDisplayName($name)
{
    $normalized = preg_replace('/[^A-Z0-9]/', '', strtoupper(trim((string) $name)));
    if ($normalized === 'SAIMARINEEXPORTSPVTLTDUNIT1') return 'SAI MARINE EXPORTS PVT LTD';
    return trim((string) $name);
}function saimeReportValue($connection, $poId, $field, $fallback = '')
{
    $row = saimeReportFacts($connection, $poId);
    return isset($row[$field]) && $row[$field] !== '' ? $row[$field] : $fallback;
}
function saimeReportHtml($connection, $poId, $field, $fallback = '')
{
    return htmlspecialchars(saimeReportValue($connection, $poId, $field, $fallback), ENT_QUOTES, 'UTF-8');
}
function saimeProcessorContactHtml($connection, $poId)
{
    $row = saimeReportFacts($connection, $poId);
    $parts = array();
    foreach (array('ProcessorPhone' => 'TEL: ', 'ProcessorFax' => 'FAX: ', 'ProcessorEmail' => 'EMAIL: ') as $field => $label) {
        if (!empty($row[$field]) && stripos($row['ProcessorAddress'], $row[$field]) === false) $parts[] = $label . htmlspecialchars($row[$field], ENT_QUOTES, 'UTF-8');
    }
    return implode(', ', $parts);
}
function saimeGrossWeights($connection, $poId, $fallbackKg, $fallbackLb, $proforma = false)
{
    $facts = saimeReportFacts($connection, $poId);
    $gross = isset($facts['TotalGrossWt']) ? (float) $facts['TotalGrossWt'] : 0;
    if ($proforma) {
        $result = $connection->query('SELECT SUM(PIGrossWt) AS GrossWeight FROM ScrmPoGradeData WHERE InvId = ' . (int) $poId);
        if (!$result) throw new RuntimeException('Unable to read saved proforma gross weight.');
        $row = $result->fetch_assoc();
        $gross = $row ? (float) $row['GrossWeight'] : 0;
    }
    $unit = isset($facts['QtyUnit']) ? strtoupper($facts['QtyUnit']) : '';
    if ($gross > 0 && in_array($unit, array('KG', 'KGS'), true)) return array($gross, $gross * 2.2046226218);
    if ($gross > 0 && in_array($unit, array('LB', 'LBS'), true)) return array($gross / 2.2046226218, $gross);
    return array($fallbackKg, $fallbackLb);
}
