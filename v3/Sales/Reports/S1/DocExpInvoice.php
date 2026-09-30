<?php
session_start();
include "../includes/config.php";
include "../includes/Globals.php";
$ID=$_GET['id'];
include "Constants.php";
require_once('../tcpdf/tcpdf.php'); 
	

class MYPDF extends TCPDF {

    public function Header() {	
        $image_file = 'images/ds_logo.jpg';
        //$this->Image($image_file, 15, 5, 25, 25, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', '', 6);
		//$this->Cell(0, 2, 'OMB APPROVAL NO. 1405-0095', 0,1, 'R', 0, '', 0, false, 'T', 'M');
		//$this->Cell(0, 2, 'EXPIRATION DATE: 01/31/2020', 0,1, 'R', 0, '', 0, false, 'T', 'M');
		//$this->Cell(0, 2, 'ESTIMATED BURDEN : 10 minutes', 0,1, 'R', 0, '', 0, false, 'T', 'M');
		 $this->SetFont('helvetica', 'B', 15);
		//$this->Cell(0, 5, 'US Department of State', 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 2, ' INVOICE', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		//$this->Cell(0, 5, '(SEE INSTRUCTIONS ON REVERSE)', 0, 1, 'C', 0, '', 0, false, 'T', 'M');		
    }
	
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		//$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
   }
}


	
    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	
	
    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('Export Invoice: '.$row['PO_NUM'].' - '.$row['PO_DATE']);  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(10); 
	$pdf->SetHeaderMargin(0);	
    $pdf->SetMargins(10, 10, 10,5);  
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(true);  
	
    $pdf->SetAutoPageBreak(TRUE, 5);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage();  
	
	
	
	$table_data=getDS_TABLE($iconn,$ID);
	$pdf->writeHTML($table_data);
	 $pdf->SetFont('helvetica', '', 6);
	
  //$pdf->Cell(0, 2, 'DS-2031', 0,0, 'L', 0, '', 0, false, 'T', 'M');
  //$pdf->Cell(0, 2, '(Formerly DSP -121)', 0,1, 'R', 0, '', 0, false, 'T', 'M');
  
$file_name='Ds'.$row['PO_NUM'];
$pdf->Output($file_name, 'I');


			


