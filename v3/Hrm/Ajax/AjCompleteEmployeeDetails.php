<?php

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
  $DeptID=$_GET['DeptID']; 
  $SubDeptID=$_GET['SubDeptID'];  
  $Status=$_GET['Status'];  


 $month_name= date("F", strtotime($from_date));
   $content1 .= '<h3 align="center">Employee List </h3>';
echo $content1;

/*require_once('../tcpdf/tcpdf.php');

class MYPDF extends TCPDF {

	public function Footer() {



		$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);

		$this->SetY(-55);

   }



    public function Header() {



        $image_file = K_PATH_IMAGES.'jme_logo.jpg';

        $this->Image($image_file, 10, 10, 35, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);

        $this->SetFont('helvetica', 'B', 15);

		$this->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');

		$this->Cell(0, 5, 'SURYAMITRA EXIM PVT.LTD', 0,1, 'C', 0, '', 0, false, 'T', 'M');



    }


}



    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);



	$pdf->SetCreator(PDF_CREATOR);

    $pdf->SetTitle('Value Addition Report : ');

    $pdf->SetHeaderData('', '', '', '');

    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));

    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    $pdf->SetDefaultMonospacedFont('helvetica');

    $pdf->SetFooterMargin(15);

	$pdf->SetHeaderMargin(15);

    $pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT,15);

    $pdf->setPrintHeader(false);

    $pdf->setPrintFooter(false);



    $pdf->SetAutoPageBreak(TRUE,15);

    $pdf->SetFont('helvetica', '', 11);

    $pdf->AddPage('l');




		$pdf->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');

		$pdf->Cell(0, 5, 'SURYAMITRA EXIM PVT.LTD', 0,1, 'C', 0, '', 0, false, 'T', 'M');

				    $pdf->SetFont('helvetica', 'B', '11');

		$pdf->Cell(0, 5, 'Value Addition Report - '. getFormatedDate($from_date), 0, 1, 'C', 0, 'B', 0, false, 'T', 'M');
					//$pdf->Cell('', '', $month_name, 0, 1, '');

		//$pdf->Cell(0, 5,''.$month_name, 0, 1, 'L', 0, '', 0, false, '', '');



    $pdf->SetFont('helvetica', '', 11);*/

    $table_data=getP_TABLE($iconn,$DeptID,$SubDeptID,$Status);     
    echo $table_data['TABLE'];
      


$file_name='Employee List.pdf';

//$pdf->Output($file_name, 'I');
			

