<?php
function getConnorIsfWorksheet($iconn, $PoID)
{
    $e = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
    $nl = function ($value) use ($e) { return nl2br($e($value)); };
    $compact = function ($value) { return trim(preg_replace('/\s+/', ' ', (string) $value)); };
    $date = function ($value) {
        if ($value === null || trim((string) $value) === '' || $value === '0000-00-00') return '';
        $time = strtotime((string) $value);
        return $time ? date('m.d.Y', $time) : (string) $value;
    };

    $stmt = $iconn->prepare("SELECT SPD.*, SBD.BuyerName, SBD.BuyerAddress, SBD.BuyerEmailAddress,
            SCD.ConsigneeName, SCD.ConsigneeAddress, SCD.ConsigneeIRSNumber, SCD.ConsigneeFishingNo,
            SIR.ImporterCode, SOC.CerificationName, SNP.NotifyPartyName,
            EXP.ExpName AS ExporterName, EXP.ExpAddress AS ExporterAddress,
            PROC.ExpName AS ProcessorName, PROC.ExpAddress AS ProcessorAddress,
            PROC.ExpUsfdaNo AS ProcessorUsfdaNo, PROC.ProcessorBAPNo,
            POD.Name AS DischargePortName
        FROM ScrmPoData SPD
        LEFT JOIN ScrmBuyerDetails SBD ON SBD.BuyerID = SPD.BuyerID
        LEFT JOIN ScrmConsigneeDetails SCD ON SCD.ConID = SPD.ConsigneeID
        LEFT JOIN ScrmImporterOfRecord SIR ON SIR.ImporterID = SPD.ImporterID
        LEFT JOIN ScrmNotifyparty SNP ON SNP.NotifyPartyID = SPD.NotifyPartyID
        LEFT JOIN ScrmOrderCertification SOC ON SOC.CerificationID = SPD.Certification
        LEFT JOIN AdminExporterDetails EXP ON EXP.ExpID = SPD.ExporterID
        LEFT JOIN AdminExporterDetails PROC ON PROC.ExpID = SPD.ProcessorID
        LEFT JOIN ScrmPortCities POD ON POD.PortId = SPD.PortOfDischarge
        WHERE SPD.PoID = ? LIMIT 1");
    $stmt->bind_param('i', $PoID);
    $stmt->execute();
    $po = $stmt->get_result()->fetch_assoc() ?: array();

    $stmtShip = $iconn->prepare("SELECT SCI.ContainerNo AS InvoiceContainerNo, SSD.ContainerNo AS ShipmentContainerNo,
            SSD.FinalDestination, ISF.*
        FROM ScrmCustomInvoices SCI
        LEFT JOIN ScrmShipmentDetails SSD ON SSD.PiID = SCI.PiID
        LEFT JOIN ScrmISFDetails ISF ON ISF.PIID = SCI.PiID
        WHERE SCI.PiID = ? ORDER BY SCI.CInvID DESC LIMIT 1");
    $stmtShip->bind_param('i', $PoID);
    $stmtShip->execute();
    $ship = $stmtShip->get_result()->fetch_assoc() ?: array();

    $plantName = trim(isset($po['ProcessorName']) ? (string) $po['ProcessorName'] : '');
    $plantAddress = trim(isset($po['ProcessorAddress']) ? (string) $po['ProcessorAddress'] : '');
    $items = array();
    $products = array();
    $packStyles = array();
    $brands = array();
    $grades = array();
    $hsCodes = array();
    $totalCases = 0;
    $totalPounds = 0.0;
    $quantityComplete = true;
    $stmtGoods = $iconn->prepare("SELECT PGD.Mcs, PGD.BuyerItemCode, PGD.Variety, PGD.Description, PGD.Brand, PGD.Grade,
            APS.PackingStyle, APS.TotalSlabs, APS.SlabWeight, APS.LbsFactor, APS.MFactor, APS.Uom, SPM.HSCode
        FROM ScrmPoGradeData PGD
        LEFT JOIN AdminPackingStyles APS ON APS.PCKID = PGD.PackingStyleID
        LEFT JOIN ScrmPIVarietyList PVL ON PVL.VarietyID = PGD.VarietyID
        LEFT JOIN ScrmPreparationMaster SPM ON SPM.ShortCode = PVL.PreparationType AND SPM.SpeciesShortCode = PVL.Species
        WHERE PGD.InvId = ?");
    $stmtGoods->bind_param('i', $PoID);
    $stmtGoods->execute();
    $goodsResult = $stmtGoods->get_result();
    while ($goods = $goodsResult->fetch_assoc()) {
        if (trim((string) $goods['BuyerItemCode']) !== '') $items[] = trim((string) $goods['BuyerItemCode']);
        $description = trim((string) $goods['Description']);
        if ($description === '') $description = trim((string) $goods['Variety']);
        if ($description !== '') $products[] = $description;
        if (trim((string) $goods['PackingStyle']) !== '') $packStyles[] = trim((string) $goods['PackingStyle']);
        if (trim((string) $goods['Brand']) !== '') $brands[] = trim((string) $goods['Brand']);
        if (trim((string) $goods['Grade']) !== '') $grades[] = trim((string) $goods['Grade']);
        $hs = trim((string) $goods['HSCode']);
        if ($hs !== '') $hsCodes[] = $hs;
        $cases = (int) $goods['Mcs'];
        $totalCases += $cases;
        $factor = (float) $goods['LbsFactor'];
        if ($factor <= 0) {
            $units = array('LB' => 1, 'LBS' => 1, 'OZ' => 0.0625, 'KG' => 2.2046226218, 'KGS' => 2.2046226218);
            $unit = strtoupper(trim((string) $goods['Uom']));
            $factor = isset($units[$unit]) ? $units[$unit] : 0;
        }
        if ($cases > 0 && ($factor <= 0 || (float) $goods['TotalSlabs'] <= 0 || (float) $goods['SlabWeight'] <= 0)) $quantityComplete = false;
        $totalPounds += $cases * (float) $goods['TotalSlabs'] * (float) $goods['SlabWeight'] * $factor;
    }

    $containerNo = trim(isset($ship['ContainerNo']) ? (string) $ship['ContainerNo'] : '');
    if ($containerNo === '') $containerNo = trim(isset($ship['ShipmentContainerNo']) ? (string) $ship['ShipmentContainerNo'] : '');
    if ($containerNo === '') $containerNo = trim(isset($ship['InvoiceContainerNo']) ? (string) $ship['InvoiceContainerNo'] : '');
    $blNo = trim(isset($ship['BLNo']) ? (string) $ship['BLNo'] : '');
    $arrivalDate = !empty($ship['ETA_FD']) ? $ship['ETA_FD'] : (!empty($ship['EstimatedETA']) ? $ship['EstimatedETA'] : $ship['ETA_POD']);
    $destinationPort = !empty($ship['FinalDestination']) ? $ship['FinalDestination'] : $po['DischargePortName'];
    $buyerName = trim((string) $po['BuyerName']);
    $buyerAddress = trim((string) $po['BuyerAddress']);
    if ($buyerName === '') $buyerName = trim((string) $po['ConsigneeName']);
    if ($buyerAddress === '') $buyerAddress = trim((string) $po['ConsigneeAddress']);
    $consigneeName = trim((string) $po['ConsigneeName']);
    $consigneeAddress = trim((string) $po['ConsigneeAddress']);
    if ($consigneeName === '') $consigneeName = $buyerName;
    if ($consigneeAddress === '') $consigneeAddress = $buyerAddress;
    $irsNo = saimeReportValue($iconn, $PoID, 'ImporterCode', trim((string) $po['ImporterCode']));
    if ($irsNo === '' && stripos((string) $po['ExporterName'], 'SAI MARINE') !== false) $irsNo = '044601-01127';
    $productText = $nl(implode("\n", array_unique($products)));
    $packText = implode(', ', array_unique(array_merge($packStyles, $grades, $brands)));
    $hsText = implode(', ', array_map(function ($code) {
        $digits = preg_replace('/\D+/', '', $code);
        return strlen($digits) === 8 ? substr($digits, 0, 4) . '.' . substr($digits, 4, 2) . '.' . substr($digits, 6, 2) : $code;
    }, array_unique($hsCodes)));
    $plantDetails = '<b>' . $e(strtoupper($plantName)) . '</b><br>';
    if (!empty($po['ProcessorUsfdaNo'])) $plantDetails .= 'FDA REGISTRATION NO: ' . $e($po['ProcessorUsfdaNo']) . '<br>';
    $plantDetails .= $nl($compact(strtoupper($plantAddress)));
    // Approval number is not an HACCP certificate; no HACCP field exists in the supplied schema.
    $manufacturerDetails = $plantDetails . '<br>HACCP NO:';
    $email = trim((string) $po['BuyerEmailAddress']);
    if ($email !== '' && stripos($buyerAddress, $email) === false) $buyerAddress .= "\n" . $email;
    $buyerDetails = '<b>' . $e(strtoupper($buyerName)) . '</b><br>' . $nl($compact(strtoupper($buyerAddress)));

    $first = function ($row, $keys) {
        foreach ($keys as $key) {
            if (isset($row[$key]) && trim((string) $row[$key]) !== '' && $row[$key] !== '0000-00-00') return trim((string) $row[$key]);
        }
        return '';
    };
    $fin = trim((string) $po['ConsigneeFishingNo']);
    if ($fin === '') $fin = implode(' & ', array_unique($items));
    $seller = $nl($po['ExporterName'] . "\n" . $compact($po['ExporterAddress']));
    $certification = preg_replace('/BAP\s*(\d)\s*\*/i', 'BAP $1 STAR', (string) $po['CerificationName']);
    $html = '<style>table{font-family:helvetica;font-size:8pt;border-collapse:collapse}td{line-height:1.05}</style>';
    $html .= '<table border="1" cellspacing="0" cellpadding="3" width="100%">';
    $html .= '<tr><td colspan="3" bgcolor="#ccffcc" align="center" style="font-size:12pt"><b>I S F -form (SHRIMP)</b></td></tr>';
    foreach (array('MAAK Purchase Order number:' => $po['ReferenceNo'], 'Buyer PO Number' => $po['PoNo'], 'FIN Number' => $fin) as $label => $value) {
        $html .= '<tr><td colspan="2" width="40%" align="right">' . $e($label) . '</td><td width="60%" bgcolor="#ff99cc" align="center" style="font-size:11pt"><b>' . $e($value) . '</b></td></tr>';
    }
    $rows = array(
        array('1', 'Master Bill of Lading No.', $e($blNo)),
        array('2', 'House Bill of Lading No.', ''),
        array('3', 'Manufacture Name, Address, FDA registration no. and HACCP Number', $manufacturerDetails),
        array('4', 'Seller Name and Address', $seller),
        array('5', 'Container stuffing location', $nl(saimeReportValue($iconn, $PoID, 'StuffingName', $plantName)) . '<br>' . $nl(saimeReportValue($iconn, $PoID, 'StuffingAddress', $plantAddress))),
        array('6', 'Consolidator Name and address', $nl(saimeReportValue($iconn, $PoID, 'ConsolidatorName', $plantName)) . '<br>' . $nl(saimeReportValue($iconn, $PoID, 'ConsolidatorAddress', $plantAddress))),
        array('7', 'Buyer name and address', $buyerDetails),
        array('8', 'Ship to name and address', $buyerDetails),
        array('9', "Importer of Record Number/Importer of record company's name & address", $e($irsNo) . '<br>' . $nl(saimeReportValue($iconn, $PoID, 'ImporterName', $po['ExporterName'])) . '<br>' . $nl(saimeReportValue($iconn, $PoID, 'ImporterAddress', $po['ExporterAddress']))),
        array('10', 'Consignee number', $e($buyerName)),
        array('11', 'Country of Origin', saimeReportHtml($iconn, $PoID, 'CountryOfOrigin', 'INDIA')),
        array('12', 'Commodity HS Code', $e($hsText))
    );
    $renderRow = function ($row) use ($e) {
        return '<tr><td width="4%">' . $e($row[0]) . '</td><td width="36%">' . $e($row[1]) . '</td><td width="60%"><b>' . $row[2] . '</b></td></tr>';
    };
    foreach ($rows as $row) $html .= $renderRow($row);
    $html .= '<tr><td colspan="3" align="center"><b>Additional information</b></td></tr>';
    $rows = array(
        array('1', 'Product name', $productText),
        array('2', 'Pack size and brand', $e($packText)),
        array('3', 'No. of cartons (cases)', $e(number_format($totalCases, 0, '.', ''))),
        array('4', 'Qty in pounds', $quantityComplete ? $e(rtrim(rtrim(number_format($totalPounds, 3, '.', ''), '0'), '.')) : ''),
        array('5', 'Container number', $e($containerNo)),
        array('6', 'BAP Star', $e($certification)),
        array('7', 'Plant BAP #. Please put N/A if not applicable.', $e($po['ProcessorBAPNo'])),
        array('', 'Farm BAP #. Please put N/A if not applicable.', ''),
        array('8', 'Hatchery BAP #. Please put N/A if not applicable.', ''),
        array('', 'Feed Mill BAP #. Please put N/A if not applicable.', ''),
        array('9', 'Farm Name and FARM #', ''),
        array('10', 'Best By Date', $e($date($first($ship, array('BestBeforeDate'))))),
        array('11', 'Departure date (ETD Port of Loading)', $e($date($first($ship, array('LoadingDate', 'ShippingOnboardDt', 'BLDate'))))),
        array('', 'Arrival date (ETA of Destination)', $e($date($first($ship, array('ETA_FD', 'EstimatedETA', 'ETA_POD'))))),
        array('12', 'Destination Port', $e($destinationPort))
    );
    foreach ($rows as $row) $html .= $renderRow($row);
    $html .= '</table><br><div style="font-family:helvetica;font-size:7pt;color:#ff0000"><i>'
        . 'a. Please fill out all the items completely<br>b. The address should have postal codes<br>'
        . 'c. Completed form needs to be sent 48 hours before the vessel departure</i></div>';
    return $html;
}
