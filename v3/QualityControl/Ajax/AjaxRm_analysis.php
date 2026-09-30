<?php

session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$TYPE = $_POST['TYPE'];
$YEAR = $_POST['YEAR'];
$MONTH = $_POST['MONTH'];

$DateStart = $YEAR . '-' . $MONTH . '-01';
$DateEnd = $YEAR . '-' . $MONTH . '-31  23:59:59';
$DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";



$uid = $_SESSION['r_id'];
//echo $_SESSION['DeptID']; exit;

if ($_SESSION['DeptID'] == 2) {
	$showOptions = true;
} else {
	//$showOptions=false;
	$showOptions = true;
}

$accessType = $_SESSION['ACCESS_TYPE'];

$SqlRmList = "select RmLotID,LotNumber,PurchaseDate,RLD.CreatedDateTime,PurchaseCount,PurchaseWeight,ROR.PostedUserID,RmPC.PurchaseType, 
	AbStatus,Name,Colour, (SELECT COUNT(*) from QcRmOrgnDetails as ORGD WHERE ORGD.LotID=RLD.RmLotID ) as OrgnDetailsPreview ,
	(SELECT Remarks from QcRmOrgnDetails as ORGD WHERE ORGD.LotID=RLD.RmLotID ORDER BY LotID DESC LIMIT 1 ) as Remarks 
	from RmLotDetatils as RLD 
	left join QcRmAbReport as QRAB on QRAB.LotID=RLD.RmLotID 
	left join QcABOptions as QAO on QAO.OptID=QRAB.AbStatus 
	left join QcRmOrgnReport as ROR on ROR.LotID=RLD.RmLotID 
	LEFT join RmArrivalDetatils as RAD on RAD.RmArrivalID=RLD.RmReferenceID
	LEFT JOIN RmPurchaseCategory as RmPC on RmPC.CatID=RAD.PurchaseCategoryID
	WHERE PurchaseDate $DateFilter ";

$stmt = $iconn->query($SqlRmList);

$data .= ' <table  id="key-datatable" data-paging="false" class="table table-hover table-bordered" >										
		
<thead class="thead-light">

      <tr>
        <th>ID</th>
        <th>Lot Number</th>
	    <th>Purchase Date</th>
        <th>Rm Date</th>
        <th>Purchase Type</th>
		<th>Count</th>
		<th>Weight(KGS)</th>
        <th >AB Status</th>
        <th >Status</th>
		<th >Reason</th>
	    <th>Actions</th>
      </tr>
    </thead>
    <tbody>';

while ($row = $stmt->fetch_array()) {
	//ROBO
	$RmLotID = $row['RmLotID'];
	$OrgnDetailsPreview = $row['OrgnDetailsPreview'];
	$OrgnSubDt = date('d-m-Y', strtotime($RowOrgnDetails['PostedDate']));
	if ($OrgnSubDt == "01-01-1970") {
		$OrgnSubDt = "00-00-0000";
	} else {
		$OrgnSubDt;
	}
	if ($row['OrgnDetailsPreview'] == 0) {
		$Status = 'Report Pending';
		$StatusColour = '#f00';
	} else if ($row['OrgnDetailsPreview'] >= 1) {

		if ($RowOrgnReportCount['RM_ANA_STATUS'] == 2) {
			$Status = 'Qc Not Aval';
			$StatusColour = '#f00';
		} else if ($row['PostedUserID'] == 0) {
			$Status = 'Verification Pending';
			$StatusColour = '#f00';
		} else {
			$Status = 'Verified';
			$StatusColour = 'green';
		}
	}

	if ($row['AbStatus'] != '') {
		$AbStatus = $row['Name'];
		$Colour = $row['Colour'];
	} else {
		$AbStatus = 'Test Pending';
		$Colour = 'gray';
	}

	$data .= '<td id="' . $row['RmLotID'] . '" > ' .  $row['RmLotID'] . ' </td>
        <td> <a  href="LotDetails.php?id=' . $row['LotNumber'] . '"></a>' . $row['LotNumber'] . '</td>
		<td id="a_type' . $row['RmLotID'] . '">' . date('d-m-Y', strtotime($row['PurchaseDate'])) . '</td>
    	<td id="a_type' . $row['RmLotID'] . '">' . date('d-m-Y H:i:s', strtotime($row['CreatedDateTime'])) . '</td>
		<td id="a_type' . $row['RmLotID'] . '">' . $row['PurchaseType'] . '</td>
		<td id="p_number' . $row['RmLotID'] . '">' . $row['PurchaseCount'] . '</td>
		<td id="address' . $row['RmLotID'] . '">' . $row['PurchaseWeight'] . '</td>
		<td id="address' . $row['RmLotID'] . '">		
		<div  class=" badge  font-size-10 "
		style="background:' . $Colour . ';color:white">' . $AbStatus . '</div></td>
		<td id="address' . $row['RmLotID'] . '">
		<div  class=" badge  font-size-10 "
		style="background:' . $StatusColour . ';color:white">' . $Status . '</div></td>
		<td id="address' . $row['RmLotID'] . '">' . $row['Remarks'] . '</td>
        <td style="width:10%;"> ';
	if ($OrgnDetailsPreview < 3) {
		if ($showOptions) {
			$data .= ' 
					<!--<a href="RmReportCreate.php?LOT_ID=' . $row['RmLotID'] . '"class="btn btn-primary">Add</a>-->
					
					<a target="_blank" href="RmReportCreate.php?LOT_ID=' . $row['RmLotID'] . '" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Add" data-original-title="Add"
						 ><i class="mdi mdi-plus font-size-16"></i></a>
					';
		} else {
			$data .= '<a href="#"class="btn btn-danger" target=_blank>Report Pending</a>';
		}
	} else {

		//if( $ORGN_INSP_REPORT['VERIFIED_BY']==0){
		if ($row['PostedUserID'] == 0) {
			if ($showOptions) {
				// $data.='<a href="RmaPreview.php?LOT_ID='. $row['RmLotID'].'"class="btn btn-primary" target=_blank>Preview '.
				$data .= '
			 
			  <a target="_blank" href="RmAnalysisPreview.php?LotId=' . $row['RmLotID'] . '" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Preview" data-original-title="Preview"
						 ><i class="mdi mdi-eye font-size-16"></i></a>';
			} else {
				$data .= '<a href="#"class="btn btn-danger" target=_blank>Approval Pending</a>';
			}
		} else {
			$data .= '
			 <a target="_blank" href="RmaPrint.php?LotID=' . $row['RmLotID'] . '" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Print" data-original-title="Print"
						 ><i class="mdi mdi-printer font-size-16"></i></a>
			 
			 ';
		}
	}








	$data .= '</td>


        </tr>';
}




echo $data .= '</tbody></table>';