function getDS_TABLE($iconn,$ID){
	include "../qc/Constants.php";
	include "../includes/func.php";
	global $TblScrmPoGradeData;
	
			$F_NAME='';
			$T_O_MCS='';
			$DOE='';
			$DOS='';
			$TOTAL_MCS=0;
			$TOTAL_SLAB=0;
			$T_EACH_SLAB=0;
			$TOTAL_NET_WT_KGS=0;
            $TOTAL_NET_WT_LBS=0;			
	        
			
		//UMA
		
		$SQL_PO_GRADE_DATA="SELECT * FROM `$TblScrmPoGradeData` where InvId='$ID'  ";
$STMT_PO_GRADE_DATA=$iconn->query($SQL_PO_GRADE_DATA);
			WHILE($PO_GRADE_DATA = $STMT_PO_GRADE_DATA->fetch_assoc()){

   $SLAB=	$PO_GRADE_DATA['SLAB']; 
    $TOTAL_SLAB+=$SLAB;

  $EACH_SLAB	=	$PO_GRADE_DATA['EACH_SLAB'];
    $T_EACH_SLAB+=$EACH_SLAB;

  $MCS=	$PO_GRADE_DATA['MCS']; 
    $TOTAL_MCS+=$MCS;

  $UOM=	$PO_GRADE_DATA['UOM']; 
  

  if($UOM=="LBS"){
   $NET_WT_LBS=(($SLAB*($EACH_SLAB))*$MCS); 
     $NET_WT_KGS=(($SLAB*($EACH_SLAB))*$MCS)/2.2; 

  
  }
 else if($UOM=="KGS"){
   $NET_WT_KGS=(($SLAB*($EACH_SLAB))*$MCS) ;
   $NET_WT_LBS=(($SLAB*($EACH_SLAB*2.2))*$MCS) ; 

  
  }
  $TOTAL_NET_WT_KGS=$NET_WT_KGS+$NET_WT_KGS;    
	   $TOTAL_NET_WT_LBS=$NET_WT_LBS+$NET_WT_LBS;		
	
			}
			$TOTAL_NET_WT_KGS;		
            $TOTAL_NET_WT_LBS;	
	        $TOTAL_MCS;

 

 $sql_banking="SELECT * FROM `SHIPPING_AND_BANKING_DETAILS`where INV_ID='$ID' ";
	$stmt_banking=$iconn->query($sql_banking);
	$row_banking=$stmt_banking->fetch_assoc();
	
	$SWIFT_CODE=$row_banking['SWIFT_CODE'];
	 $SHIPMENT_DATE=date('d-m-y',strtotime($row_banking['SHIPMENT_DATE']));
	//echo $SHIPMENT_DATE=date('F d y',strtotime($row_banking['SHIPMENT_DATE']));  exit; 
	 
	//ECHO $SHIPMENT_DATE_WORD= date('d F Y', strtotime($SHIPMENT_DATE));  EXIT;

	$BL_NO=$row_banking['BL_NO'];
	$CUSTOMS_ASSIGN=$row_banking['CUSTOMS_ASSIGN'];
    $SB_NO=$row_banking['SB_NO'];
    $SHIPMENT_TERM=$row_banking['SHIPMENT_TERM'];
    $TERMS_DELIVERY=$row_banking['TERMS_DELIVERY'];
    $CONTAINER_NO=$row_banking['CONTAINER_NO'];
    $SEAL_NO=$row_banking['SEAL_NO'];
    $VESSEL_FREIGHT_NO=$row_banking['VESSEL_FREIGHT_NO'];
    $INVOICE_VALUE_US=$row_banking['INVOICE_VALUE_US'];
    $OCEAN_FREIGHT_US=$row_banking['OCEAN_FREIGHT_US'];
    $PORT_TAXES=$row_banking['PORT_TAXES'];
    $FOB_VALUE_IN_US=$row_banking['FOB_VALUE_IN_US'];

		
/***/		
			$sql_INV_DATA="SELECT * FROM `INVOICE_DATA` WHERE ID='$ID'";
			$stmt_INV_DATA=$iconn->query($sql_INV_DATA);
			$INV_DATA = $stmt_INV_DATA->fetch_assoc();
			
			$INVOICE_NUMBER=$INV_DATA['INVOICE_NUMBER'];
			$NAME_ADD_OF_CONSIGNEE=$INV_DATA['NAME_ADD_OF_CONSIGNEE'];
			$ADDRESS_OF_CONSIGNEE=$INV_DATA['ADDRESS_OF_CONSIGNEE'];
			$INVOICE_DATE=$INV_DATA['INVOICE_DATE'];
			$DESTINATION_COUNTRY=$INV_DATA['DESTINATION_COUNTRY'];
			$PORT_OF_DIS=$INV_DATA['PORT_OF_DIS'];
			$BRAND=$INV_DATA['BRAND'];
			$DESTINATION_PORT=explode(',',$INV_DATA['PORT_OF_DIS']);
			

			
			$PO_NUM=$INV_DATA['PO_NUM'];
			$PO_DATE=getFormatedDate($INV_DATA['PO_DATE']);
			

			//$NAME_ADD_CONS=$INV_DATA['NAME_ADD_OF_CONSIGNEE'].$INV_DATA['ADDRESS_OF_CONSIGNEE']; CMNTD BY ME
			$QUANTITY_IN_KGS=$INV_DATA['QUANTITY_IN_KGS'];
	
	
			
	$content='';
	
	$size=45;	
	$size1=55;	
	
	$EXP=explode(',',$QUANTITY_IN_KGS);
	$LBS=$EXP[0];
	$KGS=$EXP[1];
	include "Constants.php";
 
	  $content .= '<table border="1" cellspacing="0" cellpadding="0">  
				
				 <tr>  
				<th width="45%">
				<table border="1" cellspacing="0" cellpadding="2">
				
				
				<tr>  
           		
				<th  align="LEFT" ><font size="7"><b>Exporter / Shipper:</b>
				<br>'.$EXPORTOR .'<br>'.$EXPORTOR_ADD.' 
				<BR>IE CODE # '.$EXPORTOR_IE_CODE.' <BR>GSTIN #  '.$EXPORTOR_GSTIN.'</font></th>
				</tr>
	
			
				
				
				<tr>  
           		
				<th  align="LEFT" ><font size="7"><b><u>Applicant/Ultimate Consignee</u></b>
				<br>'.$NAME_ADD_OF_CONSIGNEE.',<br>'.$ADDRESS_OF_CONSIGNEE.'
				<br><br><br><br>
				<br><br><br><br>
				<br><br><br><br>
				<br><br><br><br>
				</font></th>
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Pre-Carriage by
				<br>REFER CONTAINER</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Place of Receipt by
				<br>VISAKHAPATNAM PORT, INDIA</font></th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Vessel / Freight No.  <BR>'.$VESSEL_FREIGHT_NO.'  
				<br></font></th>
				<th  align="LEFT" width="50%" ><font size="7">Port of Loading
				<br>VISAKHAPATNAM PORT, INDIA</font></th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Port of Discharge
				<br>'.$PORT_OF_DIS.'</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Final Destination
				<br>'.$PORT_OF_DIS.'</font></th>
				
				</tr>
				</table>
				</th>
				
				
				<th width="55%">
				<table border="1" cellspacing="0" cellpadding="2">
				
				<tr> 
				<th  align="LEFT" ><font size="7"><b>Invoice No. & Date.</b>
				<br>'.$INVOICE_NUMBER.',Dated '.$INVOICE_DATE.' </font></th>
				</tr>
				
				<tr> 
				<th  align="LEFT" ><font size="7"><b>Buyer`s Order No. & Date. </b>
				<br>PO #'.$PO_NUM.' Dated :'.$PO_DATE.' </font></th>
				</tr>
				
				<tr > 
				<th  align="LEFT"  ><font size="7" ><b>Other Refrence(s) :
				<b style="color:white">_________________________</b>  E.I.A. APPROVAL# '.$EXPORTOR_APP_NUM.'</b>
				<br>
				<u><b>Packer</u> :</b><br>'.$PROCESSOR.'<br>'.$PROCESSOR_ADD.'  
				<br>USFDA REGISTRATION NO  : '.$EXPORTOR_USFDA.'<BR>FEI # :  '.$EXPORTOR_FEI.'</font></th>
				</tr>
				
				<tr> 
           		
                <th  align="left" width="100%"><font size="7">Buyer (If other than Consignee) :<br>
				<b><u>Importer of Record:</u></b><br>CUSTOMS ASSIGN  # '.$EXPORTOR_CUSTOMS.'
				<br>'.$EXPORTOR .'<br>'.$EXPORTOR_ADD.' </font></th>
				</tr>
				
				<tr> 
				<th border="1"  align="left" height="50%" width="50%"><font size="7">Country of Origin of Goods 	
				<br><b align="center">INDIA</b></font></th>
                <th border="1"  align="left" width="50%"><font size="7">Country of Final Destination  
				<br><b align="center">  '.$DESTINATION_COUNTRY.'</b></font></th>
				</tr>
				
				<tr> 
				<th  align="LEFT"  width="100%"><font size="7"><b><u>Terms of Delivery and Payment:</u>
				</b><br><BR>BY DA   '.$TERMS_DELIVERY.' DAYS FROM SIGHT <br>
				SHIPMENT TERM : '.$SHIPMENT_TERM.'<br><br><br><br><br></font> </th>
				</TR>
				
				
				</table>
				</th>
				
				</tr> 
				
				
				
				
				
				
				<tr> 
<th width="100%" >
				<table border="1" cellspacing="0" cellpadding="2">	
<tr> 				
           		<th  align="center"  width="12.5%"><font size="7">Shipping Marks: <BR> NO MARKS</font></th>
                <th  align="LEFT" width="10%">  <font size="7">No. & kind of packings <BR><b> '.$TOTAL_MCS.'</b> MCS</font></th>
                <th  align="LEFT" width="22.5%"><font size="7">Container Number <br><br><b>'.$CONTAINER_NO.'</b></font></th>
                <th  align="LEFT" width="22%"><font size="7">Seal Number <br><br><b>'.$SEAL_NO.'</b></font></th>
                <th  align="center" width="11%"><font size="7">Quantity in Lbs </font></th>
                <th  align="center" width="11%"><font size="7">Unit Price <br>USD/LB  <br><br><b>'.$DESTINATION_PORT[0].'</b></font></th>
                <th  align="center" width="11%"><font size="7">Amount in U.S.D DDP<br><br><b> '.$DESTINATION_PORT[0].'</b></font></th>
                </tr>
				
				</table>
				</th>
				</tr>
				
				';
			
				$description_head='<B><u>Description of Goods: </u></B><BR>1 X 40 FCL <BR>';
				$W_SPACE='<b style="color:white"> yuhjutgfrtgujkiolmnbvgftrkliomkjhygtfrdeswderfgtyhjmnkih</b>';
				$TOTAL_MCS=0;
			$content.='<tr >
				
			<table border="0" cellspacing="0" cellpadding="2">';
			
			
			$sql_varity_name="SELECT DISTINCT Variety FROM `$TblScrmPoGradeData` WHERE InvId='$ID'";
			$stmt_varity_name=$iconn->query($sql_varity_name);
			WHILE($type=$stmt_varity_name->fetch_assoc()){
				
			$VARIETY_NAME=$type['Variety']; 
			
		
		
			$sql_PO_GRADE_DATA="SELECT * FROM `$TblScrmPoGradeData` WHERE Variety='$VARIETY_NAME' and InvId='$ID'";
			$stmt_PO_GRADE_DATA=$iconn->query($sql_PO_GRADE_DATA);
			
			$SQL_MCS="SELECT SUM(Mcs) AS VMCS FROM `$TblScrmPoGradeData` 
			WHERE 
			InvId='$ID' and
			Variety='$VARIETY_NAME'";
			
			$STMT_VMCS=$iconn->query($SQL_MCS);
			$R_VMCS=$STMT_VMCS->fetch_assoc();
			$VMCS=$R_VMCS['VMCS'];
			
			
			$TOTAL=0;
			$AMOUNT_PER_LAB=0;
			$TOTAL_AMOUNT_PER_LAB=0;
			$TOTAL_QUANTITY_PER_GRADE=0;
			//$TOTAL_MCS=0;
			$TOTAL_MCSS=0;
			
				
			
			

			$COUNT=0;
			WHILE($PO_DATA = $stmt_PO_GRADE_DATA->fetch_assoc()){
				$PACKING = $PO_DATA['PACKING'];	
				$AMOUNT_PER_LAB=$PO_DATA['AMOUNT_PER_LB'];
				$TOTAL_AMOUNT_PER_LAB+=$AMOUNT_PER_LAB; 
				$QUANTITY_PER_GRADE= $PO_DATA['SLAB']*$PO_DATA['EACH_SLAB']*$PO_DATA['MCS'];
				$TOTAL_QUANTITY_PER_GRADE	+=$QUANTITY_PER_GRADE;
				
				
				if($COUNT==0){
					$description= $VMCS.' CARTONS OF '.$VARIETY_NAME.' 
						 " '.$BRAND.' "  BRAND  PACKING: '.$PACKING.'
						 NET WEIGHT PER CARTON  <BR>
						 <b style="color:white"> yuhjutgfFDFrtgujkiolmnbvgftrkliomkjhygtfrde
						 swderfgtyhjmnkih</b><U>SIZE</U>'; 				
				}ELSE{
					$description= '';
				}
$COUNT++;

if($description!= ''){
				
			
			$content.='
				<tr>
				<th  align="LEFT"  width="67%"  style="border-right: 1px solid black">
				<font size="7" >'.$description_head.'
				'.$description.'
				</font> 
				</th>
				<th align="center" width="11%" style="border-right: 1px solid black"><font size="7"></font></th>
				<th align="center" width="11%" style="border-right: 1px solid black"><font size="7"></font></th>
				<th align="center" width="11%" style="border-right: 1px solid black"><font size="7"></font></th>
				
				</tr>';
}
				$content.='<tr>

				<th  align="LEFT"  width="67%"  style="border-right: 1px solid black">
				<font size="7" >
				'.$PO_DATA['GRADE'].'<b>'.$W_SPACE.$PO_DATA['MCS'].' </b> </font> 
				</th>';
				
				$content.='
				<th align="center" width="11%" style="border-right: 1px solid black">
				<font size="7" >'.$QUANTITY_PER_GRADE.'</font>
				</th>
				<th align="center" width="11%" style="border-right: 1px solid black"><font size="7">'.$PO_DATA['AMOUNT_PER_LB'].'</font></th>
				<th align="center" width="11%"><font size="7">'.$QUANTITY_PER_GRADE * $PO_DATA['AMOUNT_PER_LB'].'</font></th>
				</tr>';
              
			  
			  $description='';
			  $description_head='';
			}
			 $TOTAL_MCS=$TOTAL_MCS+$VMCS;
			}
			
			$content.= '</table>
			  </tr>';
			/*   $TOTAL= $PO_DATA['MCS'] .'<B style="color:white">yuhfrtfghjcvggu</B>'.$QUANTITY_PER_GRADE .'<B style="color:white">yuhfrtfghj</B>'
			   .$PO_DATA['AMOUNT_PER_LB'] . '<B style="color:white">yuhfrtfghj</B>'. $QUANTITY_PER_GRADE * $PO_DATA['AMOUNT_PER_LB']	;
			 */
				
				
				 $BILL=$TOTAL_QUANTITY_PER_GRADE* $TOTAL_AMOUNT_PER_LAB;
			  
			  $TOTAL_AMOUNT_PER_LAB; 
			  $TOTAL_QUANTITY_PER_GRADE; 

           $TOTAL= '<th  align="LEFT"  width="67%"><font size="7">TOTAL '.$W_SPACE.''.$TOTAL_MCS.'</font> </th>
			   <th width="11%"><font size="7"> '.$TOTAL_QUANTITY_PER_GRADE.'</font></th>
				<th width="11%"><font size="7"></font></th>
				<th width="11%"><font size="7"> '.$TOTAL_QUANTITY_PER_GRADE* $TOTAL_AMOUNT_PER_LAB.'</font></th>';
				
				

				$BILL_WORD=numberTowords($BILL);
				
				
				$content.='<tr> '.
				$TOTAL.'
           		
               
				</tr>
				
				
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="7"> SHIPPING TERMS   : '.$SHIPMENT_TERM.'<BR>
				
				<BR><BR> <BR><BR><br>

				 NET WEIGHT:  <b style="color:white"> yuhjutgf</B> '.$TOTAL_NET_WT_LBS.' LBS
				<b style="color:white"> yuhjutgfrtgujkiolmnbvgftrkliomkjhygtfrdeswderf</b>'.$TOTAL_NET_WT_KGS.'KGS<BR><BR> GROSS WEIGHT: 
				</font></th>
               </tr>
				
				
				 
				
				<tr>  
           		<th  align="LEFT"  width="70%"><font size="6">  <BR> <BR> <BR>
				<BR>  <BR><BR><B><U>DECLARATION:</U></B> WE DECLARE THAT THIS INVOICE SHOWS
				THE ACTUAL PRICE OF GOODS DESCRIBED AND THAT ALL PARTICULARS ARE TRUE AND CORRECT</font></th>

            
                <th  align="LEFT"  width="30%"><font size="6"> Signature</font></th>
           		
				
				</tr>
				  
				

				
				
				</table>'; 
	
	return $content;	
	
}

?>