<?php


$PoID=$_GET['PoID'];
session_start();


include "../includes/config.php";

include "Constants.php";
include "PrintHeaderInc.php";



$sql_exporter="SELECT * FROM $TblAdminExporterDetails ORDER BY ExpID DESC LIMIT 1";
$stmt_exporter=$iconn->query($sql_exporter);
$row_processor=$stmt_exporter->fetch_array();



 $sql_code_list="SELECT * FROM `$TblQcCodeList` as cl left JOIN $TblQcPoVarityList as pvl on pvl.VarietyID=cl.VarietyID 
 LEFT JOIN AdminPackingStyles as ps on ps.PCKID=cl.PackingStyleID
WHERE cl.InvID='$PoID' ORDER BY `DayCode` ASC";
$stmt_code_list=$iconn->query($sql_code_list);

$code_list_row=$stmt_code_list->fetch_array();
$stmt_code_list=$iconn->query($sql_code_list);




/*$sql_varity_list="SELECT * FROM `PO_VARIETY_LIST` WHERE INV_ID='$ID'";
$stmt_varity_list=$iconn->query($sql_varity_list);*/

$T_DAY_CODE='';         
$T_PACKING='';
$T_MCS='';
while($code_list_row=$stmt_code_list->fetch_array()){
       
	   $DAY_CODE=$code_list_row['DayCode'];
	   $PACKING=$code_list_row['PackingStyle'];
	   $GLAZE_PERC=$code_list_row['GlazePerc'];
	   $MCS=$code_list_row['McsTotal'];
	   
	   $T_MCS=$T_MCS+$MCS;
       
	   
	  
	  
	  if(!in_array($DAY_CODE,$ary_code,true)){ 
		$ary_code[]=$DAY_CODE;
	  
	  IF($T_DAY_CODE==''){
		  $T_DAY_CODE=$DAY_CODE;
	  }
	  else{
		  $T_DAY_CODE=$T_DAY_CODE.','.$DAY_CODE;
	  }  
	  }else{
	  //echo $DAY_CODE;
	  }
	  
	  
	  //print_r($ary_code);
	  
	  if(in_array($PACKING,$ary_PACKING)){                               		 
	  }
	  else{
		  $ary_PACKING[]=$PACKING;
	  
	  IF($T_PACKING==''){
		  if($GLAZE_PERC==0){
			  $T_PACKING=$PACKING; 
		  }else{
			  $T_PACKING=$PACKING; 
		  }
		 
	  }
	  else{
		  if($GLAZE_PERC==0){
			 $T_PACKING=$T_PACKING.','.$PACKING;
		  }else{
			  $T_PACKING=$T_PACKING.','.$PACKING;
		  }
		  
	  }
	  
	  }
	
}

//print_r($ary_code);

 $T_MCS= $T_MCS;

/*$sql="SELECT * FROM `INVOICE_DATA`  WHERE `ID`='$ID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();*/

function getHeader($PoID,$iconn){

$contents = '';
global $PoID;
global $TblScrmPoData;
global $TblScrmConsigneeDetails;
global $TblQcCoveringLetter;
	 	
 $sql="SELECT * FROM `$TblScrmPoData` as pd LEFT JOIN $TblScrmConsigneeDetails as scd on scd.ConID=pd.ConsigneeID
left JOIN $TblQcCoveringLetter as cvl   on  cvl.InvID=pd.PoID
 WHERE `PoID`='$PoID'"; 

$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();
$CONSIGNMENT_EXPORTED=$row['ConsigneeCountry'];

 $PO_TME_STAMP=strtotime($row['PoDate']);
 $PO_DATE=date("d/m/Y", $PO_TME_STAMP);

$INV_TME_STAMP=strtotime($row['InvDate']);
$INV_DATE=date("d/m/Y", $INV_TME_STAMP);

$contents .='<p>Sub: Request for issuance of Health Certificate for export of Fishery Products to </font>
<font size="20"><B><u>'.$row['ConsigneeCountry'].'</u></B></font> <font size="11"><br><br>Respected Sir/Madam, <BR/><BR/>



Please issue a Health Certificate for the consignment P.O .NO:' .$row['PoNo'] .'  DT:'.$PO_DATE.', against<br>
Invoice no :' .$row['InvNo'] .' DT:'.$INV_DATE.' the  particulars of  which are given below meant for export to<br> EU. The certification fees may be debited  from eic on line payment at EIA-Chennai,<br>
S.O: Bhimavaram,  Further  we hereby declare that , we have  packed aquaculture products and we
have not used any antibiotics while processing the consignment in our plant. Pink copy of the CFE
and other particulars of the consignment are enclosed.</font></font size="15"> CFE :</font><font size="11"> # ' .$row['QCertNo'] .' 



</p>';
return $contents;
}



		
	

	$from_title = date('d M, Y');
	$to_title = date('d M, Y');

	


	
    
	//require_once('Constants.php');
	
    



 $pdf->SetTitle('Covering Note: '.$row['PoNo'].' - '.$row['PoDate']);  
	
    $content = '';  
   
    $content .= getHeader($ID,$iconn);  
    
   
   

	
