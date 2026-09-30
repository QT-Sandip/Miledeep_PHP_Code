<?php
//ini_set( 'display_errors', 1 );

session_start();

//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $PoID=$_GET['id'];
 $Type=$_GET['Type'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
require_once __DIR__ . '/Includes/SaimeSinglePagePdf.php';
include ($cp."Includes/IncludePage.php");
require_once __DIR__ . '/Includes/SaimeReportData.php';
require_once __DIR__ . '/Includes/ConnorIsfWorksheet.php';


class MYPDF extends TCPDF {

    public function Header() {

	global $Status;


        $image_file = 'images/ds_logo.jpg';
        $this->SetFont('helvetica', 'B', 15);
		$this->Cell(190, 0, '', 0, 1, 'C', 0, '', 1, false, 'T', 'M');
		$this->Cell(0, 10, '', 0, 1, 'C', 0, '', 1, false, 'T', 'M');

    }

    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 15, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
   }
}



    $pdf = new MYPDF('P', PDF_UNIT, 'LETTER', true, 'UTF-8', false);



	$pdf->SetCreator(PDF_CREATOR);
    $isfFormatContext = getIsfFormatContext($iconn, $PoID);
    $pdf->SetTitle($isfFormatContext['BrokerName'] . ' ISF 10+2 : ' . $Header);
    $pdf->SetHeaderData('', '', '', '');
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont('helvetica');
    $pdf->SetFooterMargin(10);
	$pdf->SetHeaderMargin(5);
    $pdf->SetMargins(7, 7, 7, 5);
    if ($isfFormatContext['UseLeesaRemainingBuyerFormat']) $pdf->SetMargins(17, 18, 17);
    if ($isfFormatContext['UseConnorFormat']) $pdf->SetMargins(10, 7, 10);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $pdf->SetAutoPageBreak(TRUE, 5);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->AddPage();



	$table_data = $isfFormatContext['UseConnorFormat']
        ? getConnorIsfWorksheet($iconn, $PoID)
        : ($isfFormatContext['UseLeesaRemainingBuyerFormat']
            ? getLeesaRemainingBuyerIsfWorksheet($iconn, $PoID)
            : getWilliamsIsfWorksheet($iconn, $PoID));
	writeSaimeSinglePage($pdf, $table_data);
	 $pdf->SetFont('helvetica', '', 6);

$ExpID = 0;
$stmtFilenameExporter = $iconn->prepare('SELECT ExporterID FROM ScrmPoData WHERE PoID = ? LIMIT 1');
$stmtFilenameExporter->bind_param('i', $PoID);
$stmtFilenameExporter->execute();
$filenameExporterRow = $stmtFilenameExporter->get_result()->fetch_assoc();
if ($filenameExporterRow) $ExpID = (int) $filenameExporterRow['ExporterID'];
$ArrayFilename=getfilename($iconn,$ExpID,$PoID);

$InvShortCode=$ArrayFilename['InvShortCode'];
$CInvNo=$ArrayFilename['CInvNo'];
$FinancialYear=$ArrayFilename['FinancialYear'];

$file_name=$InvShortCode.'-SD-'.$CInvNo.'-'.$FinancialYear.'.pdf';

ob_end_clean();
if($Type==''){
$Type='I';
}else{
		$Fname=$MailTempFolder; //From Config
	$file_name=$_GET['FileName'];
	$file_name = str_replace('/','', $file_name );;
	$file_name=$Fname.$file_name;
}$pdf->Output($file_name, $Type);
//$pdf->Ln();


/**
 * Keep the complete worksheet on one page. Trial renders are rolled back;
 * page breaks remain enabled so overflow is detected instead of clipped.
 * The original layout is retained whenever it already fits.
 */
function writeIsfSinglePage($pdf, $html)
{
    // TCPDF uses its own transactions for nobr rows; remove that attribute
    // during measurement so it cannot replace our whole-worksheet snapshot.
    $html = preg_replace('/\s+nobr="true"/i', '', $html);
    $startPage = $pdf->getPage();
    $startPages = $pdf->getNumPages();
    $bottom = $pdf->getPageHeight() - $pdf->getBreakMargin();
    $baseFont = $pdf->getFontSizePt();
    for ($attempt = 0; $attempt < 60; ++$attempt) {
        $scale = pow(0.9, $attempt);
        $fittedHtml = preg_replace_callback(
            '/font-size\s*:\s*([0-9.]+)(pt|px)/i',
            function ($match) use ($scale) {
                return 'font-size:' . sprintf('%.4F', (float) $match[1] * $scale) . $match[2];
            },
            $html
        );
        $fittedHtml = preg_replace_callback(
            '/cellpadding="([0-9.]+)"/i',
            function ($match) use ($scale) {
                return 'cellpadding="' . sprintf('%.4F', (float) $match[1] * $scale) . '"';
            },
            $fittedHtml
        );
        $pdf->startTransaction();
        $pdf->SetFontSize($baseFont * $scale);
        $pdf->writeHTML($fittedHtml);
        if ($pdf->getNumPages() === $startPages && $pdf->getPage() === $startPage && $pdf->GetY() <= $bottom) {
            $pdf->commitTransaction();
            return;
        }
        $pdf->rollbackTransaction(true);
    }
    throw new RuntimeException('ISF content could not fit on a single page.');
}

