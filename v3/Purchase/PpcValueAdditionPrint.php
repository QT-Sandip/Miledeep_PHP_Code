<?php

session_start();

include "../includes/config.php";

include "../includes/Globals.php";

  //$date=$_GET['date'];
  $from_date=$_GET['from_date'];
  $to_date=$_GET['to_date'];

   $UNIT=$_GET['U']; 
  $TYPE=$_GET['TYPE'];  // melting =1 NEW =0


 //$month_name= date("F", strtotime($date));
 $month_name= date("F", strtotime($from_date));
//  $month_name= date("F", strtotime($from_date));

require_once('../tcpdf/tcpdf.php');

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

		$this->Cell(0, 5, 'JAGADEESH MARINE EXPORTS', 0,1, 'C', 0, '', 0, false, 'T', 'M');



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

		$pdf->Cell(0, 5, 'JAGADEESH MARINE EXPORTS', 0,1, 'C', 0, '', 0, false, 'T', 'M');

				    $pdf->SetFont('helvetica', 'B', '11');

		$pdf->Cell(0, 5, 'Value Addition Report - '. getFormatedDate($from_date), 0, 1, 'C', 0, 'B', 0, false, 'T', 'M');
					//$pdf->Cell('', '', $month_name, 0, 1, '');

		//$pdf->Cell(0, 5,''.$month_name, 0, 1, 'L', 0, '', 0, false, '', '');



    $pdf->SetFont('helvetica', '', 11);

    $UNIT_NAME='';
      if($UNIT ==0){
		
      $SqlProStations="SELECT * FROM `RM_PROCESSING_STATIONS`";
      $StmtProStations=$iconn->query($SqlProStations);

      	while($Row=$StmtProStations->fetch_array()){
          $UNIT=$Row['ID'];
          $UNIT_NAME=$Row['UNIT_NAME'];
          $table_data=getP_TABLE($iconn,$from_date,$to_date,$UNIT_NAME);
          
		  if($table_data['TOTAL_SENT_WEIGHT']!=0){
			$pdf->writeHTML($table_data['TABLE']);
			}
        }
      }else{
		  
		  
        $SqlProStations="SELECT * FROM `RM_PROCESSING_STATIONS` where ID='$UNIT' ";
		
        $StmtProStations=$iconn->query($SqlProStations);
$Row=$StmtProStations->fetch_array();
		
  $UNIT_NAME=$Row['UNIT_NAME'];      
       $table_data=getP_TABLE($iconn,$from_date,$to_date,$UNIT_NAME);
      $pdf->writeHTML($table_data['TABLE']);
      }


$file_name='Value Addition Report -'.$from_date.'-'.$to_date.'.pdf';

$pdf->Output($file_name, 'I');