$logo = $COMPANY_LOGO;
$toolcopy = '<img src="http://live-jme.mydeal.live/images/jme_logo.jpg"  width="80" height="80">';
//$pdf->writeHTML($toolcopy, true, 0, true, 0);


//$pdf->Cell(180 ,35,'',0,1,'L');
$header=11;
$sub_header=10;


$PO_TIME_STAMP = strtotime($row['Date']);
$DO_SAMPLING = date("d/m/Y", $PO_TIME_STAMP);

$pdf->SetFont($FONT_ARI,'',$header);

$pdf->Cell(0 ,5,'DT:'.$DO_SAMPLING,0,1,'R');


$pdf->Cell(180 ,5,'To,',0,1,'L');
$pdf->Cell(150 ,5,'The  officer incharge,,',0,1,'L');
$pdf->Cell(49 ,5,'Export Inspection Agency-Chennai,',0,1,'L');
$pdf->Cell(150 ,5,'Sub office-Bhimavaram',0,1,'L');

//$pdf->Ln();


$pdf->SetFont($FONT_ARI,'B',15);
//$pdf->Cell(0 ,5,'Respected Sir/Madam',0,1,'L');

$pdf->SetFont($FONT_ARI,'BU',20);
//$pdf->Cell(0 ,5,'NOn -EU(HEALTH)',0,1,'C');
$pdf->SetFont($FONT_ARI,'',$header);


$pdf->writeHTML($content);   
//$pdf->Ln();	
//$pdf->Cell($W1 ,$H1,'',0,1,'L');

$W1=100;
$W2=90;
$H1=5;
$pdf->SetFont($FONT_ARI,'',$sub_header);

//$pdf->Cell($W1 ,$H1,'I.Details identifying the fishery products:',0,1,'L');

//$pdf->Ln();
$pdf->SetFont($FONT_ARI,'B',$sub_header);
/*while($varity_list_row=$stmt_varity_list->fetch_array()){
	$ary_varity_list[]=$varity_list_row['FULL_NAME'];
//$pdf->Cell(15 ,$H1,'',0,0,'L');
//$pdf->Cell($W2 ,$H1,$varity_list_row['FULL_NAME'],0,1,'L');
}*/

$pdf->SetFont($FONT_ARI,'',$sub_header);
//$pdf->Ln();

$COUNT=1;





$count_v=count($ary_varity_list);

if($count_v>5){
	$c_pd=3;
}else{
	$c_pd=6;
}





