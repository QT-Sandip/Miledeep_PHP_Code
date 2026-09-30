<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

include($cp . "Includes/IncludePage.php");

// include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
$Month = $_POST['Month'];
$Month = sprintf("%02d", $Month);
$YEAR = $_POST['Year'];
$SType = $_POST['Type'];
$UNIT = $_POST['PsId'];
$date_start = $YEAR . '-' . $Month . '-01';
$date_end = $YEAR . '-' . $Month . '-31  23:59:59';

$accessType = $_SESSION['AccessType'];

$sql = "SELECT * from RmPurchaseRatesHistory where RmPriceDate 
	BETWEEN '$date_start' AND '$date_end' and SType='$SType' order by ID DESC";

$stmt = $iconn->query($sql);
// $errorInfo = $stmt->errorInfo();
// var_dump($errorInfo);

$content = '';

$content .= ' <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >											
				<thead class="thead-light">
                  <th align="center" >RM Price Date</th> 
                  <th align="center" >Species</th> 
				  <th align="center" >Tools</th>
                </thead>
                <tbody>';

$PURCHASE_AMOUNT = 0;

while ($row = $stmt->fetch_array()) {

	$SType = $row['SType'];
	if ($SType == 'VM') {
		$Url = 'AddPurchaseRates.php?id=';
	} else {
		$Url = 'AddBTPurchaseRates.php?id=';
	}

	$RM_PRICE_DATE = getFormatedDate($row['RmPriceDate']);

	$content .= '<tr>  
           		
               <td><font size="' . $text . '">Rates From -  ' . $RM_PRICE_DATE . '</font></td>
               <td><font size="' . $text . '">' . $SType . '</font></td>
                <td style="width:40%;"> 
				 <a href="add-purchase-rates-m1.php?id=' . $row['ID'] . '&Stype=' . $row['SType'] . '" 
					class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top"
					title="Edit FinanciersList" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
              		<a  target="_blank"  href="RM_RatesPrnt.php?id=' . $row['ID'] . '" class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
					title="View RatesList" data-original-title="View"><i class="dripicons-preview font-size-18"></i>
					</a>
					<button class="btn btn-text" id="copy-btn" data-toggle="tooltip" data-placement="top"
					title="Copy Rate" data-original-title="Copy" data-date-id=' . $row['ID'] . ' data-stype=' . $row['SType'] . '><i class="mdi mdi-content-copy font-size-18"></i></button>';
	if ($accessType == '1') {
		$content .=	'<button class="btn text-danger" id="delete-btn" data-toggle="tooltip" data-placement="top"
										title="Delete Rate" data-original-title="Delete" data-date-id=' . $row['ID'] . ' data-stype=' . $row['SType'] . '><i class="mdi mdi-trash-can font-size-18"></i></button>';
	}
	$content .=	'
				</td>
		</tr>';
}
$content .= '</tbody></table>';

echo $content;


// $content .= '<tr>  

//                <td align="center" ><font size="' . $text . '">Rates From -  ' . $RM_PRICE_DATE . '</font></td>
//                 <td  align="center" style="width:40%;"> 
// 				 <a target="_blank" href="' . $Url . urlEncode64($row['ID']) . '" 
// 						 class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top"
// 						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
//               <a  target="_blank"  href="RM_RatesPrnt.php?id=' . urlEncode64($row['ID']) . '" class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
// 						 title="" data-original-title="View"><i class="dripicons-preview font-size-18"></i>
// 						 </a>
// 				<!--<a  target="_blank" 
// 				href="AddPurchaseRates.php?id=' . urlEncode64($row['ID']) . '"
// 				class="btn btn-danger">Edit</a>
// 				<a  target="_blank" href="RM_RatesPrnt.php?id=' . urlEncode64($row['ID']) . '"class="btn btn-success">Print</a>
// 				--></td>
// 		</tr>';