function getWilliamsIsfWorksheet($iconn, $PoID)
{
    $linerName = SaimeReportData::linerName($iconn, $PoID);
    $e = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
    $nl = function ($value) use ($e) { return nl2br($e($value)); };
    $date = function ($value) {
        if ($value === null || trim((string) $value) === '' || $value === '0000-00-00') return '';
        $time = strtotime((string) $value);
        return $time ? date('m.d.Y', $time) : (string) $value;
    };

    $stmt = $iconn->prepare("SELECT SPD.*,
            SCD.ConsigneeName, SCD.ConsigneeAddress, SCD.ConsigneeIRSNumber,
            SBD.BuyerName, SBD.BuyerAddress, SBD.BuyerIRSNumber,
            SIR.ImporterCode, SNP.NotifyPartyName,
            EXP.ExpName AS ExporterName, EXP.ExpAddress AS ExporterAddress, EXP.ExpPhoneNumber AS ExporterPhone,
            PROC.ExpName AS ProcessorName, PROC.ExpAddress AS ProcessorAddress,
            POL.Name AS LoadingPortName, POD.Name AS DischargePortName
        FROM ScrmPoData SPD
        LEFT JOIN ScrmConsigneeDetails SCD ON SCD.ConID = SPD.ConsigneeID
        LEFT JOIN ScrmBuyerDetails SBD ON SBD.BuyerID = SPD.BuyerID
        LEFT JOIN ScrmImporterOfRecord SIR ON SIR.ImporterID = SPD.ImporterID
        LEFT JOIN ScrmNotifyparty SNP ON SNP.NotifyPartyID = SPD.NotifyPartyID
        LEFT JOIN AdminExporterDetails EXP ON EXP.ExpID = SPD.ExporterID
        LEFT JOIN AdminExporterDetails PROC ON PROC.ExpID = SPD.ProcessorID
        LEFT JOIN ScrmPortCities POL ON POL.PortId = SPD.PortofLoading
        LEFT JOIN ScrmPortCities POD ON POD.PortId = SPD.PortOfDischarge
        WHERE SPD.PoID = ? LIMIT 1");
    $stmt->bind_param('i', $PoID);
    $stmt->execute();
    $po = $stmt->get_result()->fetch_assoc() ?: array();

    $stmtShipment = $iconn->prepare("SELECT SCI.CInvID, SCI.ContainerNo AS InvoiceContainerNo, SCI.CInvCode,
            SCI.LinerSealNo, SCI.PackDescription,
            SSD.ContainerNo AS ShipmentContainerNo, SSD.FinalDestination,
            ISF.*
        FROM ScrmCustomInvoices SCI
        LEFT JOIN ScrmShipmentDetails SSD ON SSD.PiID = SCI.PiID
        LEFT JOIN ScrmISFDetails ISF ON ISF.PIID = SCI.PiID
        WHERE SCI.PiID = ? ORDER BY SCI.CInvID DESC LIMIT 1");
    $stmtShipment->bind_param('i', $PoID);
    $stmtShipment->execute();
    $ship = $stmtShipment->get_result()->fetch_assoc() ?: array();

    $vessels = array();
    $stmtVessel = $iconn->prepare('SELECT VesselName, VOYNO, TranshipDt FROM ScrmTranshipmentPortsList WHERE PIID = ? ORDER BY TspID');
    $stmtVessel->bind_param('i', $PoID);
    $stmtVessel->execute();
    $vesselResult = $stmtVessel->get_result();
    while ($vessel = $vesselResult->fetch_assoc()) $vessels[] = $vessel;

    $plantName = $po['ProcessorName'];
    $plantAddress = $po['ProcessorAddress'];
    $plantName = saimeReportValue($iconn, $PoID, 'ProcessorName', $plantName);
    $plantAddress = saimeReportValue($iconn, $PoID, 'ProcessorAddress', $plantAddress);
    $buyerName = trim((string) $po['BuyerName']);
    $buyerAddress = trim((string) $po['BuyerAddress']);
    if ($buyerName === '') $buyerName = $po['ConsigneeName'];
    if ($buyerAddress === '') {
        $lookupName = '%' . trim((string) $po['ConsigneeName']) . '%';
        $stmtBuyer = $iconn->prepare('SELECT BuyerName, BuyerAddress, BuyerIRSNumber FROM ScrmBuyerDetails WHERE BuyerName LIKE ? ORDER BY CHAR_LENGTH(BuyerAddress) DESC LIMIT 1');
        $stmtBuyer->bind_param('s', $lookupName);
        $stmtBuyer->execute();
        $fallbackBuyer = $stmtBuyer->get_result()->fetch_assoc();
        if ($fallbackBuyer) {
            $buyerName = $fallbackBuyer['BuyerName'];
            $buyerAddress = $fallbackBuyer['BuyerAddress'];
            if (empty($po['BuyerIRSNumber'])) $po['BuyerIRSNumber'] = $fallbackBuyer['BuyerIRSNumber'];
        }
    }

    $consigneeName = trim((string) $po['ConsigneeName']);
    $consigneeAddress = trim((string) $po['ConsigneeAddress']);
    if ($consigneeAddress === '') {
        $consigneeName = $buyerName;
        $consigneeAddress = $buyerAddress;
    }

    $irsNo = saimeReportValue($iconn, $PoID, 'ImporterCode', trim((string) $po['ImporterCode']));
    if ($irsNo === '') $irsNo = trim((string) $po['BuyerIRSNumber']);
    if ($irsNo === '' && stripos((string) $po['ExporterName'], 'SAI MARINE') !== false) $irsNo = '044601-01127';

    $shipTo = trim((string) $ship['ByRoad']);
    if (!empty($ship['SDD'])) {
        $storeId = (int) $ship['SDD'];
        $storeResult = $iconn->query("SELECT StoreName, StoreAddress1 FROM ScrmStoreDeliveryDetails WHERE StoreID = $storeId LIMIT 1");
        if ($storeResult && ($store = $storeResult->fetch_assoc())) {
            $shipTo = trim($store['StoreName'] . ' ' . $store['StoreAddress1']);
        }
    }
    if ($shipTo === '') $shipTo = trim((string) $ship['FinalDestination']);
    if ($shipTo === '') $shipTo = trim((string) $po['DischargePortName']);

    $originVessel = trim((string) $ship['OriginatingVessel']);
    $originVoyage = trim((string) $ship['VoyOrg']);
    $finalVessel = trim((string) $ship['ImportingVessel']);
    $finalVoyage = trim((string) $ship['VoyFinal']);
    $transhipDate = trim((string) $ship['TranshipmentDate']);
    if (count($vessels)) {
        if ($originVessel === '') $originVessel = $vessels[0]['VesselName'];
        if ($originVoyage === '') $originVoyage = $vessels[0]['VOYNO'];
        $lastVessel = $vessels[count($vessels) - 1];
        if ($finalVessel === '') $finalVessel = $lastVessel['VesselName'];
        if ($finalVoyage === '') $finalVoyage = $lastVessel['VOYNO'];
        if ($transhipDate === '') $transhipDate = $lastVessel['TranshipDt'];
    }

    $containerNo = trim((string) $ship['ContainerNo']);
    if ($containerNo === '') $containerNo = trim((string) $ship['ShipmentContainerNo']);
    if ($containerNo === '') $containerNo = trim((string) $ship['InvoiceContainerNo']);
    $blNo = trim((string) $ship['BLNo']);

    $descriptions = array();
    $hsCodes = array();
    $stmtGoods = $iconn->prepare("SELECT CIG.CIDescription, CIG.Variety, CIG.Codes,
            CIG.BuyerItemCode, PGD.Description AS PoDescription, SPM.HSCode
        FROM ScrmCIGradeData CIG
        LEFT JOIN ScrmPoGradeData PGD ON PGD.ID = CIG.PoGradeID
        LEFT JOIN ScrmPIVarietyList PVL ON PVL.VarietyID = CIG.VarietyID
        LEFT JOIN ScrmPreparationMaster SPM ON SPM.ShortCode = PVL.PreparationType
        WHERE CIG.InvId = ?");
    $stmtGoods->bind_param('i', $PoID);
    $stmtGoods->execute();
    $goodsResult = $stmtGoods->get_result();
    while ($goods = $goodsResult->fetch_assoc()) {
        $description = trim((string) $goods['CIDescription']);
        if ($description === '') $description = trim((string) $goods['PoDescription']);
        if ($description === '') $description = trim((string) $goods['Variety']);
        if ($description !== '') $descriptions[] = $description;
        $hs = trim((string) $goods['HSCode']);
        if ($hs === '') $hs = trim((string) $goods['Codes']);
        if ($hs !== '') $hsCodes[] = $hs;
    }
    $commodity = implode('<br>', array_unique($descriptions));
    $formattedHsCodes = array();
    foreach (array_unique($hsCodes) as $code) {
        $digits = preg_replace('/\D+/', '', (string) $code);
        $formattedHsCodes[] = strlen($digits) === 8
            ? substr($digits, 0, 4) . '.' . substr($digits, 4, 2) . '.' . substr($digits, 6, 2)
            : $code;
    }
    $hsCode = implode(', ', $formattedHsCodes);

    $loadingDate = isset($ship['LoadingDate']) ? $ship['LoadingDate'] : '';
    if (empty($loadingDate) && isset($ship['BLDate'])) $loadingDate = $ship['BLDate'];
    $etaPod = isset($ship['ETA_POD']) ? $ship['ETA_POD'] : '';
    $etaFinal = isset($ship['ETA_FD']) ? $ship['ETA_FD'] : '';
    if (empty($etaFinal) && isset($ship['EstimatedETA'])) $etaFinal = $ship['EstimatedETA'];
    $etaLines = array();
    $transhipPort = isset($ship['TranshipmentPort']) ? trim((string) $ship['TranshipmentPort']) : '';
    if ($transhipPort !== '' && $etaPod !== '') $etaLines[] = strtoupper($transhipPort) . ': ' . $date($etaPod);
    $finalEtaPort = !empty($ship['FinalDestination']) ? $ship['FinalDestination'] : $po['DischargePortName'];
    if ($etaFinal !== '') $etaLines[] = strtoupper((string) $finalEtaPort) . ': ' . $date($etaFinal);
    if (!$etaLines && $etaPod !== '') $etaLines[] = strtoupper((string) $po['DischargePortName']) . ': ' . $date($etaPod);
    $etaText = implode("\n", $etaLines);

    $importerName = $e(strtoupper(saimeReportValue($iconn, $PoID, 'ImporterName', $po['ExporterName'])));
    if (!empty($po['NotifyPartyName'])) $importerName .= '<br>C/O. ' . $e(strtoupper((string) $po['NotifyPartyName']));

    $html = '<style>
        table{border-collapse:collapse;font-family:helvetica;font-size:7.2pt;color:#000}
        td,th{padding:2px 3px;line-height:1.08}
        .title{font-size:11pt}
        .note{font-size:6.2pt;line-height:1.08}
        .label{font-size:6.6pt}
        .value{font-size:7.3pt;font-weight:bold}
    </style>';
    $html .= '<table border="1" cellpadding="2" cellspacing="0" width="100%">';
    $worksheetBrokerName = trim((string) $po['NotifyPartyName']) !== '' ? strtoupper($po['NotifyPartyName']) : 'CUSTOMS BROKER';
    $html .= '<tr><th align="center" class="title"><b><u>' . $e($worksheetBrokerName) . '</u></b><br><b><u>IMPORTER SECURITY FILING DATA (ISF-10+2) WORKSHEET TEMPLATE</u></b></th></tr>';
    $html .= '<tr><td class="note"><b>Note:</b> Fields marked with an (*) designate mandatory data required for ISF filing. All required address information must contain a postal code (zip) or the ISF will be rejected by CBP. ISF data must be received a minimum of three working days prior to loading on the final vessel arriving the cargo into the United States. If available, a copy of the commercial invoice and related shipping documentation would be helpful for data verification and unified entry filing.</td></tr>';
    $html .= '<tr><td cellpadding="0"><table border="0" cellpadding="2" cellspacing="0" width="100%"><tr>';
    $html .= '<td width="55%" style="border-right:1px solid #000"><span class="label">Importer Name:</span><br><span class="value">' . $importerName . '</span></td>';
    $html .= '<td width="27%" style="border-right:1px solid #000"><span class="label">P.O. or Ref #</span><br><span class="value">' . $e($po['PoNo']) . '</span></td>';
    $html .= '<td width="18%"><span class="label">Date:</span><br><span class="value">' . $e($date($po['PoDate'])) . '</span></td>';
    $html .= '</tr></table></td></tr>';
    $html .= '<tr><td cellpadding="0"><table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td width="50%" style="border-right:1px solid #000"><b>*Master B/L w/SCAC:</b><br><span class="value">' . $e($blNo) . '</span></td><td width="50%"><b>*House Way Bill w/SCAC (if applicable):</b></td></tr></table></td></tr>';
    $html .= '<tr><td cellpadding="0"><table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td width="50%" style="border-right:1px solid #000"><b>Originating Vessel:</b><br><span class="value">' . $e(strtoupper($originVessel)) . '</span></td><td width="16%" style="border-right:1px solid #000"><b>Voy #:</b><br><span class="value">' . $e($originVoyage) . '</span></td><td width="34%"><b>Est. date of loading:</b><br><span class="value">' . $e($date($loadingDate)) . '</span></td></tr></table></td></tr>';
    $html .= '<tr><td cellpadding="0"><table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td width="40%" style="border-right:1px solid #000"><b>Final Importing Vessel:</b><br><span class="value">' . $e(strtoupper($finalVessel)) . '</span></td><td width="14%" style="border-right:1px solid #000"><b>Voy #:</b><br><span class="value">' . $e($finalVoyage) . '</span></td><td width="23%" style="border-right:1px solid #000"><b>Est. date of transshipment:</b><br><span class="value">' . $e($date($transhipDate)) . '</span></td><td width="23%"><b>ETA:</b><br><span class="value">' . $nl($etaText) . '</span></td></tr></table></td></tr>';
    if ($linerName !== '') {
        $html .= '<tr><td><b>Steamship Company:</b><br><span class="value">' . $e(strtoupper($linerName)) . '</span></td></tr>';
    }
    $html .= '<tr><td><b>Container No. (attachment if necessary)</b><br><span class="value">' . $e($containerNo) . '</span></td></tr>';

    $rows = array(
        '1. * Importer of record number (IRS #, Customs assigned #, or SS # as applicable):<br><span class="value">' . $e($irsNo) . '</span>',
        '2. * Buyer-complete name &amp; address:<br><span class="value">' . $e(strtoupper($buyerName)) . '<br>' . $nl(strtoupper($buyerAddress)) . '</span>',
        '3. * Consignee number (IRS #, Customs assigned #, or SS # as applicable):<br><span class="value">' . $e($po['ConsigneeIRSNumber']) . '</span>',
        '4. Consignee name &amp; address:<br><span class="value">' . $e(strtoupper($consigneeName)) . '<br>' . $nl(strtoupper($consigneeAddress)) . '</span>',
        '5. Ship to-complete name &amp; address:<br><span class="value">' . $nl(strtoupper($shipTo)) . '</span>',
        '6. * Manufacturer (or supplier)-complete name &amp; address:<br><span class="value">' . $e(strtoupper($plantName)) . '<br>' . $nl(strtoupper($plantAddress)) . '</span>',
        '7. * Seller-complete name &amp; address:<br><span class="value">' . $e(strtoupper($po['ExporterName'])) . '<br>' . $nl(strtoupper($po['ExporterAddress'])) . (!empty($po['ExporterPhone']) ? '<br>TEL: ' . $e($po['ExporterPhone']) : '') . '</span>',
        '8. * Container stuffing location-complete name &amp; address:<br><span class="value">' . $e(strtoupper(saimeReportValue($iconn, $PoID, 'StuffingName', $plantName))) . '<br>' . $nl(strtoupper(saimeReportValue($iconn, $PoID, 'StuffingAddress', $plantAddress))) . '</span>',
        '9. * Consolidator (if applicable)-complete name &amp; address:<br><span class="value">' . $e(strtoupper(saimeReportValue($iconn, $PoID, 'ConsolidatorName', $plantName))) . '<br>' . $nl(strtoupper(saimeReportValue($iconn, $PoID, 'ConsolidatorAddress', $plantAddress))) . '</span>',
        '10. * Country of Origin: <span class="value">' . saimeReportHtml($iconn, $PoID, 'CountryOfOrigin', 'INDIA') . '</span>',
        '11. Detailed commodity description or HTS number:<br><span class="value">' . $nl(strtoupper($commodity)) . ($hsCode !== '' ? ' &nbsp; HS CODE: ' . $e($hsCode) : '') . '</span>'
    );
    foreach ($rows as $index => $rowHtml) {
        $height = in_array($index, array(1, 3, 5, 6, 7, 8, 10)) ? '44px' : '25px';
        $html .= '<tr><td style="height:' . $height . '">' . $rowHtml . '</td></tr>';
    }
    $html .= '</table>';
    return $html;
}

function getIsfFormatContext($iconn, $PoID)
{
    $stmt = $iconn->prepare("SELECT SBD.BuyerName, SNP.NotifyPartyName
        FROM ScrmPoData SPD
        LEFT JOIN ScrmBuyerDetails SBD ON SBD.BuyerID = SPD.BuyerID
        LEFT JOIN ScrmNotifyparty SNP ON SNP.NotifyPartyID = SPD.NotifyPartyID
        WHERE SPD.PoID = ? LIMIT 1");
    $stmt->bind_param('i', $PoID);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: array();

    $buyerName = trim(isset($row['BuyerName']) ? (string) $row['BuyerName'] : '');
    $notifyPartyName = trim(isset($row['NotifyPartyName']) ? (string) $row['NotifyPartyName'] : '');
    $normalBuyer = preg_replace('/[^A-Z0-9]/', '', strtoupper($buyerName));
    $isCpFoods = strpos($normalBuyer, 'CPFOOD') !== false;

    $normalBroker = preg_replace('/[^A-Z0-9]/', '', strtoupper($notifyPartyName));
    $isConnor = strpos($normalBroker, 'JOHNSCONNOR') !== false;
    $isWilliamsClarke = strpos($normalBroker, 'WILLIAMSCLARKE') !== false;

    return array(
        'UseConnorFormat' => $isConnor,
        'BuyerName' => $buyerName !== '' ? $buyerName : 'ISF',
        'NotifyPartyName' => $notifyPartyName,
        'BrokerName' => $notifyPartyName !== '' ? $notifyPartyName : ($buyerName !== '' ? $buyerName : 'ISF'),
        'UseLeesaRemainingBuyerFormat' => !$isCpFoods && !$isConnor && !$isWilliamsClarke
    );
}

function getLeesaRemainingBuyerIsfWorksheet($iconn, $PoID)
{
    $e = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
    $nl = function ($value) use ($e) { return nl2br($e($value)); };
    $date = function ($value) {
        if ($value === null || trim((string) $value) === '' || $value === '0000-00-00') return '';
        $time = strtotime((string) $value);
        return $time ? date('m.d.Y', $time) : (string) $value;
    };

    $stmt = $iconn->prepare("SELECT SPD.*, SBD.BuyerName, SBD.BuyerAddress,
            SCD.ConsigneeName, SCD.ConsigneeAddress, SCD.ConsigneeIRSNumber, SCD.ConsigneeFishingNo,
            SIR.ImporterCode, SOC.CerificationName, SNP.NotifyPartyName, SNP.NPartyAddress, SNP.NPartyEmailID,
            EXP.ExpName AS ExporterName, EXP.ExpAddress AS ExporterAddress,
            PROC.ExpName AS ProcessorName, PROC.ExpAddress AS ProcessorAddress,
            PROC.ExpUsfdaNo AS ProcessorUsfdaNo, PROC.ExpApprovalNo AS ProcessorApprovalNo,
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
            SSD.FinalDestination, ISF.*, STUFF.ExpName AS StuffingName, STUFF.ExpAddress AS StuffingAddress
        FROM ScrmCustomInvoices SCI
        LEFT JOIN ScrmShipmentDetails SSD ON SSD.PiID = SCI.PiID
        LEFT JOIN ScrmISFDetails ISF ON ISF.PIID = SCI.PiID
        LEFT JOIN AdminExporterDetails STUFF ON STUFF.ExpID = ISF.StuffingLocation
        WHERE SCI.PiID = ? ORDER BY SCI.CInvID DESC LIMIT 1");
    $stmtShip->bind_param('i', $PoID);
    $stmtShip->execute();
    $ship = $stmtShip->get_result()->fetch_assoc() ?: array();

    $plantName = trim(isset($po['ProcessorName']) ? (string) $po['ProcessorName'] : '');
    $plantAddress = trim(isset($po['ProcessorAddress']) ? (string) $po['ProcessorAddress'] : '');
    $plantName = saimeReportValue($iconn, $PoID, 'ProcessorName', $plantName);
    $plantAddress = saimeReportValue($iconn, $PoID, 'ProcessorAddress', $plantAddress);

    $hsCodes = array();
    $stmtGoods = $iconn->prepare("SELECT DISTINCT SPM.HSCode
        FROM ScrmPoGradeData PGD
        LEFT JOIN ScrmPIVarietyList PVL ON PVL.VarietyID = PGD.VarietyID
        LEFT JOIN ScrmPreparationMaster SPM ON SPM.ShortCode = PVL.PreparationType AND SPM.SpeciesShortCode = PVL.Species
        WHERE PGD.InvId = ?");
    $stmtGoods->bind_param('i', $PoID);
    $stmtGoods->execute();
    $goodsResult = $stmtGoods->get_result();
    while ($goods = $goodsResult->fetch_assoc()) {
        $code = trim((string) $goods['HSCode']);
        $digits = preg_replace('/\D+/', '', $code);
        if (strlen($digits) === 8) $code = substr($digits, 0, 4) . '.' . substr($digits, 4, 2) . '.' . substr($digits, 6, 2);
        if ($code !== '') $hsCodes[] = $code;
    }

    // The supplied worksheet uses the vessel itinerary for "Ship To Name".
    $vessels = array();
    $stmtVessel = $iconn->prepare('SELECT VesselName, VOYNO FROM ScrmTranshipmentPortsList WHERE PIID = ? ORDER BY TspID');
    $stmtVessel->bind_param('i', $PoID);
    $stmtVessel->execute();
    $vesselResult = $stmtVessel->get_result();
    while ($vessel = $vesselResult->fetch_assoc()) {
        if (trim((string) $vessel['VesselName']) !== '') {
            $vessels[] = trim($vessel['VesselName'] . (trim((string) $vessel['VOYNO']) !== '' ? ' V ' . $vessel['VOYNO'] : ''));
        }
    }
    if (!$vessels) {
        foreach (array('OriginatingVessel' => 'VoyOrg', 'ImportingVessel' => 'VoyFinal') as $nameKey => $voyageKey) {
            if (!empty($ship[$nameKey])) $vessels[] = trim($ship[$nameKey] . (!empty($ship[$voyageKey]) ? ' V ' . $ship[$voyageKey] : ''));
        }
    }
    $containerNo = trim(isset($ship['ContainerNo']) ? (string) $ship['ContainerNo'] : '');
    if ($containerNo === '') $containerNo = trim(isset($ship['ShipmentContainerNo']) ? (string) $ship['ShipmentContainerNo'] : '');
    if ($containerNo === '') $containerNo = trim(isset($ship['InvoiceContainerNo']) ? (string) $ship['InvoiceContainerNo'] : '');
    $blNo = isset($ship['BLNo']) ? trim((string) $ship['BLNo']) : '';
    $destinationPort = !empty($ship['FinalDestination']) ? $ship['FinalDestination'] : $po['DischargePortName'];
    $buyerName = trim((string) $po['BuyerName']);
    $buyerAddress = trim((string) $po['BuyerAddress']);
    if ($buyerName === '') $buyerName = trim((string) $po['ConsigneeName']);
    if ($buyerAddress === '') $buyerAddress = trim((string) $po['ConsigneeAddress']);
    $irsNo = saimeReportValue($iconn, $PoID, 'ImporterCode', trim((string) $po['ImporterCode']));
    if ($irsNo === '' && stripos((string) $po['ExporterName'], 'SAI MARINE') !== false) $irsNo = '044601-01127';
    $stuffingLocation = isset($ship['StuffingLocation']) ? trim((string) $ship['StuffingLocation']) : '';
    if (ctype_digit($stuffingLocation)) {
        $stuffingLocation = trim(saimeProcessorDisplayName($ship['StuffingName']) . "\n" . (string) $ship['StuffingAddress']);
    }
    $brokerName = trim((string) $po['NotifyPartyName']);
    if ($brokerName === '') $brokerName = 'CUSTOMS BROKER';
    $brokerAddress = trim((string) $po['NPartyAddress']);
    $brokerEmail = trim((string) $po['NPartyEmailID']);

    $html = '<style>
        table{border-collapse:collapse;font-family:times;font-size:10pt}
        td{line-height:1.1}
    </style>';
    $html .= '<div style="font-family:times;text-align:center;font-size:11pt"><b><span style="font-size:14pt">'
        . $e($brokerName) . '</span><br>' . $nl($brokerAddress);
    if ($brokerEmail !== '' && stripos($brokerAddress, $brokerEmail) === false) $html .= '<br>Email id: ' . $e($brokerEmail);
    $html .= '</b></div>';
    $html .= '<h3 style="font-family:times;text-align:center;font-size:14pt">10 + 2 Importer&#8217;s Security Filing Data Elements</h3>';
    $rows = array(
        array('1', 'Manufacturer (or supplier) Name:', $plantName),
        array('', 'Manufacturer (or supplier) Address:', $plantAddress),
        array('2', 'Seller Name:', $po['ExporterName']),
        array('', 'Seller Address:', $po['ExporterAddress']),
        array('3', 'Buyer Name:', $buyerName),
        array('', 'Buyer Address:', $buyerAddress),
        array('4', 'Ship To Name:', implode("\n", array_unique($vessels))),
        array('5', 'Ship to address :', $destinationPort),
        array('6', 'Container stuffing location:', $stuffingLocation),
        array('7', 'Consolidator (stuffer) Name:', saimeReportValue($iconn, $PoID, 'ConsolidatorName', $po['ExporterName'])),
        array('', 'Consolidator (stuffer) Address:', saimeReportValue($iconn, $PoID, 'ConsolidatorAddress', $plantAddress)),
        array('8', 'Importer of Record Number:', $irsNo),
        // The supplied broker form labels the bill of lading as Consignee Number.
        array('9', 'Consignee Number:', $blNo),
        array('10.', 'Container no :', $containerNo),
        array('11', 'Country of Origin:', saimeReportValue($iconn, $PoID, 'CountryOfOrigin', 'INDIA')),
        array('12', 'Commodity HTSUS Number:', implode(', ', array_unique($hsCodes)))
    );
    $html .= '<table border="1" cellspacing="0" cellpadding="5" width="100%">';
    foreach ($rows as $row) {
        $html .= '<tr nobr="true"><td width="5%"><b>' . $e($row[0]) . '</b></td>'
            . '<td width="34%"><b>' . $e($row[1]) . '</b></td>'
            . '<td width="61%">' . $nl($row[2]) . '</td></tr>';
    }
    $html .= '</table><br><div style="font-family:times;font-size:10pt">'
        . 'Please complete the above information and send it back to ' . $e($brokerName)
        . '<br>When received we will then submit the data elements on your behalf.<br>Thank you.</div>';
    return $html;
}

function getDS_TABLE($iconn,$PoID){

	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/func.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include "../../SalesInculdes/SalesConstants.php";

	global $PoDataRow;


$SqlScrmPoData="SELECT * FROM $TblScrmPIData as SPD
 left JOIN $TblScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN $TblScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN AdminExporterDetails as AED on SPD.ProcessorID=AED.ExpID
 left JOIN $TblScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 WHERE PoID='$PoID'";
$StmtPoData=$iconn->query($SqlScrmPoData);
$PoDataRow = $StmtPoData->fetch_assoc();

$ExpID=$PoDataRow['ExporterID'];
$ConsigneeName=$PoDataRow['ConsigneeName'];
$ConsigneeAddress=$PoDataRow['ConsigneeAddress'];
$PoNo=$PoDataRow['PoNo'];
$ReferenceNo=$PoDataRow['ReferenceNo'];
$PoDate=getFormatedDateNA($PoDataRow['PoDate']);
$ProcsrName=saimeProcessorDisplayName($PoDataRow['ExpName']);
$ProcsrAddress=$PoDataRow['ExpAddress'];


$CustomInvoiceRow = SaimeReportData::beaverStreetInvoice($iconn, $PoID);


$InvDate=$PoDataRow['InvDate'];
$LoadingPort=$PoDataRow['Name'];

$SqlPoGradeData="SELECT
SUM(Mcs) as MCS,Brand FROM `ScrmPoGradeData`  WHERE InvId='$PoID'";
$StmtPoGradeData=$iconn->query($SqlPoGradeData);
$RowPoGradeData = $StmtPoGradeData->fetch_assoc();

$MCS=$RowPoGradeData['MCS'];
$Brand=$RowPoGradeData['Brand'];

$PaymentData = getPaymentValues($iconn, $PoID);

$ExporterData=getExporterDetails($ExpID);
$PackSealNo=$CustomInvoiceRow['PackSealNo'];
$SealNo=$CustomInvoiceRow['SealNo'];
$ContainerNumber=$CustomInvoiceRow['ContainerNo'];
$CInvCode=$CustomInvoiceRow['CInvCode'];
$VehicalNumber=$CustomInvoiceRow['VehicalNumber'];
$SBNo=$CustomInvoiceRow['SBNo'];
$BLNo=$CustomInvoiceRow['BLNo'];
$PlaceOfRecipt=$CustomInvoiceRow['PlaceOfRecipt'];
$FinalDestination=$CustomInvoiceRow['FinalDestination'];
$BLDate=getFormatedDateNA($CustomInvoiceRow['BLDate']);

$ETD=getFormatedDateNA($CustomInvoiceRow['ETD']);
$ETA=getFormatedDateNA($CustomInvoiceRow['ETA']);

$ExpName=$ExporterData['ExpName'];
$ExpAddress=$ExporterData['ExpAddress'];
$InvNO=$ExporterData['InvShortCode'].$CustomInvoiceRow['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;

$SqlPOgradeData="SELECT * FROM `$ScrmCIGradeData` where InvId='$PoID'  ";
$StmtPOgradeData=$iconn->query($SqlPOgradeData);

WHILE($RowPOgradeData = $StmtPOgradeData->fetch_assoc()){

$MCS=	$RowPOgradeData['Mcs'];
   $TotalMcs+=$MCS;
}

 $SqlTenPlusTwo="SELECT * FROM `$ScrmLogisticsDetails` as STD LEFT JOIN $ScrmLogisticsMaster
as SL on SL.ID=STD.ConsolidatorNameAddress WHERE PiID='$PoID'";
$StmtTenPlusTwo=$iconn->query($SqlTenPlusTwo);
$RowTenPlusTwo=$StmtTenPlusTwo->fetch_assoc();

$VesselName=$RowTenPlusTwo['VesselName'];
$VOY=$RowTenPlusTwo['VOY'];




	$content='';

	$size=40;

	$size1=60;






			  $width=100;



			  $content .= '
			  <H4 align="Center"><b><u>' . saimeReportHtml($iconn, $PoID, 'BrokerName', 'WILLIAMS CLARKE CO.,INC') . ' <BR>
			  IMPORTER SECURITY FILING DATA (ISF-10+2)WORKSHEET TEMPLATE </b></u></H4>
			  <H4 align="Center"><b><u></b></u></H4>

			  <table border="1" cellspacing="0" cellpadding="5" align="center" width="100%">


               <tr>
				 <th  align="LEFT" width="50%" ><B>Importer Name:</B> <BR>'.$ConsigneeName.'</th>
				 <th  align="LEFT" width="35%" >P.O or Ref# <BR>'.$PoNo.' /'.$ReferenceNo.'</th>
				 <th  align="LEFT" width="15%" >Date: <BR>'.$PoDate.'</th>
				</tr>

			  <tr>
				 <th  align="LEFT" width="50%" >Master B/L w/SCAC: <BR></th>
				 <th  align="LEFT" width="50%" >* House Way Bill w/SCAC(if applicable): <BR></th>
			  </tr>

			  <tr>
				 <th  align="LEFT" width="30%" >Originating Importing Vessel:<BR></th>
				 <th  align="LEFT" width="20%" >Voy# <BR></th>
				 <th  align="LEFT" width="50%" >Est. date of loading:<BR>
				 '.$BLDate.'('.$LoadingPort.')</th>

			  </tr>

			<tr>
				 <th  align="LEFT" width="30%" >Final Importing Vessel:<BR></th>
				 <th  align="LEFT" width="20%" >Voy# <BR></th>
				 <th  align="LEFT" width="30%" >Est. date of transshipment<BR></th>
				 <th  align="LEFT" width="20%" >ETA:<B><BR>
				 '.$FinalDestination.'<br>'.$ETA.'</B></th>
			</tr>


			   <tr>

			   <th  align="LEFT"  width="'.$width.'%"><B>Container No.
			   (attachment if necessart)<br>'.$ContainerNumber.'</B></th>

                </tr>
				<tr>

			   <th  align="LEFT"  width="'.$width.'%"> 1. Importer of record number
			   (IRS #,Customs assigned#,or SS # as applicable): <BR>
			   </th>

                </tr>

				<tr>

			   <th  align="LEFT"  width="'.$width.'%"> 2. Buyer -compete name & address:
			   <BR>
			   <B>'.$ConsigneeName.','.$ConsigneeAddress.'</B><BR></th>

                </tr>
				<tr>

			   <th  align="LEFT"  width="'.$width.'%">3. Consignee number (IRS #,Customs assigned#,or SS # as applicable): <BR></th>

                </tr>

				<tr>

			   <th  align="LEFT"  width="'.$width.'%">4. Consignee name & address : <BR>
			   <B>'.$ConsigneeName.','.$ConsigneeAddress.'</B><BR></th>

                </tr>


				<tr>
			     <th  align="LEFT"  width="'.$width.'%">5. Ship to-complete name & address:</th>

                </tr>

				<tr>
			     <th  align="LEFT"  width="'.$width.'%">6. *Manufacture (Or Supplier)-Complete name & address:
				 <b><br>   '.$ProcsrName.'<br>   '.$ProcsrAddress.'</b></th>

                </tr>

				<tr>
			     <th  align="LEFT"  width="'.$width.'%">7. *Seller -Complete name & address:
				 <B><BR>   '.$ExpName.',<BR>  '.$ExpAddress.'<BR></B></th>

                </tr>

				<tr>
			     <th  align="LEFT"  width="'.$width.'%">8. *Container Stuffing location-Complete name & address:
				 <b><br>    '.$ProcsrName.'<br>   '.$ProcsrAddress.'</b></th>

                </tr>

				<tr>
			     <th  align="LEFT"  width="'.$width.'%">9. *Consolidator(if applicable)-Complete name & address:
				<B><BR>   '.$ExpName.',<BR>  '.$ExpAddress.'<BR></B></th>

                </tr>

				<tr>
				 <th  align="LEFT" width="'.$width.'%" >10. *Country of Origin : ' . saimeReportHtml($iconn, $PoID, 'CountryOfOrigin', 'INDIA') . '
				 <BR></th>

			   </tr>

			    <tr>
				 <th  align="LEFT" width="'.$width.'%" >11. Detailed commodity description or HTS number:

				 <BR>
				 <BR>PACKING :  BRAND :
				 <BR>    <B>TOTAL:</B>'.$TotalMcs.'
				 <BR>    <B>HS CODE:</B>
				 </th>

			   </tr>





			  ';


			$content .= '</table>';


	return $content;

}

?>