$FARMER_NAME .= '<table border="0" cellspacing="0" padding-left: 10px; cellpadding="3">
<tr>
           		<th  width="35%" align="left">1.1Consignor Name and Address</th>
                <th width="65%" align="left">: M/S '.$EXPORTOR.',<BR>'.$EXPORTOR_ADD.'</th>				
           </tr>

			
		   <tr>  
           		<th width="35%" align="left">1.5Consignee Name and Address</th>
                <th width="65%" align="left">: '.$row['ConsigneeName'].'<BR>'.$row['ConsigneeAddress'].' </th>				
           </tr>
		   
			
			<tr>  
           		<th width="35%" align="left">1.7 Country of Origin</th>
                <th width="65%" align="left">: INDIA </th>				
           </tr>
		   
		   

		   
		   <tr> 
           		<th width="35%" align="left">ISO Code</th>
                <th width="65%" align="left">: IN </th>				
           </tr>
			
			<tr> 
           		<th width="35%" align="left">1.8 Region of Origin</th>
                <th width="65%" align="left">: ANDHRA PRADESH </th>				
           </tr>
		   
		   <tr>  
           		<th width="35%" align="left">Code</th>
                <th width="65%" align="left">: </th>				
           </tr>
		   
		   <tr> 
           		<th width="35%" align="left"> 1.9 Country of destination</th>
                <th width="65%" align="left">: '.$row['ConsigneeCountry'].'</th>				
           </tr>
		   
		   <tr> 
           		<th width="35%" align="left">ISO Code</th>
                <th width="65%" align="left">:
</th>				
           </tr>
		   
		   <tr>  
				
           		<th width="35%" align="left">1.11 Place of Origin:                                                                                                                   </th>
                <th width="65%" align="left">:   </th>				
           </tr>
		    <tr>  
				
           		<th width="35%" align="left">Name</th>
                <th width="65%" align="left">: M/S. '. $row_processor['ExpName'].'</th>				
           </tr>
		   <tr>  
				
           		<th width="35%" align="left">Address</th>
                <th width="65%" align="left">: '.$row_processor['ExpAddress'].' </th>				
           </tr>
		   <tr>  
				
           		<th width="35%" align="left">Approval No    </th>
                <th width="65%" align="left">: '.$PROCESSOR_APP_NUM.'  </th>				
           </tr>
		   <tr>  
				
           		<th width="35%" align="left">1.13 Place of loading  </th>
                <th width="65%" align="left">:'.$row['ConsigneeCountry'].'  </th>				
           </tr>
		   <tr>  
				
           		<th width="35%" align="left">1.14 Date of departure </th>
                <th width="65%" align="left">:  </th>				
           </tr>
		   <tr>  
				
           		<th width="35%" align="left">1.15Means of Transport </th>
                <th width="65%" align="left">:BY SEA  </th>				
           </tr>
		   <tr>  
				
           		<th width="35%" align="left">Identification</th>
                <th width="65%" align="left">:  </th>				
           </tr>
		   <tr>  
				
           		<th width="35%" align="left">Documentary reference</th>
                <th width="65%" align="left">:  </th>				
           </tr>
		   <tr>  
				
           		<th width="35%" align="left">1.16Entry BIP in EU </th>
                <th width="65%" align="left">:  </th>				
           </tr>
		   
		   </table>  
		';


$pdf->writeHTML($FARMER_NAME);  

//$pdf->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', 'http://www.tcpdf.org', '', false, 150, '', false, false, 0, false, false, false);
		
 /*
 $toolcopy = '<img src="http://live-jme.mydeal.live/images/jme_footer.jpg"  width="600px" height="80">';
$footer = "images/jme_footer.jpg";
//$pdf->Cell(12,5, $pdf->Image($footer, 10,265, 190,0),0,0,'C',0);

//$pdf->writeHTML($toolcopy, true, 0, true, 0);
*/


/* next page*/

$pdf->AddPage();



$W2=85;
$W3=100;
$H1=5;


$pdf->Cell(180 ,35,'',0,1,'L');

$consign_exp = explode(',', $row['ConsigneeCountry']);

 $CONSIGNMENT_EXPORTED_TO=$consign_exp[0].', CONTAINER NO :'.$consign_exp[1].', '.'VESSEL NAME :'.$consign_exp[2].', SEAL NO :'.$consign_exp[3];