//function getP_TABLE($iconn,$date){
function getP_TABLE($iconn,$from_date,$to_date,$UNIT_NAME){
global $UNIT,$TYPE,$AdminLoginDetails;
	$date_end=$to_date.' 23:59:59';


				/*<?php		$mon=$_POST['month']?>*/

if($UNIT!=0){
$query="and PS.ID='$UNIT'";
}else{
  $query="";
}
if($TYPE==1){
$query.="and imd.id='0'";
}else{
 // $query="";
}
	
		
	 $sql_lots="SELECT DISTINCT
	  imd.id,
	  imd.LOT_NO,
	  imd.ANTI_BIOTIC_STATUS,
	  imd.PURCHASE_DATE,
	  imd.WEIGHT_IN_KGS,
	  VA.COUNT,
	  VA.PROCESSING_DATE,
	  VA.SENT_WEIGHT,
    VA.HL_QTY_RCVD,
    VA.RETURN_WEIGHT,
	  VA.WgtAfterValueAddition,
	  VA.VARIETY ,
	  VA.SHIFT ,
	  VA.REMARKS ,
	  VA.PROCESSING_DATE ,
	  VA.USER_ID ,
	  PS.UNIT_NAME
	  from inputmaterial_details as imd
			 left join PPC_VALUE_ADDITION as VA on imd.id=VA.LOT_ID
			 left join RM_PROCESSING_STATIONS as PS on VA.STATION_ID =PS.ID
			  where  PROCESSING_DATE BETWEEN '$from_date' AND '$date_end'
			  and VA.VARIETY!='DeHeading' $query
			  order by PURCHASE_DATE ASC,LOT_NO ASC,VARIETY ASC";
		


			 $stmt_lots=$iconn->query($sql_lots);
			  $r_lots = $stmt_lots->fetch_array();
			  
			  $UserId=$r_lots['USER_ID']; 

 $SqlUserName="SELECT * FROM `$AdminLoginDetails`  WHERE UserID='$UserId'"; 
$StmtUserName=$iconn->query($SqlUserName);
$RowUserName=$StmtUserName->fetch_array();

			  $stmt_lots=$iconn->query($sql_lots);
				


//

	$content='';


	$size=40;

	$size1=60;


  if($UNIT_NAME==''){
	  
	  

     $UNIT_NAME= $r_lots['UNIT_NAME'];
  }


			  $S_NO=1;
			  $width=120/14;
			  $width_s=$width-5;
			  $width_w=$width+5;
 

$UNIT_NAME= $r_lots['UNIT_NAME'].' ('.$RowUserName['UserName'].')';

			  $content .= '<table border="1" cellspacing="0" cellpadding="3">
					
        <tr>
		
        <th  align="center" width="97.8%"><font size="9"><B>'.$UNIT_NAME.'</B></font></th>


      </tr>
			   <tr>



                <th  align="center"  width="'.$width_s.'%"><font size="9"><B>S No.</B></font></th>
				        <th  align="center" width="'.$width.'%" ><font size="9"><B> LOT NO </B></font></th>

                <th  align="center"  width="'.$width.'%" ><font size="9"><B>RM Date </B></font></th>
              <!--  <th  align="center"  width="'.$width.'%" ><font size="9"><B>VA Date </B></font></th> -->
                <th  align="center" width="'.$width.'%"><font size="9"><B>Station </B></font></th>
                <th  align="center" width="'.$width.'%" ><font size="9"><B>Count</B></font></th>
               <th  align="center" width="'.$width.'%" ><font size="9"><B>HL Qty</B></font></th>
				       <th  align="center" width="'.$width.'%" ><font size="9"><B>Type Of Product</B></font></th>

				<th  align="center" width="'.$width.'%" ><font size="9"><B>Received Weight</B></font></th>
				<th  align="center" width="'.$width.'%" ><font size="9"><B>HL Return </B></font></th>
        <th  align="center" width="'.$width.'%" ><font size="9"><B>Weight After VA </B></font></th>
				<th  align="center" width="'.$width.'%"><font size="9"><B>YEILD </B></font></th>
				<th  align="center" width="'.$width.'%"><font size="9"><B>Remarks </B></font></th>



               </tr>

			  ';

	$TOTAL_SENT_WEIGHT=0;
	$TotalHLRecWt=0;
	$TotalWgtAfterVa=0;
	
		//
		while($r_lots = $stmt_lots->fetch_array()){


 
$SENT_WEIGHT=$r_lots['SENT_WEIGHT'];
$RECEIVED_WEIGHT=$r_lots['HL_QTY_RCVD'];
$WgtAfterVa=$r_lots['WgtAfterValueAddition'];
$RETURN_WEIGHT=$r_lots['RETURN_WEIGHT'];
$TotalHLRecWt+=$RECEIVED_WEIGHT;
$TotalWgtAfterVa+=$WgtAfterVa;

$TOTAL_SENT_WEIGHT+=$SENT_WEIGHT;

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
				 <th  align="center" width="'.$width_s.'%"><font size="9">'.$S_NO.'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['LOT_NO'].'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$PURCHASE_DATE.'</font></th>
				<!-- <th  align="center" width="'.$width.'%"><font size="9">'.$PROCESSING_DATE.'</font></th> -->
				 <th  align="center" width="'.$width.'%" ><font size="9">'.$r_lots['UNIT_NAME'].'</font></th>
         <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['COUNT'].'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['SENT_WEIGHT'].'</font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">'.$r_lots['VARIETY'].'</font></th>


				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['HL_QTY_RCVD'].'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$RETURN_WEIGHT .' </font></th>
         <th  align="center" width="'.$width.'%"><font size="9">'. $WgtAfterVa .' </font></th>

				 <th style="color:'.$COLOR .'" align="center" width="'.$width.'%"><font size="9">'.$yield1.' </font></th>

				  <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['REMARKS'].'</font></th>

				 </tr>';

 $S_NO++;
}

      $content .=

    '<tr>

   <th  align="center" width="37.9%"><font size="9"><B>Total </B></font></th>

    <th  align="center" width="8.5%"><font size="9"><B>'. $TOTAL_SENT_WEIGHT.'</B></font></th>
    <th  align="center" width="8.6%"><font size="9"><B></B></font></th>
    <th  align="center" width="8.6%"><font size="9"><B>'. $TotalHLRecWt.'</B></font></th>
	    <th  align="center" width="8.6%"><font size="9"><B></B></font></th>

    <th  align="center" width="8.6%"><font size="9"><B>'.$TotalWgtAfterVa.'</B></font></th>

   </tr>';
 
		$content .= '</table>';



		$Data['TOTAL_SENT_WEIGHT']=$TOTAL_SENT_WEIGHT;
		$Data['TotalHLRecWt']=$TotalHLRecWt;
		$Data['TotalWgtAfterVa']=$TotalWgtAfterVa;
		$Data['TABLE']=$content;
		
	return $Data;

}

?>
