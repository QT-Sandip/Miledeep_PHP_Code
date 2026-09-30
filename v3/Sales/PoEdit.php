<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/SalesNew/Constants.php");
include("Includes/IncludePage.php");


$PageTitleFolder = "Sales";
$PageTitle = "Proforma Invoice Edit";
$DocType = 'PI';
$backurl = $_GET["backurl"]; //added by uma(29-12-2023) to update mcs
// $ConsolidateGrossweightPopup=$_SESSION["ConsolidateGrossweightPopup"];//by uma(28-02-2024)
$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$ConsolidateGrossweightPopup = $ArrayClientConfigData['ConsolidateGrossweightPopup']; //by uma(28-02-2024)
$HasPIOtherTax = $ArrayClientConfigData['HasPIOtherTax'];
$EnableCustomInvoicePoData = $ArrayClientConfigData['enableCustomInvoicePoData'];
// print_r($EnableCustomInvoicePoData); exit;
//  $ConsolidateGrossweightPopup;

$ArrayClientInvoiceConfigData = json_decode(json_encode($_SESSION['ClientInvoiceConfigData']), true);
$HasFrozenWeight = $ArrayClientInvoiceConfigData['ShowFrozenWeight'];
$ShowPlaceOfReceipt = $ArrayClientInvoiceConfigData['ShowPlaceOfReceipt'];

$PreparedBy = $_SESSION["UserID"];


$POID = $_GET['id'];
$INVOICE_ID = $POID;
$PostedUserId = $_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));

$ClientId = $_SESSION['ClientID'];
$UnitPriceLimit = ($ClientId === 'DFLV3') ? 'step="0.0000001"' : '0.001';

/**uma grossweight popup*/
if ($ConsolidateGrossweightPopup == 0) {
    $SqlPackingStyle = "SELECT ID as PoGradeID FROM `ScrmPoGradeData` WHERE InvId='$POID' order by ID asc";
} else {
    $SqlPackingStyle = "SELECT DISTINCT(PackingStyleID) as PoGradeID FROM `ScrmPoGradeData` WHERE InvId='$POID' order by ID asc";
}
//$SqlPackingStyle = "SELECT ID as PoGradeID FROM `ScrmPoGradeData` WHERE InvId='$POID'";
$StmtPackingStyle = $iconn->query($SqlPackingStyle);
// echo $SqlPackingStyle; exit;

$AryPackingStyleIds = array();
while ($RowPackingStyle = $StmtPackingStyle->fetch_array()) {
    $AryPackingStyleIds[] = $RowPackingStyle['PoGradeID'];
}
// echo json_encode($AryPackingStyleIds, JSON_PRETTY_PRINT);
// exit;
/**uma */

$SqlCount = "SELECT * FROM `ScrmCustomInvoices` WHERE PiID='$POID'";
$StmtCount = $iconn->query($SqlCount);
$RowCustomInvoice = $StmtCount->fetch_array();
$CInvDate = $RowCustomInvoice['CInvDate'];

$SqlUom = "SELECT * FROM ScrmUnitsMaster WHERE Status=1";
$StmtUom = $iconn->query($SqlUom);
// $ARY_OPTIONS=array();
// WHILE($ROW=$StmtUom->FETCH_ARRAY()){
// 	$ARY_OPTIONS[]=$ROW;
// }

$poTable = "ScrmPIData";
$sqlTest = "SELECT * FROM ScrmPoData  WHERE PoID='$POID'";
$stmtSqlTest = $iconn->query($sqlTest);
$rowCount = $stmtSqlTest->num_rows;
if ($rowCount > 0) {
    $poTable = "ScrmPoData";
}
$sqlPIData = "SELECT * FROM $poTable  WHERE PoID='$POID'";
$stmtPIData = $iconn->query($sqlPIData);
$RowPIData = $stmtPIData->fetch_array();
// echo json_encode($RowPIData, JSON_PRETTY_PRINT);exit;

$Country = $RowPIData['DestinationCountry'];
$InvoiceNo = $RowPIData['InvNo'];
$ExpID = $RowPIData['ExporterID'];

$SqlImporter = "SELECT * FROM `ScrmImporterOfRecord` WHERE Status=1";
$StmtImporter = $iconn->query($SqlImporter);

$SqlNotifyparty = "SELECT * FROM `ScrmNotifyparty` WHERE Status=1";
$StmtNotifyparty = $iconn->query($SqlNotifyparty);

$SqlExporter = "SELECT * FROM AdminExporterDetails WHERE ExpType in(1,3)";
$StmtExporter = $iconn->query($SqlExporter);

$SqlProcessor = "SELECT * FROM AdminExporterDetails WHERE ExpType in(2,3)";
$StmtProcessor = $iconn->query($SqlProcessor);

$SqlCertification = "SELECT * FROM `ScrmOrderCertification` WHERE Status=1";
$StmtCertification = $iconn->query($SqlCertification);

$SqlConsignee = "SELECT * FROM ScrmConsigneeDetails WHERE Status=1";
$StmtConsignee = $iconn->query($SqlConsignee);

$SqlBuyer = "SELECT * FROM ScrmBuyerDetails WHERE Status=1";
$StmtBuyer = $iconn->query($SqlBuyer);

$SqlAgent = "SELECT * FROM `ScrmPiAgentMaster` WHERE Status=1";
$StmtAgent = $iconn->query($SqlAgent);

$SqlBank = "SELECT * FROM ScrmBankDetailsMaster WHERE `BankStatus` = '1'";
$StmtBank = $iconn->query($SqlBank);

$SqlCurrency = "SELECT * FROM `ScrmCurrencyMaster` WHERE `Status` = '1'";
// $SqlCurrency = "SELECT * FROM `ScrmCurrencyMaster`";
$StmtCurrency = $iconn->query($SqlCurrency);

$SqlCountryList = "SELECT * FROM ScrmCountriesList";
$StmtCountryList = $iconn->query($SqlCountryList);

while ($CountryRow = $StmtCountryList->fetch_array()) {
    $Countries[] = $CountryRow;
}

$SqlPaymentTerms = "SELECT * FROM ScrmPaymentTerms";
$StmtPaymentTerms = $iconn->query($SqlPaymentTerms);

$SqlAdminUsers = "SELECT * FROM AdminUserDetails WHERE DepartmentID=9 and PoUserStatus=1";
$StmtAdminUsers = $iconn->query($SqlAdminUsers);

$SqlPorts1 = "SELECT * FROM ScrmPortCities WHERE IsPortofLoading=1";
$StmtPorts1 = $iconn->query($SqlPorts1);

$SqlUltimateConsignee = "SELECT * FROM ScrmUltimateConsigneeDetails WHERE Status=1";
$StmtUltimateConsignee = $iconn->query($SqlUltimateConsignee);


$SqlPorts = "SELECT * FROM ScrmPortCities where Country='$Country'";
$StmtPorts = $iconn->query($SqlPorts);

$SqlShipmentTerms = "SELECT * FROM `ScrmShipmentTermsMaster` ";
$StmtShipmentTerms = $iconn->query($SqlShipmentTerms);

$sqlProductionPlant = "SELECT ProcessingStationID,StationName,FullName,ProdCode FROM `RmProcessingStations` WHERE StationStatus=1";
$stmtProductionPlant = $iconn->query($sqlProductionPlant);

$sqlPackingStyles = "SELECT * FROM `AdminPackingStyles` Where Status='1'";
$stmtPackingStyles = $iconn->query($sqlPackingStyles);

$packingStylesMap = [];
if ($stmtPackingStyles && $stmtPackingStyles->num_rows > 0) {
    while ($row = $stmtPackingStyles->fetch_assoc()) {
        $packingStylesMap[$row['PCKID']] = $row;
    }
}

while ($RowPo = $StmtPorts->fetch_array()) {
    $AryPorts[] = $RowPo;
}

$AccAval = true;
$CYear = date("Y");
$AYear = $CYear + 1;
while ($AccAval) {
    $PoRefId = generateAccountId();
    //$PINo="SME/".$CYear.'-'.$AYear.'/'.$PoRefId;
    $sql = "SELECT count(*) as Total FROM `$TblScrmPoData` where PoRefId='$PoRefId'";
    $sqlca = $iconn->query($sql);
    $rlt = mysqli_fetch_array($sqlca, MYSQLI_ASSOC);
    if ($rlt['Total'] == 0) {
        $AccAval = false;
    }
}

function generateAccountId()
{
    $generator = "1357902468";
    $result = "";
    for ($i = 1; $i <= 5; $i++) {
        $result .= substr($generator, (rand() % (strlen($generator))), 1);
    }

    return $result;
}

extract($_POST);
extract($_GET);