$FARMER_NAME = '<table border="0" cellspacing="0" cellpadding="3">

			<tr>  
           		<th width="35%" align="left">1.18 Description of commodity</th>
				
				
                <th width="65%" align="left">: '.$code_list_row['FullName'].'</th>				
         </tr>
		   
		   
		   <tr>  
           		<th width="35%" align="left">1.19 Commodity Code (HS Code</th>
				
				
				
             <th width="65%" align="left">: </th>
				
	   
           </tr>			<tr>  
           		<th width="35%" align="left">1.20 Quantity</th>
                <th width="65%" align="left">:</th>				
           </tr>			<tr>  
           		<th width="35%" align="left">1.21 Temperature of product</th>
                <th width="65%" align="left">: -18˚C OR BELOW</th>				
           </tr>			<tr>  
           		<th width="35%" align="left">1.22 Number of packages</th>
                <th width="65%" align="left">:'.$T_MCS.'MC('.$T_PACKING.')</th>				
           </tr>			<tr> 
           		<th width="35%" align="left">1.23 Identification of container /Seal Number</th>
                <th width="65%" align="left">:</th>				
           </tr>			<tr>  
           		<th width="35%" align="left">1.24 Type of packages</th>
                <th width="65%" align="left">:</th>				
           </tr>			<tr>  
           		<th width="35%" align="left">1.25 Commodities certified for</th>
                <th width="65%" align="left">: HUMAN CONSUMPTION</th>				
           </tr>			<tr>  
           		<th width="35%" align="left">1.27 For import or admission to EU</th>
                <th width="65%" align="left">: YES</th>				
           </tr>			<tr>  
           		<th width="35%" align="left">1.28 Identification of the Commodities</th>
                <th width="65%" align="left">:</th>				
           </tr>			<tr>  
           		<th width="35%" align="left">Species(Scientific Name)</th>
                <th width="65%" align="left">:'.$row['SCIENTIFIC_NAME'].'</th>				
           </tr>			<tr>  
           		<th width="35%" align="left">Nature of commodities</th>
                <th width="65%" align="left">:</th>				
           </tr>			<tr>  
           		<th width="35%" align="left">Treatment type</th>
                <th width="65%" align="left">:</th>				
           </tr>			<tr>  
           		<th width="35%" align="left">Approval No</th>
                <th width="65%" align="left">: '.$PROCESSOR_APP_NUM.'</th>				
           </tr>			<tr>  
           		<th width="35%" align="left">Number Packages</th>
                <th width="65%" align="left">:'.$T_MCS.'C/S</th>				
           </tr>			<tr>  
           		<th width="35%" align="left">Net Weight</th>
                <th width="65%" align="left">:</th>				
           </tr>
		   
		   
		   </table>  
		';


$pdf->writeHTML($FARMER_NAME);



//$contents='<p><b>The product is Fit for human consumption.</b></p>';
$pdf->writeHTML($contents);

$pdf->Cell(0 ,5,'',0,1,'L');
$contents='<p> The undersigned hereby declare that the consignment has not been shipped and that the Fishery Products
described above have been handled , prepared , processed , identified , stored and transported in our approved 
facility under conditions at least equivalent to those laid down inRegulation (EC)/853/2004 laying down the
health conditions   for the production  and the placing on the market of fishery products




</p>';
$pdf->writeHTML($contents);
$PO_TIME_STAMP = strtotime($row['DO_SAMPLING']);
$DO_SAMPLING = date("d-m-Y", $PO_TIME_STAMP);

/*$pdf->Cell(0 ,5,'',0,1,'L');
$contents='<p>Place: <b>Bhimavaram</b><br>Date:<b>'.$DO_SAMPLING.'</b><br><br>yours faithfully<br>
For M/s.<b>'.$PROCESSOR.'</b><br></p>';*/
//$pdf->writeHTML($contents);



$pdf->Cell(0 ,15,'',0,1,'L');
$pdf->Cell(0 ,5,'Place: BHIMAVARAM',0,1,'L');
$pdf->Cell(0 ,5,'Date:'.$DO_SAMPLING,0,1,'L');
$pdf->Cell(0 ,5,'Yours faithfully ,',0,1,'R');
$pdf->Cell(0 ,15,'',0,1,'L');

$pdf->Cell(0 ,5,'Name :'.$row['VARIFIED_ID'],0,1,'R');
$pdf->Cell(0 ,5,'Designation : QC TECHNOLOGIST',0,1,'R');



$pdf->Ln();
//$pdf->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', 'http://www.tcpdf.org', '', false, 150, '', false, false, 0, false, false, false);

 
 
 
 
ob_clean();

$pdf->Output('payroll.pdf', 'I');

?>