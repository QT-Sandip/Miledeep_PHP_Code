
<?php
session_start();

include $_SERVER["DOCUMENT_ROOT"] . "/includes/config.php";
include $_SERVER["DOCUMENT_ROOT"] . "/includes/Globals.php";
include $_SERVER["DOCUMENT_ROOT"] . "/includes/Util.php";
$Header = "ORDER`S LIST REPORT";

$Year = $_GET["Year"];
$Exporter = $_GET["Exporter"];

$DateFilter = getFinancialYear($Year);

$Query = "";
if ($Exporter != "") {
    $Query .= "and ExporterID in ($Exporter)";
}

$file_name = "Orders List -" . $from_date . "-" . $to_date . ".xlsx";
$date_end = $to_date . " 23:59:59";

$sql = "SELECT GROUP_CONCAT(PoID) as PoID  FROM ScrmPoData as SPD 
					left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
					left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id 
					left join ScrmShipmentStatusCodes as SSC on SPD.Status=SSC.Code
					left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID
					WHERE AvailableDate!='' and (SDB.TransportedDate ='' or SDB.TransportedDate is null) 
					$Query order by PoID desc";

$stmt = $iconn->query($sql);
$RowData = $stmt->fetch_array();
$PoID = $RowData["PoID"];
$AryPoID = explode(",", $PoID);
$OrderCount = Count($AryPoID);

$SqlGroup = "SELECT Category,ShortName,GROUP_CONCAT(VarietyID) as VarietyID 
		FROM `ScrmPIVarietyList` GROUP BY Category order by Category";
$StmtGroup = $iconn->query($SqlGroup);

while ($RowData = $StmtGroup->fetch_array()) {
    $AryData = getTableDate($RowData["VarietyID"], $PoID);

    if ($AryData["RowCount"] > 0) {
        //echo '<h3>'.$RowData['Category']. '-'. $RowData['ShortName']  .'</h3>';

        echo $AryData["TableData"];
    }
}

function getFinalMcs($PoGradeId)
{
    global $iconn;
    $SqlGradeData = "SELECT COALESCE(sum(AvalMcs),0) as AvalMcs,
COALESCE(sum(AvalMcs*TotalSlabs*SlabWeight*MFactor),0) as FinishedKgs
FROM `CsStockData` as CSD LEFT JOIN AdminPackingStyles as APS
 on APS.PCKID=CSD.PackingStyleID WHERE PoGradeId='$PoGradeId'";
    $StmtGradeData = $iconn->query($SqlGradeData);
    $RowGradeData = $StmtGradeData->fetch_array();
    return $RowGradeData;
}