if (isset($_POST['submit'])) {
    $existingPiData = legacyAuditFetchRowById('ScrmPIData', 'PoID', $POID, 'Sales');
    $existingPoData = legacyAuditFetchRowById('ScrmPoData', 'PoID', $POID, 'Sales');

    $SqlCountryType = "SELECT CountryType FROM `ScrmCountriesList` where id='$DestinationCountry' ";
    $StmtCountryType = $iconn->query($SqlCountryType);
    $RowCountryType = $StmtCountryType->fetch_array();

    $TypeCountry = $RowCountryType['CountryType'];




    $YEAR = date("Y");
    $PoStatus = '0';



    $LoginId = $_SESSION["r_id"];
    $InvNo = sprintf('%03d', $InvNo);
    /***uma(06/07/2022)**/


    //if($InvoiceNo==$InvNo){
    /***uma(06/07/2022)**/
    if ($InvoiceNo == $InvNo && $ExpID == $ExporterID) {
        /***uma(10/04/2023)**/
        //	$InvNumFull=getFullPiInvoiceNumbertoSave($ExporterID,$InvDate,$InvNo);
        $InvNumFull = getInvoiceNumber($iconn, $ExporterID, $ProcessorID, $InvDate, $InvNo, $DocType);
        $sql_update = "UPDATE `ScrmPIData` SET `InvNo`='$InvNo',`InvNumFull`='$InvNumFull',`InvDate`='$InvDate',
`ExporterID`='$ExporterID',`ProcessorID`='$ProcessorID',`ConsigneeID`='$ConsigneeID',
`BuyerID`='$BuyerID',`PoNo`='$PoNo',`PoDate`='$PoDate',`DestinationCountry`='$DestinationCountry',`TypeCountry`='$TypeCountry',
`Year`='$YEAR',`BankID`='$BankID',`PortOfDischarge`='$PortOfDischarge',
`FinalDestination`='$FinalDestination',`PaymentTerms`='$ByPayment',`ShippingTerms`='$ShippingTerms',`DeliveryTerms`='$DeliveryTerms',`PreparedBy`='$PreparedBy',`PRInstrctns`='$PRInstrctns',`QtyUnit`='$QtyUnit',
`UnitCurrency`='$UnitCurrency',`PortOfDischarge`='$PortOfDischarge',`ShipByDate`='$ShipByDate',`Remarks`='$Remarks',`ImporterID`='$ImporterID',`NotifyPartyID`='$NotifyPartyID',`ReferenceNo`='$ReferenceNo',`Agent`='$Agent',`Certification`='$Certification',
`PlaceofReceipt`='$PlaceofReceipt',
`PortofLoading`='$PortofLoading',`ShowBank`='$ShowBank',
`UltimateConsigneeID`='$UltimateConsigneeID',`ShipByDateTextformat`='$ShipByDateTextformat', `ShowFrozenWeight`='$ShowFrozenWeight',
`Freight`='$Freight' WHERE PoID='$POID'";

        $SqlPoUpdate = "UPDATE `ScrmPoData` SET 
 `InvNo`='$InvNo', `InvNumFull`='$InvNumFull',`InvDate`='$InvDate',
 `ExporterID`='$ExporterID',`ProcessorID`='$ProcessorID',
 `LotNo`='$LotNo',`SciName`='$SciName',`ConsigneeID`='$ConsigneeID',
 `BuyerID`='$BuyerID',`PoNo`='$PoNo',`PoDate`='$PoDate',
 `DestinationCountry`='$DestinationCountry',`TypeCountry`='$TypeCountry',
 `Year`='$YEAR',`BankID`='$BankID',`PortOfDischarge`='$PortOfDischarge',
 `FinalDestination`='$FinalDestination',`PaymentTerms`='$ByPayment',`ShippingTerms`='$ShippingTerms',
 `DeliveryTerms`='$DeliveryTerms',`PreparedBy`='$PreparedBy',`PRInstrctns`='$PRInstrctns',
 `QtyUnit`='$QtyUnit',`UnitCurrency`='$UnitCurrency',
 `ShipByDate`='$ShipByDate',`Remarks`='$Remarks',`ImporterID`='$ImporterID',
 `NotifyPartyID`='$NotifyPartyID',`ReferenceNo`='$ReferenceNo',
 `Agent`='$Agent',`Certification`='$Certification',
 `PlaceofReceipt`='$PlaceofReceipt',
 `PortofLoading`='$PortofLoading',`ShowBank`='$ShowBank',
 `UltimateConsigneeID`='$UltimateConsigneeID',`ShipByDateTextformat`='$ShipByDateTextformat',
 `ShowFrozenWeight`='$ShowFrozenWeight', `Freight`='$Freight'
 WHERE PoID ='$POID'";


        if ($EnableCustomInvoicePoData == 1) {
            if (empty($CInvDate)) {
                $stmt1 = $iconn->query($sql_update);
            }
            $StmtPoUpdate = $iconn->query($SqlPoUpdate);
        } else {
            $stmt1 = $iconn->query($sql_update);
            $StmtPoUpdate = $iconn->query($SqlPoUpdate);
        }


        //$INV_ID = $iconn->insert_id;





        if ($stmt1 || $StmtPoUpdate) {
            if ($stmt1) {
                legacyAuditLogUpdateById('ScrmPIData', 'PoID', $POID, $existingPiData, $_POST, array(
                    'ReferenceNo' => $POID,
                    'Remarks' => 'Proforma invoice updated',
                ), 'Sales');
            }
            if ($StmtPoUpdate) {
                legacyAuditLogUpdateById('ScrmPoData', 'PoID', $POID, $existingPoData, $_POST, array(
                    'ReferenceNo' => $POID,
                    'Remarks' => 'PO master updated from proforma edit',
                ), 'Sales');
            }



            //header('Location: PoBreakUp.php?id='.$INV_ID);
            //header('Location: PIList.php');
            echo '<script language="javascript" type="text/javascript"> 
		alert("Proforma Updated");
                 window.close();
        </script>';
        }
    } else {
        $StartYear = date('Y', strtotime($InvDate));

        $EndYear = $StartYear + 1;
        $FinStartDate = $StartYear . '-04-01';
        $FinEndDate = $EndYear . '-03-31';

        $InvNumFull = getFullPiInvoiceNumbertoSave($ExporterID, $InvDate, $InvNo);

        $sqlCount = "SELECT count(*) as Count FROM `$TblScrmPoData` where InvDate BETWEEN '$FinStartDate' and '$FinEndDate' and
	InvNo='$InvNo' and ExporterID='$ExporterID' and Status not in(2,4,7)";

        $StmtCount = $iconn->query($sqlCount);
        $RowCount = $StmtCount->fetch_array();
        if ($RowCount['Count'] != 0) {
            echo '<script language="javascript" type="text/javascript"> 
alert("Update Failed  Invoice Number already exists");
                window.location = "PIList.php";
        </script>';
        } else {
            $sql_update = "UPDATE `ScrmPIData` SET `InvNo`='$InvNo',`InvNumFull`='$InvNumFull',`InvDate`='$InvDate',
`ExporterID`='$ExporterID',`ProcessorID`='$ProcessorID',`LotNo`='$LotNo',`SciName`='$SciName',`ConsigneeID`='$ConsigneeID',
`BuyerID`='$BuyerID',`PoNo`='$PoNo',`PoDate`='$PoDate',`DestinationCountry`='$DestinationCountry',`TypeCountry`='$TypeCountry',
`Year`='$YEAR',`BankID`='$BankID',`PortOfDischarge`='$PortOfDischarge',
`FinalDestination`='$FinalDestination',`PaymentTerms`='$ByPayment',`ShippingTerms`='$ShippingTerms',`DeliveryTerms`='$DeliveryTerms',
`PreparedBy`='$PreparedBy',`PRInstrctns`='$PRInstrctns',`QtyUnit`='$QtyUnit',`UnitCurrency`='$UnitCurrency',
`PortOfDischarge`='$PortOfDischarge',`ShipByDate`='$ShipByDate',`Remarks`='$Remarks',`ImporterID`='$ImporterID',
`NotifyPartyID`='$NotifyPartyID',`ReferenceNo`='$ReferenceNo',`Agent`='$Agent',`Certification`='$Certification',
`PlaceofReceipt`='$PlaceofReceipt',
`PortofLoading`='$PortofLoading',`ShowBank`='$ShowBank',`UltimateConsigneeID`='$UltimateConsigneeID',`ShipByDateTextformat`='$ShipByDateTextformat', `ShowFrozenWeight`='$ShowFrozenWeight'
 WHERE PoID='$POID'";

            $SqlPoUpdate = "UPDATE `ScrmPoData` SET 
    `InvNo`='$InvNo',`InvNumFull`='$InvNumFull',`InvDate`='$InvDate',
    `ExporterID`='$ExporterID',`ProcessorID`='$ProcessorID',
    `ConsigneeID`='$ConsigneeID',
    `BuyerID`='$BuyerID',`PoNo`='$PoNo',`PoDate`='$PoDate',
    `DestinationCountry`='$DestinationCountry',`TypeCountry`='$TypeCountry',
    `Year`='$YEAR',`BankID`='$BankID',`PortOfDischarge`='$PortOfDischarge',
    `FinalDestination`='$FinalDestination',`PaymentTerms`='$ByPayment',`ShippingTerms`='$ShippingTerms',
    `DeliveryTerms`='$DeliveryTerms',`PreparedBy`='$PreparedBy',`PRInstrctns`='$PRInstrctns',
    `QtyUnit`='$QtyUnit',`UnitCurrency`='$UnitCurrency',
    `ShipByDate`='$ShipByDate',`Remarks`='$Remarks',`ImporterID`='$ImporterID',`NotifyPartyID`='$NotifyPartyID' 
    ,`ReferenceNo`='$ReferenceNo',`Agent`='$Agent',`Certification`='$Certification',`PlaceofReceipt`='$PlaceofReceipt',`PortofLoading`='$PortofLoading',
    `ShowBank`='$ShowBank',`UltimateConsigneeID`='$UltimateConsigneeID',`ShipByDateTextformat`='$ShipByDateTextformat',
    `ShowFrozenWeight`='$ShowFrozenWeight'
    WHERE PoID ='$POID'";


            if ($EnableCustomInvoicePoData == 1) {
                if (empty($CInvDate)) {
                    $stmt1 = $iconn->query($sql_update);
                }
                $StmtPoUpdate = $iconn->query($SqlPoUpdate);
            } else {
                $stmt1 = $iconn->query($sql_update);
                $StmtPoUpdate = $iconn->query($SqlPoUpdate);
            }


            if ($stmt1 || $StmtPoUpdate) {
                if ($stmt1) {
                    legacyAuditLogUpdateById('ScrmPIData', 'PoID', $POID, $existingPiData, $_POST, array(
                        'ReferenceNo' => $POID,
                        'Remarks' => 'Proforma invoice updated',
                    ), 'Sales');
                }
                if ($StmtPoUpdate) {
                    legacyAuditLogUpdateById('ScrmPoData', 'PoID', $POID, $existingPoData, $_POST, array(
                        'ReferenceNo' => $POID,
                        'Remarks' => 'PO master updated from proforma edit',
                    ), 'Sales');
                }



                echo '<script language="javascript" type="text/javascript"> 
		alert("Proforma Updated");
                 window.close();
        </script>';
            }
        }
    }
}




$SqlPI = "SELECT * FROM `$poTable`   WHERE PoID='$INVOICE_ID'";

$StmtPI = $iconn->query($SqlPI);
$RowPI = $StmtPI->fetch_array();
$PoRefId = $RowPI['PoRefId'];
$QtyUnit = $RowPI['QtyUnit'];
$UnitCurrency = $RowPI['UnitCurrency'];


$SqlPIRef = "SELECT * FROM `$poTable`   WHERE PoRefId='$PoRefId'";

$StmtPIRef = $iconn->query($SqlPIRef);
$RowCurrency = $StmtPIRef->fetch_array();
$UnitCurrency = $RowCurrency['UnitCurrency'];

$StmtPIRef = $iconn->query($SqlPIRef);


$sql_packing = "SELECT * FROM `AdminPackingStyles` Where Status='1' 
order by abs(PackingStyle) Asc  limit 100";

$stmt_packing = $iconn->query($sql_packing);



$SQL_PO_GRADE_DATA = "SELECT * FROM `ScrmPoGradeData` as PGD
 left join ScrmPIVarietyList as PVL on PGD.VarietyID=PVL.VarietyID
 left join AdminPackingStyles as APS on APS.PCKID=PVL.PackingStyle
 where InvId='$INVOICE_ID'  ORDER BY ID ASC";
$STMT_PO_GRADE_DATA = $iconn->query($SQL_PO_GRADE_DATA);

$GRADE_ROW = $STMT_PO_GRADE_DATA->FETCH_ARRAY();

$STMT_PO_GRADE_DATA = $iconn->query($SQL_PO_GRADE_DATA);
/*
$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='CS' order by ABS(OPTIONS) asc ";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
    $ARY_OPTIONS[]=$ROW;
}

*/

$SqlPIVarietyList = "SELECT * FROM `ScrmPIVarietyList` as  SPV 
	left join ScrmBrandsMaster as  SBM on SBM.BrandID =SPV.BrandName
	left join AdminPackingStyles as  APS on APS.PCKID =SPV.PackingStyle
	left join ScrmPreparationMaster as  SPM on SPM.ShortCode =SPV.PreparationType
		where SPV.Status='1'";
$StmtPIVarietyList = $iconn->query($SqlPIVarietyList);

function checkEmpty($Value)
{
    if ($Value == '') {
        $Value = '';
    } else {
        $Value = ' ' . $Value;
    }
    return $Value;
}

$AryVarietyList = array();

while ($RowPIVarietyList = $StmtPIVarietyList->fetch_array()) {
    $AryItem = array();
    $PreparationName = checkEmpty($RowPIVarietyList['PreparationName']);
    $TypeProduct = checkEmpty($RowPIVarietyList['TypeProduct']);
    $ProductFormm = checkEmpty($RowPIVarietyList['FullName']);
    $FreezingTypee = checkEmpty($RowPIVarietyList['FreezingType']);
    $PreparationTypee = checkEmpty($RowPIVarietyList['PreparationType']);
    $GlazePercc = checkEmpty($RowPIVarietyList['GlazePerc']);
    $Glazee = checkEmpty($RowPIVarietyList['Glaze']);
    $Treatmentt = checkEmpty($RowPIVarietyList['Treatment']);
    $Grade = checkEmpty($RowPIVarietyList['Grade']);
    $PackingStyle = checkEmpty($RowPIVarietyList['PackingStyle']);
    $BrandName = checkEmpty($RowPIVarietyList['BrandName']);
    $PreparationDesc = checkEmpty($RowPIVarietyList['PreparationDesc']);
    $ProductDescription = checkEmpty($RowPIVarietyList['ProductDescription']);
    $DeviSUPC = checkEmpty($RowPIVarietyList['DeviSUPC']);
    $BuyerSUPC = checkEmpty($RowPIVarietyList['BuyerSUPC']);


    // $Data=$Species.$ProductFormm.$FreezingTypee.$PreparationTypee.$GlazePercc.$Glazee.$Treatmentt.$Grade.$PackingStyle.$BrandName; 
    $Description = $DeviSUPC . '-' . $Grade . $PackingStyle . $ProductDescription . $FreezingTypee . $GlazePercc . '%' . $Glazee . $Treatmentt . $BrandName;
    $AryItem['Name'] = $Description;
    $AryItem['ID'] = $RowPIVarietyList['VarietyID'];
    $AryVarietyList[] = $AryItem;
}


$sql_invoice = "SELECT * FROM `$poTable` where PoID='$INVOICE_ID'";
$stmt_invoice = $iconn->query($sql_invoice);
$invoice_details = $stmt_invoice->fetch_array();

$UnitCurrency = $invoice_details['UnitCurrency'];



extract($_POST);
extract($_GET);