function getP_TABLE($iconn,$DeptID,$SubDeptID,$Status){
global $DeptID,$SubDeptID;
	
if($SubDeptID!='' ){
	
$query=" HED.SubDeptID='$SubDeptID' ";
}elseIF($DeptID!=''){
	$query="HED.DeptID='$DeptID' ";
}

if($Status!=''){
$query.="AND HED.EmpStatus='$Status'";
}else{
 
}
	
		
$SqlEmployeeCompleteDetails="SELECT 
HED.EmpID, HED.BioMetricID, HED.DeptID, HED.SubDeptID, HED.EmpName,
HED.EmpCategory,  HED.DesigID, HED.ShiftID,  HED.JoiningDate, HED.EmpStatus,
HD.DeptID,HD.DepartmentName,
HSD.SubDeptID,HSD.SubDeptName,
HEPD.EmpID,HEPD.PhoneNumber,HEPD.PresentAddress,HEPD.PermanentAddress,
HEPD.DateOfBirth,HEPD.AadharNumber,HEPD.ESICNo,HEPD.PANNo,
HESD.Basic,HESD.HRA,
HESD.DailyAllowance,HESD.SpecialAllowance,HESD.MedicalAllowance,
HESD.Conveyence,HESD.Others,HESD.Incentives,
HEBAD.EmpID,HEBAD.AccNumber,HEBAD.BankID,HEBAD.IFSC

FROM HrmEmployeeDetails HED 
LEFT JOIN HrmDepartments HD ON HD.DeptID=HED.DeptID
LEFT JOIN HrmSubDepartments HSD ON HSD.SubDeptID=HED.SubDeptID
LEFT JOIN HrmEmpPersonalDetails HEPD ON HEPD.EmpID=HED.EmpID
LEFT JOIN HrmEmpSalaryDetails HESD ON HESD.EmpID=HED.EmpID
LEFT JOIN HrmEmpBankAccountDetails HEBAD ON HEBAD.EmpID=HED.EmpID

WHERE $query";
		


			  $StmtEmpCompltDtils=$iconn->query($SqlEmployeeCompleteDetails);
			  $r_lots = $StmtEmpCompltDtils->fetch_array();
			  $StmtEmpCompltDtils=$iconn->query($SqlEmployeeCompleteDetails);
				


//


	$content='';


	$size=40;

	$size1=60;




			  $S_NO=1;
			  $width=120/14;
			  $width_s=$width-5;
			  $width_w=$width+5;


			  $content .= '
	  <table border="1" cellspacing="0" cellpadding="3">
	  			  			  <h3 align="center"> Employee Details</h3>

			   <tr>



                <th  align="center"  width="'.$width_s.'%"><B>S No.</B></th>
				<th  align="center" width="'.$width.'%" ><B> Emp ID </B></th>
                <th  align="center"  width="'.$width.'%"><B>Emp Name</B></th>
                <th  align="center"  width="'.$width.'%" ><B>Dept </B></th> 
                <th  align="center" width="'.$width.'%"><B>Sub Dept </B></th>
                <th  align="center" width="'.$width.'%" ><B>Designation</B></th>
                <th  align="center" width="'.$width.'%" ><B>Phone No</B></th>
				<th  align="center" width="'.$width.'%" ><B>Present Address</B></th>
				<th  align="center" width="'.$width.'%" ><B>Permanent Address</B></th>
				<th  align="center" width="'.$width.'%" ><B>Aadhar No</B></th>
                <th  align="center" width="'.$width.'%" ><B>Date Of Birth</B></th>
                <th  align="center" width="'.$width.'%" ><B>PAN No</B></th>
                <th  align="center" width="'.$width.'%" ><B>Basic Salary</B></th>
				<th  align="center" width="'.$width.'%"><B>HRA </B></th>
				<th  align="center" width="'.$width.'%"><B>Account Number </B></th>
				<th  align="center" width="'.$width.'%"><B>Bank Name</B></th>



               </tr>

			  ';

	$TOTAL_SENT_WEIGHT=0;
		//
		while($r_lots = $StmtEmpCompltDtils->fetch_array()){

$IceCanCost=200;

$IceCans=$r_lots['IceConsumption'];
$SENT_WEIGHT=$r_lots['SENT_WEIGHT'];
$RECEIVED_WEIGHT=$r_lots['HL_QTY_RCVD'];
$WgtAfterVa=$r_lots['WgtAfterValueAddition'];
$RETURN_WEIGHT=$r_lots['RETURN_WEIGHT'];
$TOTAL_SENT_WEIGHT+=$SENT_WEIGHT;
$Variety=$r_lots['VARIETY'];
$CountObtained=$r_lots['COUNT'];
$ShedIDD=$r_lots['STATION_ID'];


$IceCost= ($IceCans*$IceCanCost);

$TotalIceCost+=$IceCost;



IF($Variety =='PD'){
	
  $VarietyID=1;
}ELSEIF($Variety=='PDTO'){
	
	
 $VarietyID=2;	
	
}ELSEIF($Variety=='PUD'){
	
	
 $VarietyID=3;	
	
}ELSEIF($Variety=='DeHeading'){
	
	
 $VarietyID=4;	
	
}


IF($ShedIDD == 9 || $ShedIDD == 12 ){
	
  $Location= 0;
}ELSE{
	
	
 $Location=1;	
	
}




        $SqlPeelingPrices="SELECT PricePerKg FROM `PpcPeelingPrices` WHERE 
       LocationID='$Location' AND VarietyID='$VarietyID' AND
	    LowerLimit < '$CountObtained' and UpperLimit >= '$CountObtained'";
       $StmtPeelingPrices=$iconn->query($SqlPeelingPrices);
		$RowPeelingPrices=$StmtPeelingPrices->fetch_array();
	
		$PeelingAmount=$RowPeelingPrices['PricePerKg'];
		
		$Payment=	($PeelingAmount*$r_lots['HL_QTY_RCVD']);
		$TotalPayment+=$Payment;

	   

	$yield=round(($WgtAfterVa/($SENT_WEIGHT-$RETURN_WEIGHT))*100,2);

	$yield1=$yield.'%';
	$yield2=trim(str_replace("()", "",$yield1));

	 $PURCHASE_DATE=date("d-m-Y", strtotime($r_lots['PURCHASE_DATE']));
	 $PROCESSING_DATE=date("d-m-Y", strtotime($r_lots['PROCESSING_DATE']));
	$r_lots['ANTI_BIOTIC_STATUS'];


		$COUNT=$r_lots['EXP_COUNT'];

		//$STANDARD_YIELD=getStandardYeild($iconn,$COUNT);

		if($yield1 < $STANDARD_YIELD ){
			$COLOR='red';
		}else{
			$COLOR='GREEN';
		}



		$RETURN_WEIGHT= isset($r_lots['RETURN_WEIGHT']) ? $r_lots['RETURN_WEIGHT'] : "-";
				    $content .=

					'<tr>
				 <th  align="center" width="'.$width_s.'%">'.$S_NO.'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['LOT_NO'].'</th>
				 <th  align="center" width="'.$width.'%">'.$PURCHASE_DATE.'</th>
				<!-- <th  align="center" width="'.$width.'%">'.$PROCESSING_DATE.'</th> -->
				 <th  align="center" width="'.$width.'%" >'.$r_lots['UNIT_NAME'].'</th>
         <th  align="center" width="'.$width.'%">'.$r_lots['COUNT'].'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['SENT_WEIGHT'].'</th>
				 <th  align="center" width="'.$width.'%" >'.$r_lots['VARIETY'].'</th>';
if($SENT_WEIGHT!=$RECEIVED_WEIGHT){

			$content .=	 '<th style="color:red" align="center" width="'.$width.'%">'.$r_lots['HL_QTY_RCVD'].'</th>';
}
else	{
				$content .=	 '<th  align="center" width="'.$width.'%">'.$r_lots['HL_QTY_RCVD'].'</th>';

}			
				$content .= '<th  align="center" width="'.$width.'%">'.$RETURN_WEIGHT .' </th>
         <th  align="center" width="'.$width.'%">'. $WgtAfterVa .' </th>
         <th  align="center" width="'.$width.'%">'. $IceCost .'/'.$IceCans.'</th>
         <th  align="center" width="'.$width.'%">'. $Payment .'</th>

				 <th style="color:'.$COLOR .'" align="center" width="'.$width.'%">'.$yield1.' </th>

				  <th  align="center" width="'.$width.'%">'.$r_lots['REMARKS'].'</th>

				 </tr>';

 $S_NO++;
}

      $content .=

    '<tr>

   <th  align="center" width="50"><B>Total </B></th>
   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>

    <th  align="center" width=""><B>'. $TOTAL_SENT_WEIGHT.'</B></th>
	 
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" >'.$TotalIceCost.'</th>
			   <th  align="center" >'.$TotalPayment.'</th>

   </tr>';

		$content .= '</table>';



		$Data['TOTAL_SENT_WEIGHT']=$TOTAL_SENT_WEIGHT;
		$Data['TABLE']=$content;
		
	return $Data;

}

?>