function getTableDate($VarietyID, $PoID)
{
    global $iconn, $Query;
    $SqlPos = "SELECT *,SSC.Name as PoStatus ,SPC.Name as Port FROM ScrmPoData as POD 
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id 
left join ScrmPortCities as SPC on POD.PortOfDischarge	=SPC.PortId 
left join ScrmShipmentStatusCodes as SSC on POD.Status=SSC.Code
left join ScrmShipmentDetailsBreif as SDB on POD.PoID=SDB.PiID
WHERE  AvailableDate!='' and (TransportedDate='' or TransportedDate is null) 
 $processorQry
 order by PoID desc";

    $StmtPos = $iconn->query($SqlPos);

    $data = '<table  id="key-datatableFullscreen" class="table table-bordered table dt-responsive wrap w-100"  data-paging="false" 
					style="background:#fff;height:80px">										
				<thead class="thead-light ">
					<th  style="min-width: 40px;">S.No</th>      
					<th style="min-width: 180px;">Buyer</th>	
					<th>Po No</th>
					<!--<th>Internal LotNo</th>
					<th>Port</th>-->
					<th>Brand</th>
					<th style="min-width: 130px;">Shipment Date</th>
					<th>Variety</th>
					<th>Packing</th>
					<th>Grade</th>
					<th style="min-width: 80px;">Ordered Mcs</th>
					<th style="min-width: 80px;">Final Mcs</th>		
					<th style="min-width: 80px;">Bal Mcs</th>		
					<th style="min-width: 80px;">Ordered Kgs</th>
					<th style="min-width: 80px;">Final Kgs</th>	
				<!-- <th>Dummy Kgs</th>	-->	
					<th>Bal Kgs</th>
					<!--<th>Aval In Store Kgs</th>-->
				</thead>
                <tbody>';

    //$SNO=1;

    while ($RowPos = $StmtPos->fetch_array()) {
        ++$SNO;
        $InvId = $RowPos["PoID"];
        $PoNum = $RowPos["PoNo"];
        $LotNo = $RowPos["LotNo"];
        $Port = $RowPos["ConsigneePort"];
        $Brand = $RowPos["Brand"];
        $ConsigneeName = $RowPos["ConsigneeName"];
        $ShipmentDate = getFormatedDate($RowPos["ShipByDate"]);

        $SqlPoBreakUp = "SELECT *,Sum(MCS*TotalSlabs*SlabWeight*MFactor) as OrderKgs FROM `ScrmPoGradeData`
 as SPGD 
left JOIN ScrmPIVarietyList as SPVL on SPGD.VarietyID=SPVL.VarietyID
LEFT join AdminPackingStyles as APS On APS.PCKID=SPGD.PackingStyleID
WHERE InvId='$InvId' group by SPGD.ID";

        //echo '<br>';

        $StmtPoBreakUp = $iconn->query($SqlPoBreakUp);
        $IsGradeAval = $StmtPoBreakUp->num_rows;

        if ($IsGradeAval > 0) {
        } else {
            continue;
        }

        $PoTotalOrderdMcs = $PoTotalFinishedMcs = $PoTotalDummyMcs = $PoTotalReqMcs = $PoTotalOrderKgs = $PoTotalFinishedKgs = $PoTotalDummyKgs = $PoTotalBalKgs = 0;

        while ($RowPoBreakUp = $StmtPoBreakUp->fetch_array()) {
            $Variety = $RowPoBreakUp["ShortName"];
            $OrderedMcs = $RowPoBreakUp["Mcs"];
            $PackingStyle = $RowPoBreakUp["PackingStyle"];
            $Grade = $RowPoBreakUp["Grade"];
            $GradeWiseBrand = $RowPoBreakUp["Brand"];
            $OrderKgs = $RowPoBreakUp["OrderKgs"];
            $FinishedMcs = 0;
            $FinishedMcs = getFinalMcs($RowPoBreakUp["ID"])["AvalMcs"];
            $FinishedKgs = getFinalMcs($RowPoBreakUp["ID"])["FinishedKgs"];
            $BalKgs = $OrderKgs - $FinishedKgs;
            $ReqMcs = $OrderedMcs - $FinishedMcs;

            /*$OrderedMcs=$RowPoBreakUp['RequiredCases'];
				 
				  $DummyMcs=$RowPoBreakUp['DummyMcs'];
				  $ReqMcs=$OrderedMcs-$FinishedMcs;
				  
				    $StoreAvalKgs=$RowPoBreakUp['StoreAvalKgs']+$RowPoBreakUp['StoreAvalLKgs'];
				   
				    $FinishedKgs=$RowPoBreakUp['FinishedKgs'];
				    $DummyKgs=$RowPoBreakUp['DummyKgs'];
				    $BalKgs=$OrderKgs-($FinishedKgs+$DummyKgs);
					*/

            if ($FinishedMcs > $OrderedMcs) {
                $Style = "color:#0bb50b";
            } else {
                $Style = "color:black";
            }

            $StyleTotal =
                "font-weight:bold;font-size:11px;color:red;background: #d2d6de;";

            $PoTotalOrderdMcs += $OrderedMcs;
            $PoTotalFinishedMcs += $FinishedMcs;
            $PoTotalReqMcs += $ReqMcs;

            $PoTotalOrderKgs += $OrderKgs;
            $PoTotalFinishedKgs += $FinishedKgs;
            $PoTotalDummyKgs += $DummyKgs;
            $PoTotalBalKgs += $BalKgs;

            $data .=
                '<tr style="' .
                $Style .
                '">
				<td >' .
                $SNO .
                '</td>
				<td >' .
                $ConsigneeName .
                '</td>
				<td >' .
                $PoNum .
                '</td>
			
				<td >' .
                $GradeWiseBrand .
                '</td>
				<td >' .
                $ShipmentDate .
                '</td>
				<td >' .
                $Variety .
                '</td>
				<td >' .
                $PackingStyle .
                '</td>
				<td >' .
                $Grade .
                '</td>
				<td>' .
                $OrderedMcs .
                '</td>
				<td>' .
                $FinishedMcs .
                '</td>
				<td>' .
                $ReqMcs .
                '</td>
				<td>' .
                round($OrderKgs, 2) .
                '</td>
				<td>' .
                round($FinishedKgs, 2) .
                '</td>
			
				<td>' .
                round($BalKgs, 2) .
                '</td>
				
				</tr>';

            $PoCount++;

            $data .=
                '<tr style="' .
                $StyleTotal .
                '">
				<td >' .
                $SNO .
                '</td>
				<td >Total</td>
				
				<td ></td>
				<td ></td>
				<td ></td>
				<td ></td>
				<td ></td>
				<td ></td>
				<td>' .
                $PoTotalOrderdMcs .
                '</td>
				<td>' .
                $PoTotalFinishedMcs .
                '</td>
				<td>' .
                $PoTotalReqMcs .
                '</td>
				<td>' .
                round($PoTotalOrderKgs, 2) .
                '</td>
				<td>' .
                round($PoTotalFinishedKgs, 2) .
                '</td>
		
				<td>.' .
                round($PoTotalBalKgs, 2) .
                '</td>
					
				</tr>';
        }
    }
    $data .= "</tbody></table>";
    $AryData["TableData"] = $data;
    $AryData["RowCount"] = $Rcount = 1;

    return $AryData;
}
?>