if (isset($_POST['submit'])) {
    $count = count($SelectedInvId);
    $InvId = implode(',', $SelectedInvId);

    $SqlPIRef = "SELECT * FROM `$poTable`   WHERE PoID in ($InvId)";

    $StmtPIRef = $iconn->query($SqlPIRef);

    //echo $VarietyId ;exit;

    $SqlPIVarietyList = "SELECT * FROM `ScrmPIVarietyList` as SPV 
	left join ScrmBrandsMaster as  SBM on SBM.BrandID =SPV.BrandName
	left join AdminPackingStyles as  APS on APS.PCKID =SPV.PackingStyle
	WHERE VarietyID='$VarietyId'";
    $StmtPIVarietyList = $iconn->query($SqlPIVarietyList);
    $RowPIVarietyList = $StmtPIVarietyList->fetch_array();

    //$VarietyName=$RowPIVarietyList['FullName'].' '.$FREEZING_TYPE; 
    $VarietyName = $FullName;
    $Glaze = $RowPIVarietyList['Glaze'];
    $GlazePerc = $RowPIVarietyList['GlazePerc'];
    $Treatment = $RowPIVarietyList['Treatment'];
    $FreezingType = $RowPIVarietyList['FreezingType'];
    $GRADE = $RowPIVarietyList['Grade'];
    $PackingStyleID = $RowPIVarietyList['PCKID'];
    $BRAND = $RowPIVarietyList['BrandName'];




    $D_O_ANALYSIS = date("d-m-Y", strtotime($D_O_ANALYSIS));
    $D_O_COMPLEATION = date("d-m-Y", strtotime($D_O_COMPLEATION));


    $ARY_GRADE = explode(',', $GRADE);
    $ARY_MCS = explode(',', $MCS);
    $ARY_AMOUNT_PER_LB = explode(',', $Price);

    if (count($ARY_GRADE) == count($ARY_MCS) && count($ARY_GRADE) == count($ARY_AMOUNT_PER_LB)) {

        for ($I = 0; $I < count($ARY_GRADE); $I++) {

            $GRADE = $ARY_GRADE[$I];
            $MCS = $ARY_MCS[$I];
            $Price = $ARY_AMOUNT_PER_LB[$I];


            if ($InvId != '') {
                for ($i = 0; $i < $count; $i++) {
                    while ($RowPIRef = $StmtPIRef->fetch_array()) {
                        $Inv_Id = $RowPIRef['PoID'];

                        $sql_org_insert = "INSERT INTO `ScrmPoGradeData`(`Grade`, `Mcs`, `FreezingType`, `InvId`, `PackingStyleID`,
	 `GlazeType`, `Variety`, `GlazePerc`, `Treatment`, `Brand`, `Price`,`BAP`,`VarietyID`,`BuyerItemCode`,`Description`,`CodeType`,`DdpPrice`) VALUES (
	 '$GRADE','$MCS','$FreezingType','$Inv_Id','$PackingStyleID','$Glaze','$VarietyName','$GlazePerc',
	 '$Treatment','$BRAND','$Price','$BAP','$VarietyId','$BuyerItemCode','$Description','$CodeType','$DdpPrice')";
                        $stmt1 = $iconn->query($sql_org_insert);
                        if ($stmt1) {
                            $insertedPoGradeId = $iconn->insert_id;
                            legacyAuditLogInsertById('ScrmPoGradeData', 'ID', $insertedPoGradeId, array(
                                'InvId' => $Inv_Id,
                                'VarietyID' => $VarietyId,
                                'Grade' => $GRADE,
                                'Mcs' => $MCS,
                            ), array(
                                'ReferenceNo' => $Inv_Id,
                                'Remarks' => 'PO grade row created from proforma edit',
                            ), 'Sales');
                        }
                    }
                }
            }
        }
    } else {
        echo '<script>alert("Grades And Break Up Mismatch") </script>';
    }
    //echo $iid;
    if ($stmt1) {

        /* $SQL_INV_UPDATE="UPDATE `INVOICE_DATA` SET 
                     `DEC_STATUS`='1'
                     WHERE ID='$INVOICE_ID'";


         $STMT_INV_UPDATE=$iconn->query($SQL_INV_UPDATE);

             */
        header('Location: PoBreakUp.php?id=' . $INVOICE_ID);
    }
}


?>


<!DOCTYPE html>
<html lang="en">

<head>

    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>

</head>

<body data-sidebar="dark">


    <?php
    include($IncludesPath . "PreLoader.php"); ?>

    <!-- Begin page -->
    <div id="layout-wrapper">


        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php");
        ?>



        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">
                    <?php include "PopUpTable.php"; ?>

                    <?php
                    if ($Status != '') {
                        //Show Alert 
                        ?>
                    <?php } ?>

                    <?php include($IncludesPath . "page-title.php"); ?>




                    <div class="row">

                        <div class="col-12">
                            <div class="card">

                                <form role="form" method="post" enctype="multipart/form-data" id="frmPoEdit">
                                    <div class="card-body">

                                        <div class="row">


                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Select Exporter <span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="ExporterID"
                                                    id="ExporterID" disabled>
                                                    <option value="">Select Exporter</option>
                                                    <?php while ($RowExporter = $StmtExporter->fetch_array()) { ?>

                                                        <option <?php if ($RowPIData['ExporterID'] == $RowExporter['ExpID']) { ?> selected="selected" <?php } ?>
                                                            Value="<?php echo $RowExporter['ExpID']; ?>">
                                                            <?php echo $RowExporter['ExpName']; ?>
                                                        </OPTION>
                                                    <?php } ?>

                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Select Processor <span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="ProcessorID"
                                                    id="ProcessorID" disabled>
                                                    <option value="">Select Processor</option>
                                                    <?php while ($RowProcessor = $StmtProcessor->fetch_array()) { ?>

                                                        <option <?php if ($RowPIData['ProcessorID'] == $RowProcessor['ExpID']) { ?> selected="selected" <?php } ?>
                                                            Value="<?php echo $RowProcessor['ExpID']; ?>"
                                                            data-invcode="<?php echo $RowProcessor['InvShortCode']; ?>">
                                                            <?php echo $RowProcessor['ExpName'] . '(' . $RowProcessor['ExpApprovalNo'] . ')'; ?>
                                                        </OPTION>

                                                    <?php } ?>

                                                </select>
                                            </div>
                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputEmail1">Reference Number</label>
                                                <input type="text" class="form-control" name='ReferenceNo'
                                                    id="ReferenceNo" placeholder="Enter Reference Number"
                                                    value="<?php echo $RowPIData['ReferenceNo']; ?>" disabled>
                                            </div>

                                            <div class="form-group col-lg-3"></div>




                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputEmail1">Proforma Invoice No<span
                                                        style="color:red">*</span></label>
                                                <input required type="number" class="form-control" name='InvNo'
                                                    id="InvNo" placeholder="Enter Proforma Invoice No"
                                                    value="<?php echo $RowPIData['InvNo']; ?>" disabled>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputEmail1">Proforma Invoice Date<span
                                                        style="color:red">*</span></label>
                                                <input required type="date" onkeydown="return false"
                                                    value="<?php echo $RowPIData['InvDate']; ?>" class="form-control"
                                                    name='InvDate' id="InvDate" placeholder="Enter Proforma Invoice No "
                                                    disabled>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1"> P.O No</label>
                                                <input type="text" class="form-control" name='PoNo'
                                                    value="<?php echo $RowPIData['PoNo']; ?>" id="PoNo"
                                                    placeholder="Enter P.O Number" disabled>
                                            </div>

                                            <?php  //echo date("Y-m-d");    
                                            ?>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1"> P.O Date</label>
                                                <input type="date" onkeydown="return false"
                                                    value="<?PHP echo $RowPIData['PoDate']; ?>" class="form-control"
                                                    name='PoDate' id="PoDate" placeholder="Enter P.O Date" disabled>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Consignee<span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="ConsigneeID"
                                                    id="ConsigneeID" disabled>
                                                    <option value="">Select Consignee</option>
                                                    <?php while ($RowConsignee = $StmtConsignee->fetch_array()) { ?>
                                                        <option <?php if ($RowPIData['ConsigneeID'] == $RowConsignee['ConID']) { ?> selected="selected" <?php } ?>
                                                            value="<?php echo $RowConsignee['ConID'] ?>">
                                                            <?php echo $RowConsignee['ConsigneeName'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Ultimate Consignee</label>
                                                <select class="form-control select2" name="UltimateConsigneeID"
                                                    id="UltimateConsigneeID" disabled>
                                                    <option value="">Select</option>
                                                    <?php while ($RowUltimateConsignee = $StmtUltimateConsignee->fetch_array()) { ?>
                                                        <option <?php if ($RowPIData['UltimateConsigneeID'] == $RowUltimateConsignee['ConID']) { ?> selected="selected" <?php } ?>
                                                            value="<?php echo $RowUltimateConsignee['ConID'] ?>">
                                                            <?php echo $RowUltimateConsignee['ConsigneeName'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>


                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Buyer Name</label>
                                                <select class="form-control select2" name="BuyerID" id="BuyerID"
                                                    disabled>
                                                    <option value="">Select Buyer</option>
                                                    <?php while ($RowBuyer = $StmtBuyer->fetch_array()) { ?>
                                                        <option <?php if ($RowPIData['BuyerID'] == $RowBuyer['BuyerID']) { ?>
                                                                selected="selected" <?php } ?>
                                                            value="<?php echo $RowBuyer['BuyerID'] ?>">
                                                            <?php echo $RowBuyer['BuyerName'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Notify Party</label>
                                                <select class="form-control select2" name="NotifyPartyID"
                                                    id="NotifyPartyID" disabled>
                                                    <option value="">Select</option>
                                                    <?php while ($RowNotifyparty = $StmtNotifyparty->fetch_array()) {
                                                        $NPartyAddress = mb_strimwidth($RowNotifyparty['NPartyAddress'], 0, 15, "..."); ?>
                                                        <option <?php if ($RowPIData['NotifyPartyID'] == $RowNotifyparty['NotifyPartyID']) { ?> selected="selected" <?php } ?>
                                                            value="<?php echo $RowNotifyparty['NotifyPartyID'] ?>">
                                                            <?php echo $RowNotifyparty['NotifyPartyName'] . ' (' . $NPartyAddress . ')' ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>



                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Importer of Record <span
                                                        style="color:red">*</span></label></label>
                                                <select class="form-control select2" name="ImporterID" required
                                                    id="ImporterID" disabled>
                                                    <!-- <option value="">Select</option> -->
                                                    <option value="0">NA</option>
                                                    <?php while ($RowImporter = $StmtImporter->fetch_array()) { ?>
                                                        <option <?php if ($RowPIData['ImporterID'] == $RowImporter['ImporterID']) { ?>
                                                                selected="selected" <?php } ?>
                                                            value="<?php echo $RowImporter['ImporterID'] ?>">
                                                            <?php echo $RowImporter['ImporterName'] ?>
                                                        </option>

                                                    <?php } ?>

                                                </select>
                                            </div>



                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Agent</label>
                                                <select class="form-control select2" name="Agent" id="Agent" disabled>
                                                    <option value="">Select</option>
                                                    <?php while ($RowAgent = $StmtAgent->fetch_array()) { ?>
                                                        <option <?php if ($RowPIData['Agent'] == $RowAgent['AgentID']) { ?>
                                                                selected="selected" <?php } ?>
                                                            value="<?php echo $RowAgent['AgentID'] ?>">
                                                            <?php echo $RowAgent['AgentName'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Certification</label>
                                                <select class="form-control select2" name="Certification"
                                                    id="Certification" disabled>
                                                    <option value="">Select</option>
                                                    <?php while ($RowCertification = $StmtCertification->fetch_array()) { ?>
                                                        <option <?php if ($RowPIData['Certification'] == $RowCertification['CerificationID']) { ?> selected="selected" <?php } ?>
                                                            value="<?php echo $RowCertification['CerificationID'] ?>">
                                                            <?php echo $RowCertification['CerificationName'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <?php
                                            if ($ShowPlaceOfReceipt == 1) {
                                                ?>
                                                <div class="form-group col-lg-3">
                                                    <label for="PlaceofReceipt">Place of Receipt</label>
                                                    <select class="form-control select2" name="PlaceofReceipt"
                                                        id="PlaceofReceipt">
                                                        <option value="">Select</option>
                                                        <?php while ($RowPorts = $StmtPorts1->fetch_array()) { ?>
                                                            <option <?php if ($RowPIData['PlaceofReceipt'] == $RowPorts['PortId']) { ?> selected="selected" <?php } ?>
                                                                value="<?php echo $RowPorts['PortId'] ?>">
                                                                <?php echo $RowPorts['Name'] ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <?php
                                                $StmtPorts1->data_seek(0);
                                            }
                                            ?>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Port of Loading</label>
                                                <select required class="form-control select2" name="PortofLoading"
                                                    id="PortofLoading" disabled>
                                                    <option value="">Select</option>
                                                    <?php while ($RowPorts = $StmtPorts1->fetch_array()) { ?>
                                                        <option <?php if ($RowPIData['PortofLoading'] == $RowPorts['PortId']) { ?> selected="selected" <?php } ?>
                                                            value="<?php echo $RowPorts['PortId'] ?>">
                                                            <?php echo $RowPorts['Name'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>


                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Destination Country<span
                                                        style="color:red">*</span></label>


                                                <select required class="form-control select2" name="DestinationCountry"
                                                    id="DestinationCountry" disabled>
                                                    <option value="">Select Country</option>
                                                    <?php
                                                    $DestinationCountry = $RowPIData['DestinationCountry'];

                                                    foreach ($Countries as $RowCountry) { ?>
                                                        <option value="<?php echo $RowCountry['id'] ?>" <?php if ($DestinationCountry == $RowCountry['id']) { ?>
                                                                selected="selected" <?php } ?>>
                                                            <?php echo strtoupper($RowCountry['name']) ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>






                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Port of Discharge<span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="PortOfDischarge"
                                                    id="PortOfDischarge" disabled>
                                                    <option value="">Select Port</option>
                                                    <?php
                                                    $PortOfDischarge = $RowPIData['PortOfDischarge'];
                                                    foreach ($AryPorts as $RowPort) { ?>
                                                        <option <?php if ($PortOfDischarge == $RowPort['PortId']) { ?>
                                                                selected="selected" <?php } ?>
                                                            value="<?php echo $RowPort['PortId'] ?>">
                                                            <?php echo $RowPort['Name'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>

                                            </div>


                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Final Destination Country</label>


                                                <select class="form-control select2" name="FinalDestinationCountry"
                                                    id="FinalDestinationCountry">
                                                    <option value="">Select Country</option>
                                                    <?php

                                                    $FinalDestinationCountry = $RowPIData['FinalDestinationCountry'];
                                                    foreach ($Countries as $RowCountry) { ?>
                                                        <option value="<?php echo $RowCountry['id'] ?>" <?php if ($FinalDestinationCountry == $RowCountry['id']) { ?>
                                                                selected="selected" <?php } ?>>
                                                            <?php echo strtoupper($RowCountry['name']) ?>
                                                        </option>
                                                    <?php } ?>

                                                </select>
                                            </div>

                                            <!--   <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> Final Destination<span style="color:red">*</span></label>
                  <input required type="text" class="form-control" name='FinalDestination' 
                  id="FinalDestination" placeholder="Enter Final Destination" value="<?php echo $RowPIData['FinalDestination']; ?>">
                </div> -->

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1"> Final Destination<span
                                                        style="color:red">*</span></label>

                                                <!--<input required type="text" class="form-control" name='FinalDestination' 
                  id="FinalDestination" placeholder="Enter Final Destination" value="<?php echo $RowPIData['FinalDestination']; ?>">
               -->

                                                <select required class="form-control select2" name="FinalDestination"
                                                    id="FinalDestination" disabled>
                                                    <option value="">Select Final Destination</option>
                                                    <?php
                                                    $FinalDestination = $RowPIData['FinalDestination'];
                                                    foreach ($AryPorts as $RowPort) { ?>
                                                        <option <?php if ($FinalDestination == $RowPort['PortId']) { ?>
                                                                selected="selected" <?php } ?>
                                                            value="<?php echo $RowPort['PortId'] ?>">
                                                            <?php echo $RowPort['Name'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>

                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">ShipBy Date</label>
                                                <input type="date" onkeydown="return false" class="form-control"
                                                    name="ShipByDate" id="ShipByDate" placeholder="Enter ShipBy Date"
                                                    Value="<?php echo $RowPIData['ShipByDate']; ?>" disabled></input>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">ShipBy Date 2 (Text)</label>
                                                <input type="text" class="form-control" name='ShipByDateTextformat'
                                                    id="ShipByDateTextformat" placeholder="Enter ShipBy Date Text"
                                                    value="<?php echo $RowPIData['ShipByDateTextformat']; ?>">
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Bank Details<span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="BankID" id="BankID"
                                                    disabled>
                                                    <option value="">Select Bank</option>
                                                    <?php
                                                    $BankID = $RowPIData['BankID'];
                                                    while ($RowBank = $StmtBank->fetch_array()) { ?>
                                                        <option <?php if ($BankID == $RowBank['ID']) { ?> selected="selected"
                                                            <?php } ?> value="<?php echo $RowBank['ID'] ?>">
                                                            <?php echo $RowBank['AccntName'] . ' - 
					 ' . $RowBank['BankName'] . '-' . $RowBank['AccountNumber'] . '-' . $RowBank['RoutingThrough'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>


                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputEmail1">Show Bank<span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="ShowBank"
                                                    id="ShowBank" disabled>
                                                    <option <?php if ($RowPIData['ShowBank'] == 0) { ?>
                                                            selected="selected" <?php } ?> value="0">No</option>
                                                    <option <?php if ($RowPIData['ShowBank'] == 1) { ?>
                                                            selected="selected" <?php } ?> value="1">Yes</option>

                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Payment Terms<span
                                                        style="color:red">*</span></label>

                                                <select required class="form-control select2" name="ByPayment"
                                                    id="ByPayment" disabled>
                                                    <option value="">Select Payment</option>
                                                    <?php
                                                    $PaymentTerms = $RowPIData['PaymentTerms'];

                                                    while ($RowPayment = $StmtPaymentTerms->fetch_array()) { ?>
                                                        <option <?php if ($PaymentTerms == $RowPayment['ID']) { ?>
                                                                selected="selected" <?php } ?>
                                                            value="<?php echo $RowPayment['ID'] ?>">
                                                            <?php echo $RowPayment['PaymentName'] . (($RowPayment['NoOfDays'] != 0) ? ' - ' . $RowPayment['ConditionDate'] . ' - (' . $RowPayment['NoOfDays'] . ' Days)' : '') ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>




                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputEmail1">Shipment Terms<span
                                                        style="color:red">*</span></label>

                                                <select required class="form-control select2" name="ShippingTerms"
                                                    id="ShippingTerms" disabled>
                                                    <?php
                                                    $ShippingTerms = $RowPIData['ShippingTerms'];
                                                    while ($RowShipterms = $StmtShipmentTerms->fetch_array()) { ?>

                                                        <option <?php if ($ShippingTerms == $RowShipterms['TermName']) { ?>
                                                                selected="selected" <?php } ?>
                                                            value="<?php echo $RowShipterms['TermName'] ?>">
                                                            <?php echo $RowShipterms['TermName'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>

                                            </div>





                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputEmail1">Delivery Terms<span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="DeliveryTerms"
                                                    id="DeliveryTerms" disabled>
                                                    <option value="">Select</option>
                                                    <option <?php if ($RowPIData['DeliveryTerms'] == 'CY/CY') { ?>
                                                            selected=="selected" <?php } ?> value="CY/CY">CY/CY</option>
                                                    <option <?php if ($RowPIData['DeliveryTerms'] == 'CY/SD') { ?>
                                                            selected=="selected" <?php } ?> value="CY/SD">CY/SD</option>
                                                    <option <?php if ($RowPIData['DeliveryTerms'] == 'FOB') { ?>
                                                            selected=="selected" <?php } ?> value="FOB">FOB</option>

                                                </select>
                                            </div>


                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputEmail1">Quantity Units<span
                                                        style="color:red">*</span></label>

                                                <select required class="form-control select2" name="QtyUnit"
                                                    id="QtyUnit" disabled>
                                                    <?php
                                                    $QtyUnit = $RowPIData['QtyUnit'];
                                                    //  var_dump($ARY_OPTIONS);
                                                    while ($RowUnit = $StmtUom->fetch_array()) { ?>
                                                        <option <?php if ($QtyUnit == $RowUnit['UnitCode']) { ?>
                                                                selected="selected" <?php } ?>
                                                            value="<?php echo $RowUnit['UnitCode'] ?>">
                                                            <?php echo $RowUnit['UnitName'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>

                                            </div>



                                            <!-- hello -->
                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Currency<span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="UnitCurrency"
                                                    id="UnitCurrency" disabled>
                                                    <option value="">Select Currency</option>

                                                    <?php while ($RowCurrency = $StmtCurrency->fetch_array()) { ?>
                                                        <option <?php if ($RowPIData['UnitCurrency'] == $RowCurrency['CurrencyCode']) { ?>
                                                                selected="selected" <?php } ?>
                                                            value="<?php echo $RowCurrency['CurrencyCode'] ?>">
                                                            <?php echo $RowCurrency['CurrencyName'] . ' - 
					                                    ' . $RowCurrency['CurrencyCode'] . '(' . $RowCurrency['CurrenySymbol'] . ')' ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>





                                            <!--<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> Prepared By<span style="color:red">*</span></label>
                 
                <select required class="form-control select2" name="PreparedBy" id="PreparedBy">
                      <option   value="">Select Prepared By</option>
                    <?php while ($RowAdminUsers = $StmtAdminUsers->fetch_array()) {
                        $UserName = $RowAdminUsers['UserName'];
                        $PreparedBy = $RowPIData['PreparedBy'];
                        ?>
             
                    <option <?php if ($PreparedBy == $UserName) { ?> selected="selected"  <?php } ?>
                    
                    value="<?php echo $UserName ?>"><?php echo $UserName ?></option>
                    <?php } ?>
                <select>
                </div> -->


                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Freight</label>
                                                <input type="text" class="form-control" name='Freight' id="Freight"
                                                    placeholder="Enter Freight"
                                                    value="<?php echo $RowPIData['Freight'] ?>" disabled>
                                            </div>

                                            <!-- Durga -->
                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Charge Name</label>
                                                <input type="text" class="form-control" name='ChargeName'
                                                    id="ChargeName" placeholder="Enter Charge Name"
                                                    value="<?php echo $RowPIData['ChargeName'] ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Charge Amount </label>
                                                <input type="text" class="form-control" name='ChargeAmount'
                                                    id="ChargeAmount" placeholder="Enter Charge Amount" maxlength="6"
                                                    value="<?php echo $RowPIData['ChargeAmount'] ?>">
                                            </div>

                                            <!-- <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">DDP Price </label>
                                                <input type="text" class="form-control" name='DdpPrice'
                                                    id="DdpPrice" placeholder="Enter Ddp Price " 
                                                    value="<?php echo $RowPIData['DdpPrice'] ?>">
                                            </div> -->

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputEmail1">Production Plant</label>
                                                <select class="form-control select2" name="ProductionPlant"
                                                    id="ProductionPlant" multiple="multiple">
                                                    <!-- <option value="">Select</option> -->
                                                    <?php
                                                    while ($RowPlant = $stmtProductionPlant->fetch_array()) {
                                                        // echo json_encode($RowPlant, JSON_PRETTY_PRINT);
                                                        // exit;
                                                        $selected = '';
                                                        $selectedIds = explode(",", $RowPIData['ProductionPlantID']);
                                                        if (in_array($RowPlant['ProcessingStationID'], $selectedIds)) {
                                                            $selected = 'selected="selected"';
                                                        }
                                                        ?>
                                                        <option <?php echo $selected; ?>
                                                            value="<?php echo $RowPlant['ProcessingStationID']; ?>"
                                                            data-prodCode="<?php echo $RowPlant['ProdCode'] ?>">
                                                            <?php echo $RowPlant['FullName'] . ' - ' . $RowPlant['StationName']; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <?php
                                            if ($HasPIOtherTax == 1) {

                                                ?>
                                                <div class="form-group col-lg-3">
                                                    <label for="exampleInputEmail1">Apply Other Tax</label>
                                                    <select required class="form-control select2" name="OtherCharges"
                                                        id="OtherCharges">
                                                        <option <?php if ($RowPIData['OtherCharges'] == 0) { ?>
                                                                selected="selected" <?php } ?> value="0">No</option>
                                                        <option <?php if ($RowPIData['OtherCharges'] == 1) { ?>
                                                                selected="selected" <?php } ?> value="1">Yes</option>

                                                    </select>
                                                </div>
                                                <?php
                                            }
                                            ?>

                                            <?php
                                            if ($HasFrozenWeight == 1) {
                                                ?>
                                                <div class="form-group col-lg-3">
                                                    <label for="ShowFrozenWeight">Show Frozen Weight<span
                                                            style="color:red">*</span></label>
                                                    <select required class="form-control select2" name="ShowFrozenWeight"
                                                        id="ShowFrozenWeight">
                                                        <option value="0" selected>No</option>
                                                        <option value="1">Yes</option>
                                                    </select>
                                                </div>
                                                <?php
                                            }
                                            ?>

                                            <!-- Durga -->
                                            <div class="form-group col-lg-12">
                                                <label for="exampleInputEmail1">Remarks</label>

                                                <textarea name="Remarks" class="form-control" rows="2" cols="50"
                                                    id="Remarks" placeholder="Enter Remarks"
                                                    disabled><?php echo $RowPIData['Remarks']; ?></textarea>


                                            </div>

                                        </div>




                                    </div>

                                    <!-- end col -->
                            </div> <!-- end row -->

                        </div> <!-- end card body-->
                    </div> <!-- end card -->













                    <div class="row">
                        <div class="col-12 text-right">
                            <button type="button" class="btn btn-primary AddNewItem" disabled style="
        width: 70px;
        position: relative;
        right: 0px;
        padding: 4px;
        margin-bottom: 5px;">Add Item</button>




                            <button id="AddGrossWtPerBrkUp" class="btn btn-warning" data-id="<?php echo $POID ?>" style="position: relative;
margin-bottom: 5px;" data-value="
               <?php echo $POID ?>" class="text">
                                <b> Add GrossWeight + </b></button>
                        </div>
                        <div class="col-12">
                            <div class="card">
                                <!-- <input type="text" class="form-control" hidden id="displayinformation" readonly > -->
                                <div class="card-body" id="ShowDataTable" style="overflow-x:auto;">


                                    <table id="key-datatable" data-paging="false" style="font-size: 12px;"
                                        class="table wrap w-100 dataTable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>ID</th>
                                                <th style="width : 15%">Variety</th>
                                                <!-- <th>Grade</th> -->
                                                <!-- <th>Packing Style</th> -->
                                                <!-- <th>Treatment</th>
                                                    <th>Freezing Type</th> -->
                                                <th style="width : 20%">Description</th>
                                                <th style="width : 90px;">Code</th>
                                                <th style="width : 90px;">Gross Weight<br>
                                                    &nbsp;(<?php echo $QtyUnit; ?>)
                                                </th>
                                                <th style="width : 100px;">Mcs</th>
                                                <?php if ($ArrayClientConfigData['HasSauce'] == 1) { ?>
                                                    <th style="width : 100px;"> Trays Qty </th>
                                                <?php } ?>
                                                <th style="width : 90px;">Unit Price<br>
                                                    &nbsp;(<?php echo $QtyUnit; ?>)
                                                </th>
                                                <th style="width : 90px;">DDP Price</th>
                                                <!-- <th> Rate Per Tray<br>
                                                    <?php echo $QtyUnitaa ?>
                                                </th> -->
                                                <?php if ($ArrayClientConfigData['HasSauce'] == 1) { ?>
                                                    <th style="width : 100px;">Cups</th>
                                                    <th style="width : 100px;">Cups Price<br>
                                                        &nbsp;(<?php echo $QtyUnit; ?>)
                                                    </th>
                                                <?php } ?>
                                                <th style="width : 100px;">Amount in<br>
                                                    &nbsp;(<?php echo $UnitCurrency; ?>)
                                                </th>
                                                <th style="width : 50px;">Actions </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            //echo $sql;
                                            $TotalMcs = 0;
                                            $TotalAmount = 0;
                                            $aryPoGradeId = array();
                                            $PoReceived = '0';
                                            while ($row = $STMT_PO_GRADE_DATA->fetch_array()) {
                                                $aryPoGradeId[] = $PoGradeId = $row['ID'];
                                                $PackingID = $row['PackingStyleID'];

                                                // $SqlPckStyl="SELECT * FROM `AdminPackingStyles` WHERE PCKID ='$PackingID'";
                                                $SqlTotal = "SELECT PS.PackingStyle,
                                                            sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
                                                            sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs,
                                                            sum(Mcs*TotalSlabs*Price ) as PriceBags,
                                                            HasSauce
                                                            FROM `ScrmPoGradeData` 
                                                            as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID
                                                             left join ScrmPIVarietyList as PVL on PGD.VarietyID=PVL.VarietyID WHERE PGD.InvId='$INVOICE_ID' 
                                                            and ID='$PoGradeId'";
                                                $StmtTotal = $iconn->query($SqlTotal);
                                                $RowTotal = $StmtTotal->fetch_array();

                                                $SqlPoData = "SELECT * FROM `ScrmPoData` where PoID = '$INVOICE_ID'";
                                                $StmtPoData = $iconn->query($SqlPoData);
                                                $PoReceived = $StmtPoData->num_rows > 0 ? '1' : '0';

                                                if ($QtyUnit == 'BAGS') {
                                                    //$Qty=$RowTotal['MCS']*$RowTotal['TotalSlabs'];
                                                    $Price = $RowTotal['PriceBags'];
                                                } else if ($invoice_details['QtyUnit'] == 'KGS') {
                                                    $Weight = $RowTotal['NetWgtKgs'];
                                                    $Price = $RowTotal['PriceKgs'];
                                                } else {
                                                    $Weight = $RowTotal['NetWgtLbs'];
                                                    $Price = $RowTotal['PriceLbs'];
                                                }


                                                /*$StmtPckStyl=$iconn->query($SqlPckStyl); 
                                                $RowPckStyl=$StmtPckStyl->fetch_array();
                                                $Packing=$RowPckStyl['PackingStyle'];*/
                                                $TotalMcs += $row['Mcs'];
                                                $Packing = $RowTotal['PackingStyle'];
                                                $TotalAmount += $Price;
                                                $TotalGrossweight += $row['GrossWt'];

                                                /*teja */

                                                $DefaultDescription = $row['FullName'];              // *
                                                $ProductDescription = $row['ProductDescription'];     // *
                                                $BuyerProdDescription = $row['BuyerProdDescription'];  // Optional
                                            
                                                /*teja */

                                                if ($row['Description'] != '') {
                                                    //$row['Variety']=$row['Description'];
                                                    $userDescription = $row['Description'];
                                                } else {
                                                    $userDescription = $row['Variety'];
                                                    $userDescription = '';
                                                }



                                                $masterDescription = $row['Variety'];

                                                if ($row['VarietyID'] == 0) {
                                                    $masterDescription = "Click To Change ";
                                                } else {
                                                    // $masterDescription=$row['BuyerProdDescription'] ? $row['BuyerProdDescription'] :  $row['ProductDescription']; 
                                                    $masterDescription = '<span class="spanBuyerProdDescription">' . $row['BuyerProdDescription'] . "</span>" . '<br> <span style="color: #30b400">(' . $row['DeviSUPC'] . " - " . $row['ProductDescription'] . ")</span>";
                                                }


                                                $HasSauce = $RowTotal['HasSauce'];

                                                //   echo  $masterDescription; 
                                                ?>
                                                <tr data-rowid="<?php echo $PoGradeId; ?>"
                                                    id="rowid<?php echo $PoGradeId; ?>">
                                                    <td id="PoGradeId<?php echo $PoGradeId; ?>">
                                                        <?php echo $PoGradeId; ?>
                                                    </td>
                                                    <td>
                                                        <p class="fullname Variety" id="Variety<?php echo $PoGradeId; ?>">
                                                            <?php echo $masterDescription; ?>
                                                        </p>
                                                        <input type="hidden" id="VarietyId<?php echo $PoGradeId; ?>"
                                                            name="VarietyId<?php echo $PoGradeId; ?>"
                                                            class="form-control hidden hdnVarietyID"
                                                            value="<?php echo $row['VarietyID']; ?>" />

                                                    </td>
                                                    <td style="display:none;">
                                                        <input type="text" class="form-control"
                                                            name="TotalSlabs<?php echo $PoGradeId; ?>"
                                                            id="TotalSlabs<?php echo $PoGradeId; ?>"
                                                            value="<?php echo $row['TotalSlabs']; ?>" />
                                                    </td>
                                                    <!-- <td id="tdGrade<?php // echo $PoGradeId;     
                                                        ?>"><?php // echo $row['Grade'];
                                                            ?></td>	 -->
                                                    <!-- <td id="tdPackingStyle<?php // echo $PoGradeId;     
                                                        ?>"><?php // echo $Packing;
                                                            ?></td>	 -->
                                                    <!-- <td><?php // echo $row['Treatment'];     
                                                        ?> </td>
                                                    <td><?php //echo $row['FreezingType'];     
                                                        ?> </td>	 -->
                                                    <td>

                                                        <textarea name="Description<?php echo $PoGradeId; ?>"
                                                            class="form-control" rows="2" cols="30"
                                                            placeholder="Enter Description(Optional)"
                                                            id="Description<?php echo $PoGradeId; ?>" disabled><?php echo $userDescription //$row['Description']     
                                                                       ?></textarea>
                                                        <p>
                                                            <?php
                                                            // $infoData =  $row['DeviSupc']." - ".$row['Grade'] . " - ".  $Packing . " - " . $row['Treatment'] ." - " . $row['Brand'] ;  
                                                            $infoData = $row['DeviSUPC'] . " - " . $row['Grade'] . " - " . $Packing . " - " . $row['Brand']; //."<br>(". $row['ProductDescription'].")";      
                                                            ?>
                                                            <!-- <span id="tdGrade<?php echo $PoGradeId ?>"><?php echo $row['Grade'] ?></span> - 
                                                            <span id="tdPackingStyle<?php echo $PoGradeId ?>"><?php echo $Packing ?></span> - 
                                                            <span id="tdBrand<?php echo $PoGradeId ?>"><?php echo $row['Brand'] ?></span>	-												 -->

                                                            <?php // $VarietyDescription=$DeviSUPC.'-'.$Grade.$PackingStyle.$ProductDescription .$FreezingTypee.$GlazePercc. '%' .$Glazee.$Treatmentt.$BrandName;     
                                                                ?>
                                                            <span id="tdInfo<?php echo $PoGradeId ?>">
                                                                <?php echo $infoData ?>
                                                            </span>
                                                        </p>
                                                    </td>
                                                    <td style="width: 80px;">
                                                        <input name="code" type="text" class="form-control"
                                                            id="code<?php echo $PoGradeId; ?>" disabled
                                                            value="<?php echo $row['BuyerItemCode'] ?>" />
                                                    </td>
                                                    <td style="width: 80px;" id="tdGrossWeight<?php echo $PoGradeId; ?>">
                                                        <!-- readonly -->
                                                        <input type="text" readonly class="form-control txtGrossWt"
                                                            id="txtGrossWt<?php echo $PoGradeId; ?>"
                                                            value="<?php echo $row['GrossWt']; ?>" class="" />
                                                    </td>
                                                    <td style="width: 80px;">
                                                        <?php // echo $row['Mcs'];
                                                            ?>
                                                        <input required type="number" max="999999" min="1"
                                                            class="form-control mcs" value="<?php echo $row['Mcs'] ?>"
                                                            name="MCS<?php echo $PoGradeId; ?>"
                                                            id="MCS<?php echo $PoGradeId; ?>"
                                                            placeholder="Enter MASTER CARTONS" disabled>
                                                    </td>
                                                    <?php if ($ArrayClientConfigData['HasSauce'] == 1) { ?>
                                                        <td style="width: 100px;">
                                                            <input type="number" class="form-control TrayQty"
                                                                value="<?php echo $row['TraysQuantity'] ?>"
                                                                name="TraysQuantity<?php echo $PoGradeId; ?>"
                                                                id="TraysQuantity<?php echo $PoGradeId; ?>" readonly>
                                                        </td>
                                                    <?php } ?>
                                                    <td style="width: 90px;">
                                                        <?php // echo $row['Price'];     
                                                            ?>
                                                        <input required type="number" <?php echo $UnitPriceLimit ?>
                                                            class="form-control price" name="Price<?php echo $PoGradeId; ?>"
                                                            id="Price<?php echo $PoGradeId; ?>"
                                                            value="<?php echo $row['Price'] ?>"
                                                            placeholder="Enter Rate/Unit(<?php echo $UnitCurrency ?>) "
                                                            disabled />
                                                        <p>

                                                            <?php
                                                            $infoData = 'Tariff added 10% if any increase or decrease at the time to entry must be to buyer ';

                                                            ?>
                                                            <span class="price-description danger">

                                                            </span>
                                                        </p>
                                                    </td>
                                                    <td style="width: 100px;">
                                                        <input type="text" class="form-control"
                                                            name='DdpPrice<?php echo $PoGradeId; ?>'
                                                            id="DdpPrice<?php echo $PoGradeId; ?>"
                                                            placeholder="Enter Ddp Price "
                                                            value="<?php echo $row['DdpPrice'] ?>">
                                                    </td>
                                                    <?php if ($ArrayClientConfigData['HasSauce'] == 1) { ?>
                                                        <td style="width: 100px;">
                                                            <?php
                                                            ?>
                                                            <input type="number" max="999999" min="1" class="form-control CUPS"
                                                                value="<?php echo $row['CUPS'] ?>"
                                                                name="CUPS<?php echo $PoGradeId; ?>"
                                                                id="CUPS<?php echo $PoGradeId; ?>" placeholder="Enter CUPS"
                                                                <?php echo ($PoReceived == '0' || ($HasSauce == 'No' || $HasSauce == '')) ? 'readonly' : 'required'; ?>>
                                                        </td>
                                                        <td style="width: 100px;">
                                                            <?php
                                                            ?>
                                                            <input type="number" <?php echo $UnitPriceLimit ?>
                                                                class="form-control CupsPrice"
                                                                value="<?php echo $row['CupsPrice'] ?>"
                                                                name="CupsPrice<?php echo $PoGradeId; ?>"
                                                                id="CupsPrice<?php echo $PoGradeId; ?>"
                                                                placeholder="Enter CUPS Price" <?php echo ($PoReceived == '0' || ($HasSauce == 'No' || $HasSauce == '')) ? 'readonly' : 'required'; ?>>
                                                        </td>
                                                    <?php } ?>
                                                    <td>
                                                        <?php echo number_format($Price, 2); ?>
                                                    </td>
                                                    <td>
                                                        <!-- <a href="javascript:void(0)" class="mr-3 text-primary aEditPoBreakUp" data-toggle="tooltip" data-placement="top"
                                                            title="" data-original-title="Edit" ><i class="mdi mdi-pencil font-size-18"></i>
                                                        </a> -->
                                                        <!-- <a  href="CsPoGradesEdit.php?id=<?php echo $row['ID']; ?>&PoID=<?php echo $INVOICE_ID; ?>" 
                                                                        class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
                                                                        title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a> -->
                                                        <button class="btn btn-link text-danger Delete"
                                                            data-id="<?php echo $row['ID']; ?>" data-toggle="tooltip"
                                                            data-placement="top" title="" value="" disabled
                                                            data-original-title="Delete" style="padding:2px;"><i
                                                                class="mdi mdi-trash-can font-size-18"></i></button>
                                                    </td>
                                                </tr>
                                                <?php

                                            }
                                            ?>
                                            <tr>
                                                <td colspan="4"
                                                    style="text-align: right;font-weight:600; font-size: 16px;">Total
                                                </td>
                                                <!-- <td style="font-weight:600; font-size: 16px;" id="tdTotalGrossweight">
                                                    <?php //echo round($TotalGrossweight,2) 
                                                    ?>
                                                </td>  -->

                                                <td style="font-weight:600; font-size: 16px;" id="tdTotalGrossweight">
                                                    <?php echo $TotalGrossweight ?>
                                                </td>
                                                <td style="font-weight:600; font-size: 16px;">
                                                    <?php echo $TotalMcs ?>
                                                </td>
                                                <td></td>
                                                <?php if ($ArrayClientConfigData['HasSauce'] == 1) { ?>
                                                    <td></td>
                                                    <td></td>
                                                <?php } ?>
                                                <td colspan="2" style="font-weight:600; font-size: 16px;">
                                                    <?php echo number_format($TotalAmount, 2) ?>
                                                </td>

                                            </tr>
                                        </tbody>

                                    </table>
                                    <input id="inputSelectedVariety" type="hidden" />

                                    <div class="col-xs-12 box-footer" align="right">
                                        <input type="hidden" id="editmode" value="T" />
                                        <!-- <button type="button" id="close"  name="close"  class="btn btn-danger btnClose">Close </button> -->
                                        <?php if (isset($_GET["backurl"]) && !empty($_GET["backurl"])) { ?>
                                            <a href="/v3/Sales/<?php echo $_GET["backurl"] . ".php?PIID=" . $_GET["id"]; ?>"
                                                class="btn btn-danger btnClose">Close</a>
                                        <?php } else { ?>
                                            <button type="button" id="close" name="close"
                                                class="btn btn-danger btnClose">Close </button>
                                        <?php } ?>

                                        <button type="button" id="edit" name="edit"
                                            class="btn btn-primary aEditPoBreakUp">Edit </button>
                                        <button type="submit" name='submit' id="btnSubmit" class="btn btn-success"
                                            disabled>Save</button>

                                        <input type="hidden" id="hdnAryPackingStyleIds" value="" />

                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>




                </div><!-- end col-->

            </div>
            <!-- end row-->







        </div> <!-- container-fluid -->
    </div>
    <!-- End Page-content -->
    </form>

    <?php include($IncludesPath . "footer.php"); ?>

    </div>
    <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->



    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "NewScripts.php"); ?>
    <?php include($IncludesPath . "ShowDataTable.php"); ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
        type="text/javascript"></script>


</body>
<script>
    $('#close').click(function (e) {
        if (document.referrer == "") {
            window.close();
        } else {
            window.history.back();
        }
        window.close();
    });

    $('#Cminus').on('click', function () {

        var NoCon = $("#NoContainers").val();

        if (NoCon > 1) {
            NoCon = parseInt(NoCon) - 1;
            $("#NoContainers").val(NoCon);
        }
    });

    $('#Cplus').on('click', function () {

        var NoCon = $("#NoContainers").val();
        if (NoCon < 10) {
            NoCon = parseInt(NoCon) + 1;
            $("#NoContainers").val(NoCon);
        }
    });
</script>

<!--uma -->
<script>
    $(document).on('click', '#AddGrossWtPerBrkUp', function (e) {
        e.preventDefault();

        var PoNo = $(this).data('value');
        var PoID = $(this).data('id');

        $('#Update').val(PoID);
        showGrossweightPoPopUp(PoNo, PoID);

    });

    function showGrossweightPoPopUp(PoNo, PoID) {

        $.ajax({
            url: 'Ajax/AjaxGrossWtPopUp.php',
            type: 'POST',
            data: {
                'PoID': PoID
            },
            success: function (data) {
                //  $("#Update").hide();

                $('.modal-title').text('PO Status Details - ' + PoNo);
                $("#PopUpTable").html(data);

                $('#myModal').modal('show');

                var table = $('#key-datatable').DataTable({
                    bSort: false,

                });


            }
        });

    }




    $(document).on('click', '#Update', function (e) {
        // updateGrossWeight();
        var InvoiceID = '<?php echo $POID ?>';

        var onSavePackIds = $("#hdnAryPackingStyleIds").val();

        var AryPackingStyleIds;

        if (onSavePackIds == "") {
            AryPackingStyleIds = <?php echo json_encode($AryPackingStyleIds); ?>;
        } else {
            AryPackingStyleIds = JSON.parse(onSavePackIds);
        }

        var ClickedId = $(this).data('id');

        var AryBreakUp = [];

        //for (i = 0; i < AryPackingStyleIds.length; i++) {
        for (i = 0; i < AryPackingStyleIds.length; i++) {
            ClickedId = AryPackingStyleIds[i];

            var GrossWeight = $(document).find('#GrossWeight' + ClickedId + "").val();
            var Backurl = '<?php echo $backurl ?>'; //uma
            var jsonData = {};
            jsonData['PckId'] = ClickedId;
            jsonData['GrossWeight'] = GrossWeight;

            AryBreakUp.push(jsonData);
        }

        if (GrossWeight == '' || GrossWeight == '0' || GrossWeight == 0) {
            alert('Please Enter Gross wt');
            return;
        }


        // return false;

        $.ajax({
            type: 'POST',
            dataType: "json",
            url: 'Ajax/AjCsUpdates.php',
            data: {
                'InvoiceID': InvoiceID,
                'Type': 'GrosswtUpdate',
                'Backurl': Backurl,
                'AryBreakUp': JSON.stringify(AryBreakUp)
            },
            success: function (data) {

                var obj = JSON.parse(JSON.stringify(data));
                if (obj.error == null) {
                    $('#TotalGrossWt').val(obj.TotalGrossWt);
                    alert(obj.Msg);
                    $('#myModal').modal('hide');
                    location.reload();
                } else {
                    alert(obj.error);
                }
            }
        });
    });

    // function updateGrossWeight() {
    // }
</script>
<!--uma -->
<script>
    $("document").ready(function () {
        /*$("#ConsigneeID").select2();
        $("#BuyerID").select2();
        $("#DestinationCountry").select2();
        $("#PortOfDischarge").select2();*/

        var hasOtherTax = <?php echo $HasPIOtherTax ?>;
        if (hasOtherTax == 1) {

            $("#OtherCharges").change(function () {
                loadExporterTaxRates();
            });
            $("#OtherCharges").trigger('change');

        }


        $("#file").change(function () {
            alert('changed!');
        });

        $('#ProductionPlant').select2();

        var processorID = $("#ProcessorID");
        plantAutofill(processorID);

        $("#ProcessorID").on("change", function () {
            var proceID = $(this);
            plantAutofill(proceID);
        });

        $(this).hide();
        $("input, select, textarea, button, .Delete, #AddNewItem").removeAttr('disabled');
        $("#editmode").val("T");
        $("#SelectedInvId").select2();
        var arr = [];

        $('#ConsigneeID').on("keyup", function () {
            /* Get input value on change */
            var inputVal = $(this).val();
            var resultDropdown = $(this).siblings(".result");
            if (inputVal.length) {
                $.get("AjaxConsigneeSearch.php", {
                    term: inputVal
                }).done(function (data) {
                    // Display the returned data in browser
                    resultDropdown.html(data);
                });
            } else {
                resultDropdown.empty();
            }

        });

        // Set search input value on click of result item
        $(document).on("click", ".result p", function () {
            var iid = $(this).attr('id');
            $("#ConsigneeID").val(iid);
            $(this).parent(".result").empty();
            $.ajax({
                type: 'POST',
                url: 'AjaxConsigneeDetails.php',
                data: {
                    'id': iid
                },
                success: function (data) {
                    var obj = JSON.parse(data);


                    var C_NAME = obj.ConsigneeName;
                    var C_ADD_id = obj.ConsigneeAddress;
                    var C_PORT_OF_DIS = obj.ConsigneePort;
                    var BUYER_COUNTRY = obj.ConsigneeCountry;
                    var BRAND_NAME = obj.ConsigneeBrand;
                    var SPECIAL_INST = obj.ConsigneeSplIns;
                    var FISHING_PMT_NO = obj.ConsigneeFishingNo;
                    alert(C_NAME);


                    $('#ConsigneeId').val(iid);
                    $('#ConsigneeAddress').val(C_NAME);
                    $('#ConsigneeBrand').val(BRAND_NAME);
                    $('#ConsigneeCountry').val(BUYER_COUNTRY);
                    $('#ConsigneePort').val(C_PORT_OF_DIS);
                    $('#ConsigneeAddress').val(C_ADD_id);
                    $('#ConsigneeSplIns').val(SPECIAL_INST);
                    $('#ConsigneeFishingNo').val(FISHING_PMT_NO);

                }
            });
            $(this).parent(".result").empty();

        });



        $('#ConsigneeID').change(function () {
            var BuyerId = $('#ConsigneeID').val();
            if (BuyerId == '') {
                return;
            }
            $.ajax({
                type: 'POST',
                url: 'Ajax/AjBuyerDtls.php',
                dataType: 'json',
                data: {
                    'BUYERID': BuyerId
                },
                success: function (response) {
                    var myJSON = JSON.parse(JSON.stringify(response));
                    $("#DestinationCountry").val(myJSON['ConsigneeCountry']).change();

                    /* $("#BRAND").val(myJSON['ConsigneeBrand']);
                     
                     $("#PORT_OF_DIS").val(myJSON['ConsigneePort']);
                     $("#ADDRESS_OF_CONSIGNEE").val(myJSON['ConsigneeAddress']);
                     $("#FISHING_PMT_NO").val(myJSON['ConsigneeFishingNo']);*/

                }
            });
        });


        $('#DestinationCountry').change(function () {
            var CountryId = $('#DestinationCountry').val();
            if (CountryId == '') {
                return;
            }
            $.ajax({
                type: 'POST',
                url: 'Ajax/AjPortDtls.php',
                //dataType: 'json',
                data: {
                    'CountryId': CountryId
                },
                success: function (response) {
                    //var myJSON = JSON.parse(JSON.stringify(response));
                    $("#PortOfDischarge").html(response);
                    // $("#PortOfDischarge").html(response);


                }
            });



        });
        $('#FinalDestinationCountry').change(function () {
            var finalDestinationCountryId = $('#FinalDestinationCountry').val();
            if (finalDestinationCountryId == '') {
                finalDestinationCountryId = $('#DestinationCountry').val();
            }

            $('#FinalDestination').val(null).trigger('change');

            if (finalDestinationCountryId == '') {
                return;
            }

            $.ajax({
                type: 'POST',
                url: 'Ajax/AjPortFinalDest.php',
                //dataType: 'json',
                data: {
                    'CountryId': finalDestinationCountryId
                },
                success: function (response) {
                    //var myJSON = JSON.parse(JSON.stringify(response));
                    // $("#PortOfDischarge").html(response);
                    $("#FinalDestination").html(response);


                }
            });
        });

        $('#ExporterID').change(function () {

            getBankDetails();
            loadExporterTaxRates();

        });

        function getBankDetails() {
            var ExpID = $('#ExporterID').val();
            $.ajax({
                type: 'POST',
                url: 'Ajax/AjExpBankDetails.php',
                //dataType: 'json',
                data: {
                    'ExpID': ExpID
                },
                success: function (response) {
                    $("#BankID").html(response);
                }
            });

        }

        function plantAutofill(processorID) {
            // $("#ProductionPlant").val("").trigger("change");

            var selectedOption = $(processorID).find('option:selected');
            var invShortCode = selectedOption.attr('data-invcode');

            if (invShortCode) {
                $('#ProductionPlant option').each(function () {
                    if ($(this).attr('data-prodCode') == invShortCode) {
                        $(this).prop('selected', true);
                    }
                });
                $('#ProductionPlant').trigger('change');
            }
        }


        function loadExporterTaxRates() {
            var exporterID = $('#ExporterID').val();
            if (exporterID) {
                $.ajax({
                    url: 'Ajax/AjaxExporterTaxRates.php',
                    type: 'POST',
                    data: {
                        'ExporterID': exporterID
                    },
                    success: function (data) {
                        let exporterTaxRates = JSON.parse(data);
                        if (exporterTaxRates.Msg == "Success") {
                            if ($('#OtherCharges').val() == 1 && exporterTaxRates?.ExporterTaxRates?.OtherTax > 0) {
                                $('.price-description').text('Price +' + exporterTaxRates.ExporterTaxRates?.OtherTax + '% tax')
                            } else {
                                $('.price-description').text('')
                            }
                        } else if (exporterTaxRates.Error) {
                            alert(exporterTaxRates.Error);
                        }

                    }
                });
            }

        }
    });
</script>


<script>
    $(document).on('click', '.Variety', function (e) {
        e.preventDefault();
        var editmode = $("#editmode").val();
        if (editmode == "T") {
            var PoID = $(this).data('id');
            var selectedVarietyRow = $(this).closest("tr").attr("data-rowid");
            $("#inputSelectedVariety").val(selectedVarietyRow);
            //$('#Update').val(PoID);	
            showPoPopUp(PoID);
        }

    });

    function showPoPopUp(PoID) {

        $.ajax({
            url: 'Ajax/AjaxPopUpVarietyDetails.php',
            type: 'POST',
            data: {
                'PoID': PoID
            },
            success: function (data) {
                // $("#Update").hide();


                $('.modal-title').text('Variety List');
                $("#PopUpTable").html(data);
                $('#myModal').modal('show');
            }
        });

    }

    $(document).on('click', '.SelectVariety', function (e) {
        e.preventDefault();

        var VarietyID = $(this).data('id');
        // var rowID = $(this).closest("tr").attr("data-rowid");

        $('#myModal').modal('hide');

        getVarietyData(VarietyID);



    });

    //.inputVariety
    $(document).on('click', '.Delete', function (e) {
        var D_ID = $(this).data('id');
        var r = confirm("Are you sure to delete this Grade?");
        if (r == true) {
            $.ajax({
                url: 'Ajax/AjaxDelete.php',
                type: 'POST',
                data: {
                    'TYPE': 'BREAKUP_DELETE',
                    'D_ID': D_ID,
                    'INVOICE_ID': '<?php echo $POID; ?>'
                },
                success: function (data) {
                    $("#rowid" + D_ID).remove();
                    location.reload();
                }
            });
        } else {

        }
    });
</script>

<script>
    function getVarietyData(VarietyID) {
        var PoId = '<?php echo $INVOICE_ID; ?>';
        var Variety = VarietyID;
        //   document.getElementById('displayinformation').hidden = false;
        $.ajax({
            type: 'POST',
            url: 'Ajax/AjaxAutoFillOptions.php',
            dataType: 'json',
            data: {
                'Type': 'VARIETY',
                'PoId': PoId,
                'Variety': Variety
            },
            success: function (response) {
                var selectedRow = $("#inputSelectedVariety").val();
                var myJSON = JSON.parse(JSON.stringify(response));
                var Species = myJSON['Species'];
                var ProductFormm = myJSON['FullName'];
                var FreezingTypee = myJSON['FreezingType'];
                var PreparationTypee = myJSON['PreparationType'];
                var GlazePercc = myJSON['GlazePerc'];
                var Glazee = myJSON['Glaze'];
                var Treatmentt = myJSON['Treatment'];

                var Grade = myJSON['Grade'];
                var PackingStyle = myJSON['PackingStyle'];
                var BrandName = myJSON['BrandName'];

                var FullName = myJSON['FullName'];
                var Description = myJSON['Description'];
                var BuyerDescription = myJSON['BuyerDescription'];
                var VarietyDescription = myJSON['VarietyDescription'];
                var VarietyId = myJSON['Variety'];
                var HasSauce = myJSON['HasSauce'];

                var PackingStyleID = myJSON['PackingStyleID'];
                var TotalSlabs = myJSON['TotalSlabs'];
                var SlabWeight = myJSON['SlabWeight'];

                var TraysQuantity = myJSON['TraysQuantity'];

                if (HasSauce === 'Yes') {
                    $("#CUPS" + selectedRow).val('').prop("readonly", false);
                    $("#CupsPrice" + selectedRow).val('').prop("readonly", false);
                } else {
                    $("#CUPS" + selectedRow).val('').prop("readonly", true);
                    $("#CupsPrice" + selectedRow).val('').prop("readonly", true);
                }



                $("#BdDescription").val(BuyerDescription);
                //$("#Description").val(BuyerDescription);
                $("#VarietyId").val(VarietyId);
                $("#Variety").val(VarietyDescription);
                // $("#displayinformation").val(Description);
                $("#FullName").val(FullName);
                //$("input:text").val(Data);




                // $("#Variety" + selectedRow).text(myJSON['FullName']);
                $("#VarietyId" + selectedRow).val(myJSON['Variety']);
                $("#Description" + selectedRow).val("");
                $("#tdGrade" + selectedRow).text(Grade);
                $("#tdPackingStyle" + selectedRow).text(PackingStyle);
                $("#tdBrand" + selectedRow).text(BrandName);

                $("#TotalSlabs" + selectedRow).val(myJSON['TotalSlabs']);

                $("#tdInfo" + selectedRow).text(VarietyDescription);

                var varity = '<span class="spanBuyerProdDescription">' + myJSON['BuyerDescription'] + '</span><br> <span style="color: #30b400">' + myJSON['ProductDescription'] + '</span>';
                $("#Variety" + selectedRow).html(varity);

            }
        });

    }

    function checkEmpty(Value) {

        if (Value == '') {
            Value = '';
        } else {
            Value = '(' + Value + ')';
        }
        return Value;
    }
</script>
<script>
    var AryBreakUp = [];
    var jsonData = {};
    $("#frmPoEdit").submit(function (e) {
        e.preventDefault();
    });


    $(document).on("click", ".aEditPoBreakUp", function () {
        $(this).hide();
        $("input, select, textarea, button, .Delete, #AddNewItem").removeAttr('disabled');
        $("#editmode").val("T");
    })

    $(document).on("click", "#btnSubmit", function () {

        jsonData = {};
        AryBreakUp = [];
        var AryListBreakUp = [];

        var AryPoGradeId = <?php echo json_encode($aryPoGradeId); ?>;

        var err = "F";

        for (i = 0; i < AryPoGradeId.length; i++) {
            rowId = AryPoGradeId[i];
            var jsonAryList = {};

            if (!Number($(document).find("#MCS" + rowId).val())) {
                // if (parseInt($("#MCS" + rowId).val()) <= 0 ) {
                alert('Mcs Value Should More than 0 ');
                // document.getElementById("#MCS" + rowId).focus();
                $(document).find("#MCS" + rowId).focus();
                return;
            }

            jsonAryList['PoGradeId'] = $("#PoGradeId" + rowId).text().replace(/\D/g, '');
            jsonAryList['Variety'] = $.trim($("#Variety" + rowId).text());

            jsonAryList['VarietyId'] = $("#VarietyId" + rowId).val();
            jsonAryList['Description'] = $("#Description" + rowId).val();

            var rowSpanProdDesc = $.trim($("#Variety" + rowId).find(".spanBuyerProdDescription").text());
            if (rowSpanProdDesc === "" && $("#Description" + rowId).val() === "") {
                $("#Description" + rowId).addClass("error");
                // return false;
                err = "T";
            } else {
                $("#Description" + rowId).removeClass("error");
            }

            jsonAryList['code'] = $("#code" + rowId).val();
            jsonAryList['MCS'] = $("#MCS" + rowId).val();
            jsonAryList['Price'] = $("#Price" + rowId).val();
            jsonAryList['GrossWt'] = $("#txtGrossWt" + rowId).val();
            jsonAryList['CUPS'] = $("#CUPS" + rowId).val();
            jsonAryList['CupsPrice'] = $("#CupsPrice" + rowId).val();
            jsonAryList['TraysQuantity'] = $("#TraysQuantity" + rowId).val();
            jsonAryList['DdpPrice'] = $("#DdpPrice" + rowId).val();
            AryListBreakUp.push(jsonAryList);

        }

        // var allZero = AryListBreakUp.every(obj => obj.GrossWt === "");
        // var allPositive = AryListBreakUp.every(obj => parseFloat(obj.GrossWt) > 0);
        // var anyNegative = AryListBreakUp.some(obj => parseFloat(obj.GrossWt) < 0);

        // // Check if all elements are "0"
        // $.each(AryListBreakUp, function(index, obj) {
        //     if (obj.GrossWt !== "0") {
        //         allZero = true;           
        //     }
        // });

        // // Check if all elements are greater than 0
        // $.each(AryListBreakUp, function(index, obj) {
        //     if (parseFloat(obj.GrossWt) <= 0) {
        //         allPositive = true;
        //     }
        // });

        // // Check if any element is less than 0
        // $.each(AryListBreakUp, function(index, obj) {
        //     if (parseFloat(obj.GrossWt) < 0) {
        //         anyNegative = false;
        //     }
        // });

        // $.each(AryListBreakUp, function(index, obj) {
        //     if (obj.GrossWt == "" || parseFloat(obj.GrossWt) <= 0) {
        //         anyEmptyAndZero = true;
        //     }
        // });


        // var errGrossWeight = "F";
        // if (allZero) {
        //     console.log("Valid (all GrossWt are 0)");
        //     errGrossWeight = "F";
        // } else if (allPositive) {
        //     console.log("Valid (all GrossWt are greater than 0)");
        //     errGrossWeight = "F";        
        // } else if (anyNegative) {
        //     alert("Invalid : All GrossWt must be either 0 or greater then 0 value");
        //     errGrossWeight = "T";
        // }  
        // // else if (anyEmptyAndZero) {
        // //     alert("Invalid : GrossWt should not be empty and 0");
        // //     errGrossWeight = "T";
        // // } 
        // else {
        //     // alert("InValid : All Gross Weight-LBS must be either 0 or greater then 0 value"); // All non-negative case
        //     // errGrossWeight = "T";
        // }

        // var isGWeightVaid = "T";

        // $.each(AryListBreakUp, function(index, obj) {
        //     var gWt = parseFloat(obj.GrossWt);
        //     if (gWt <= 0 ) {
        //         isGWeightVaid = "F";
        //         return false;
        //     }
        //     if (obj.GrossWt == "") {
        //         isGWeightVaid = "F";
        //         return false;
        //     }        
        // });

        // return false;


        var prodPlant = $('#ProductionPlant').val();
        prodPlant = prodPlant.join(",");

        jsonData['ExporterID'] = $("#ExporterID").val();
        jsonData['ProcessorID'] = $("#ProcessorID").val();
        jsonData['ReferenceNo'] = $("#ReferenceNo").val();
        jsonData['InvNo'] = $("#InvNo").val();
        jsonData['InvDate'] = $("#InvDate").val();
        jsonData['PoNo'] = $("#PoNo").val();
        jsonData['PoDate'] = $("#PoDate").val();
        jsonData['ConsigneeID'] = $("#ConsigneeID").val();
        jsonData['UltimateConsigneeID'] = $("#UltimateConsigneeID").val();
        jsonData['BuyerID'] = $("#BuyerID").val();
        jsonData['NotifyPartyID'] = $("#NotifyPartyID").val();
        jsonData['ImporterID'] = $("#ImporterID").val();
        jsonData['Agent'] = $("#Agent").val();
        jsonData['Certification'] = $("#Certification").val();
        jsonData['PortofLoading'] = $("#PortofLoading").val();
        jsonData['PlaceofReceipt'] = $("#PlaceofReceipt").val();
        jsonData['DestinationCountry'] = $("#DestinationCountry").val();
        jsonData['FinalDestinationCountry'] = $("#FinalDestinationCountry").val();

        jsonData['PortOfDischarge'] = $("#PortOfDischarge").val();
        jsonData['FinalDestination'] = $("#FinalDestination").val();
        jsonData['ShipByDate'] = $("#ShipByDate").val();
        jsonData['ShipByDateTextformat'] = $("#ShipByDateTextformat").val();
        jsonData['BankID'] = $("#BankID").val();
        jsonData['ShowBank'] = $("#ShowBank").val();
        jsonData['ByPayment'] = $("#ByPayment").val();
        jsonData['ShippingTerms'] = $("#ShippingTerms").val();
        jsonData['DeliveryTerms'] = $("#DeliveryTerms").val();
        jsonData['QtyUnit'] = $("#QtyUnit").val();
        jsonData['UnitCurrency'] = $("#UnitCurrency").val();
        jsonData['Freight'] = $("#Freight").val();
        jsonData['ChargeName'] = $("#ChargeName").val();
        jsonData['ChargeAmount'] = $("#ChargeAmount").val();
        jsonData['ProductionPlant'] = prodPlant;
        jsonData['Remarks'] = $("#Remarks").val();
        jsonData['OtherCharges'] = $("#OtherCharges").val();
        jsonData['PoID'] = '<?php echo $POID ?>';
        jsonData['DdpPrice'] = $("#DdpPrice").val();

        jsonData['PreparedBy'] = "<?php echo $PreparedBy; ?>";

        //TypeCountry
        //YEAR
        //PRInstrctns

        // AryBreakUp.push(jsonData);

        jsonData['Breakup'] = AryListBreakUp;

        AryBreakUp = AryBreakUp.concat(AryListBreakUp);
        jQuery.validator.addMethod("noSpace", function (value, element) {
            return value == '' || value.trim().length != 0;
        }, "No space please and don't leave it empty");

        jQuery.validator.addMethod('selectcheck', function (value) {
            return (value != '0' || value != '');
        }, "year required");

        // jQuery.validate.addMethod('twoDigits', function(value) { // Durga 
        //     return /^\d{2}$/.test(value);
        // }, "Enter only two digits");

        // if(err == "T" || errGrossWeight == "T"){
        if (err == "T") { //|| isGWeightVaid == "F"

            if (err == "T") {
                $('html, body').scrollTop($("textarea.error").offset().top - 50);
            }
            // if(isGWeightVaid == "F") {
            //     alert("InValid : Gross Weight-LBS must be greater then 0 value");
            // }

            // $('.txtGrossWt').each(function(index) {
            //     var inputValue = parseFloat($(this).val());

            //     if(inputValue <= 0){
            //         $(this).addClass('error');
            //     } else {
            //         $(this).removeClass('error');
            //     }
            // });

            return false;
        } else {
            // $('.txtGrossWt').each(function(index) {
            //     $(this).removeClass('error');
            // });
        }
    });

    $(document).ready(function () {
        /* Popup Registration */

        $("#frmPoEdit").validate({
            onkeyup: false,
            onfocusout: false,
            invalidHandler: function (form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var firstInvalidElement = $(validator.errorList[0].element);
                    //$('html,body').scrollTop(firstInvalidElement.offset().top - 100);
                    firstInvalidElement.focus();
                }
            },
            ignore: "",

            rules: {

                ExporterID: {
                    selectcheck: true
                },
                ProcessorID: {
                    selectcheck: true
                },
                InvNo: {
                    required: true
                },
                InvDate: {
                    required: true
                },
                ConsigneeID: {
                    selectcheck: true
                },
                DestinationCountry: {
                    selectcheck: true
                },
                PortOfDischarge: {
                    selectcheck: true
                },
                FinalDestination: {
                    selectcheck: true
                },
                BankID: {
                    selectcheck: true
                },
                ShowBank: {
                    selectcheck: true
                },
                ByPayment: {
                    selectcheck: true
                },
                ShippingTerms: {
                    selectcheck: true
                },
                DeliveryTerms: {
                    selectcheck: true
                },
                QtyUnit: {
                    selectcheck: true
                },
                UnitCurrency: {
                    selectcheck: true
                },
                // ChargeAmount: { 
                //     twoDigits: true
                // }

            },
            errorPlacement: function () {
                return false; // suppresses error message text
            },
            submitHandler: function () {
                var InvId = '<?php echo $POID ?>';
                var Backurl = '<?php echo $backurl ?>';
                $.ajax({
                    url: 'Ajax/AjaxPoEdit.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'InvId': InvId,
                        'Backurl': Backurl,
                        'Data': JSON.stringify(jsonData)
                    },
                    success: function (data) {
                        var obj = JSON.parse(JSON.stringify(data));
                        if (obj.Error == null) {
                            alert("Updated Successfully");

                            if (data.AryPackingStyleIds) {
                                var AryPackIds = JSON.stringify(data.AryPackingStyleIds);
                                $("#hdnAryPackingStyleIds").val(AryPackIds);
                            } else {
                                $("#hdnAryPackingStyleIds").val("");
                            }

                            // var TotalGrosswt = $.trim($("#tdTotalGrossweight").text());
                            var TotalGrosswt = $.trim(obj.GrossWt);
                            if (confirm('Total Grossweight is ' + TotalGrosswt + ', Are you sure you want to update grossweight?')) {
                                // Save it!
                                $('#AddGrossWtPerBrkUp').trigger("click");
                                return false;
                            } else {
                                if (obj.CallBackUrl != null && obj.CallBackUrl != '') {
                                    window.location.replace(obj.CallBackUrl);
                                } else {
                                    window.history.back();
                                }
                            }
                        } else {
                            alert(obj.Error);
                        }

                    }
                });
                // $.ajax({
                //     type: "POST",
                //     url: "registerlogin.php",
                //     data: data_array,
                //     beforeSend: function () {
                //         // $('.ajaxloader').show();
                //         // $('body').css("overflow-y", "hidden");
                //     },
                //     complete: function () {
                //         // $('.ajaxloader').hide();
                //         // $('body').css("overflow-y", "visible");
                //     },
                //     success: function (data1) {
                //         //alert(data);
                //         if (data1 == "error") {

                //         } else if (data1 == "success") {

                //         }
                //     },
                //     error: function (xhr, status, error) {
                //         var err = eval("(" + xhr.responseText + ")");
                //         //alert(err.Message);
                //     }
                // });


            }



        });
        /* ***************** */

        function validateFloatKeyPress(el, evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            var number = el.value.split('.');

            if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            }

            // Just one dot
            if (number.length > 1 && charCode == 46) {
                return false;
            }

            // Get the carat position
            var caratPos = getSelectionStart(el);
            var dotPos = el.value.indexOf(".");

            if (caratPos > dotPos && dotPos > -1 && (number[1].length > 2)) {
                return false;
            }

            return true;
        }

        // Thanks: http://javascript.nwbox.com/cursor_position/
        function getSelectionStart(o) {
            if (o.createTextRange) {
                var r = document.selection.createRange().duplicate();
                r.moveEnd('character', o.value.length);
                if (r.text == '') return o.value.length;
                return o.value.lastIndexOf(r.text);
            } else {
                return o.selectionStart;
            }
        }

        $(document).on('keypress', '.grossWeight', function (evt) {
            if (!validateFloatKeyPress(this, evt)) {
                evt.preventDefault();
            }
        });
        $(document).on('paste', '.grossWeight', function (evt) {
            evt.preventDefault();
        });
    })
</script>
<script>
    function AddNewItem() {
        var userPreference;

        if (confirm("Do you want to Add New Item?") == true) {
            var PoID = '<?php echo $POID ?>';
            $.ajax({
                url: 'Ajax/AjaxAddNewGrade.php',
                type: 'POST',
                datatype: 'json',
                data: {
                    'PoID': PoID
                },
                success: function (data) {
                    if (Response.Error == null) {
                        alert("Gross Weight is updated to new default values");
                        $(document).find("#key-datatable tbody tr:last").before(data);
                        location.reload();
                    } else {
                        alert(Response.Error);
                    }

                }
            });
        } else {
            userPreference = "Canceled!";
        }
    }

    $(".AddNewItem").on("click", function () {

        AddNewItem();
    })

    $('input.mcs').keyup(function () {
        /*
        check for 3 or greater than 3 characters.
        If you check for only greater than 3, then it will let
        you write the fourth character because just before writing,
        it is not greater than three.
        */
        if ($(this).val().length >= 7) {
            $(this).val($(this).val().substring(0, 6));
            $("#btnSubmit").removeAttr('disabled');
            $(this).removeClass("error");
            // $("#btnSubmit").prop("disabled", false);
            return false;
        }
        var mcs = parseFloat($(this).val()) || 0;
        var idSuffix = $(this).attr('id').replace('MCS', '');
        var targetId = '#TraysQuantity' + idSuffix;
        var totalSlabs = parseFloat($('#TotalSlabs' + idSuffix).val()) || 0;
        var trayQty = mcs * totalSlabs;
        $(targetId).val(trayQty);
        // else{
        // 	$(this).addClass("error");
        // 	$("#btnSubmit").attr('disabled');
        // 	// $("#btnSubmit").prop("disabled", true);

        // }
    });

    var $regexname = /^\d{0,3}(?:\.\d{0,7})?$/;
    $('.price').on('keyup', function () {
        if (!$(this).val().match($regexname)) {
            // there is a mismatch, hence show the error message
            $(this).addClass("error");
            $("#btnSubmit").prop("disabled", true);
        } else {
            $(this).removeClass("error");
            $("#btnSubmit").prop("disabled", false);

        }
    });

    $(document).on("click", "#btnModalClose, #myModal .close", function () {
        location.reload();
    })
</script>
<style>
    input:disabled,
    textarea:disabled {
        background: #e7e7e7 !important;
    }

    .fullname {
        color: #2c74e8;
    }

    .fullname:hover {
        text-decoration: underline;
        cursor: pointer;
    }

    select.error+.select2-container,
    input.error {
        border: 1px solid red;
    }
</style>

</html>
