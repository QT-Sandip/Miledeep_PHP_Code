<?php
session_start();
include "../includes/config.php";
include "../includes/Globals.php";

 //$ID=$_GET['id'];
 $ID=$_GET['INV_ID'];

require_once('../tcpdf/tcpdf.php'); 
	
	
class MYPDF extends TCPDF {

   public function Header() {	
       // $image_file = 'images/ds_logo.jpg';
        $this->Image($image_file, 15, 5, 25, 25, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
        //$this->SetFont('helvetica', '', 6);
		$this->Cell(0, 5, 'CERTIFICATE OF ORIGIN', 0,1, 'C', 0, '', 0, false, 'T', 'M');
		
		 $this->SetFont('helvetica', 'B', 12);
				
    }
	
    public function Footer() {
        //$this->SetFont('helvetica', 'I', 8);
		//$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);
		//$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
   }
}  

    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('APTA Report');  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(10); 
	$pdf->SetHeaderMargin(0);	
    $pdf->SetMargins(10, 32, 10,5);  
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(true);  
	
    $pdf->SetAutoPageBreak(TRUE, 5);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage();  
	
	
	
	$table_data=getDS_TABLE($iconn,$ID);
	$pdf->writeHTML($table_data);
	 $pdf->SetFont('helvetica', '', 6);
	
  //$pdf->Cell(0, 2, 'DS-2031', 0,0, 'L', 0, '', 0, false, 'T', 'M');
 // $pdf->Cell(0, 2, '(Formerly DSP -121)', 0,1, 'R', 0, '', 0, false, 'T', 'M');
  
$file_name='APTA';
$pdf->Output($file_name, 'I');


			


function getDS_TABLE($iconn,$ID){
	
	 include "Constants.php";
	
			$F_NAME='';
			$T_O_MCS='';
			
			
			
			$sql_INV_DATA="SELECT * FROM `INVOICE_DATA` WHERE ID='$ID'";
			$stmt_INV_DATA=$iconn->query($sql_INV_DATA);
			$INV_DATA = $stmt_INV_DATA->fetch_assoc();
			
			$consign_exp = explode(',', $INV_DATA['CONSIGNMENT_EXPORTED_TO']);
			$CONSIGNMENT_EXPORTED_TO=$consign_exp[0].', CONTAINER NO :'.$consign_exp[1].'
			, SEAL NO :'.$consign_exp[3];
			
			
			
		    $sql_PO_VARIETY="SELECT * FROM `PO_VARIETY_LIST` WHERE INV_ID='$ID'";
			$stmt_PO_VARIETY=$iconn->query($sql_PO_VARIETY);
			$PO_VARIETY = $stmt_PO_VARIETY->fetch_assoc();
			
			
			$sql_APTA="SELECT * FROM `APTA_TABLE` WHERE ID='$ID'";
			$stmt_APTA=$iconn->query($sql_APTA);
			$APTA = $stmt_APTA->fetch_assoc();
			
			
			//$DOE=getFormatedDate($DSP_DATA['EXPORT_DATE']);
			//$DOS=getFormatedDate($DSP_DATA['DOC_DATE']);
			//
			//$NAME_ADD_CONS=$INV_DATA['NAME_ADD_OF_CONSIGNEE'].$INV_DATA['ADDRESS_OF_CONSIGNEE']; CMNTD BY ME
			$QUANTITY_IN_KGS=$INV_DATA['QUANTITY_IN_KGS'];
	
	
			$sql_code_list="SELECT * FROM `CODE_LIST_CREATE` WHERE INV_ID='$ID'";
			$stmt_code_list=$iconn->query($sql_code_list);
			
			$COUNT=1;
			while($r_code_list = $stmt_code_list->fetch_assoc()){
				$T_O_MCS=$T_O_MCS+$r_code_list['T_O_MCS'];
				/*if($F_NAME==''){
				
				}else{

				}  */
				
			$DESCRIPTION=''.$r_code_list['T_O_MCS'].' CARTONS OF '.$PO_VARIETY['FULL_NAME'].',
				PACKING: '.$r_code_list['PACKING'].'PER MASTER CARTON, NET WEIGHT: 
				, SIZE (PCS/LB): '.$r_code_list['GRADES'].' ';
				$COUNT++;
			}
			
			
	
	$content='';
	
	$size=50;	
	$size1=50;
	
	
				
	$EXP=explode(',',$QUANTITY_IN_KGS);
	$LBS=$EXP[0];
	$KGS=$EXP[1];
	$DOT_LINE='..........................................';
 
 $INV_TMSP = strtotime($INV_DATA['INVOICE_DATE']);
            $INVOICE_DATE = date("d-m-Y", $$INV_TMSP);

 
	 $content .= '<table border="1" cellspacing="0" cellpadding="3">  
				<tr>  
           		
				<th  align="LEFT"  width="'.($size).'%"><font size="11">
				1.Goods consigned from:<br> (Exporter`s business name,address,court)
				<br> '.$EXPORTOR.'<br>'.$EXPORTOR_ADD .'</font></th>
                
				<th  align="CENTER" width="'.($size1).'%"><font size="11">
				Reference No. <b>******************* </b>
				<br><br><b align="center"> ASIA- PACIFIC TRADE AGREEMENT 
				<br>(APTA)</b> 
				 <p align="center">(Combined declaration and certificate) </p>
				<br><br> Issued in <b>INDIA</b> <br> (country)</font></th>
                

				</tr>
				
				
				<tr>  
           		<th  align="LEFT"  width="'.($size).'%"><font size="9">2.Goods consigned to :
				<BR> (Consignees name,address,country )
				<BR>'.$INV_DATA['NAME_ADD_OF_CONSIGNEE'].'  <br>  '.$INV_DATA['ADDRESS_OF_CONSIGNEE'].'<br> '.$INV_DATA['DESTINATION_COUNTRY'].' <br> <br>  
				 </font></th>

                <th  align="LEFT" width="'.($size1).'%"><font size="8">3. For officail use
				<br><br><br><BR> <BR></font></th>
                

				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="9">4.Means of transport and route : 
				<br><br><br>  By Sea <br> BY ROAD: FROM BHIMAVARAM TO VISAKHAPATNAM,INDIA,. 
				BY SEA : FROM VISAKHAPATNAM,INDIA TO '.$INV_DATA['PORT_OF_DIS'].' '
				.$INV_DATA['DESTINATION_COUNTRY'].'   </font></th>
               </tr>
				
				
				
				<tr>  
           		<th  align="LEFT"  width="65"><font size="9">5.Tariff item Number  </font></th>
				
                <th  align="LEFT" width="12%"><font size="9">6.Marks and numbers of package: </font></th>
                <th  align="LEFT" width="40%"><font size="9">7.Number and kind of packages/description of goods: </font></th>
                <th  align="LEFT" width="12%"><font size="9"> 8.Origin criterion (see notes overleaf) </font></th>
                <th  align="LEFT" width="12%"><font size="9"> 9.Gross weight or other quantity:  </font></th>
                <th  align="LEFT" width="12%"><font size="9"> 10.Number and date of invoices:</font></th>
                
				</tr>
				
				<tr>  
           		<th  align="CENTER"  width="12%"><font size="9">030617 </font></th>
                <th  align="CENTER" width="12%"><font size="9"> NO MARKS </font></th>
                <th  align="LEFT" width="40%"><font size="9"> '.$DESCRIPTION.','.$CONSIGNMENT_EXPORTED_TO.'</font></th>
                <th  align="CENTER" width="12%"><font size="9">"A"</font></th>
                <th  align="LEFT" width="12%"><font size="9">'.$LBS.'<br> '.$KGS.' </font></th>
                <th  align="LEFT" width="12%"><font size="9"> '.$INV_DATA['INVOICE_NUMBER'].
				'  <br> '.$INVOICE_DATE.'</font></th>
                
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="9"> <BR><BR><BR> </font></th>
				</tr>
				
				
				<tr>  
           		<th  align="CENTER"  width="50%"><font size="9"><b>
				11.Declaration by the exporter</b> <br>    
				The undersigned hereby declare that the above details and statements
				are correct: that all the goods were produced in  <br> <b> INDIA </b> <br>'.$DOT_LINE.'
				
				<br>(country)<br>
				And that they comply with the origin requirements specified for these goods
				in the Asia - Pacific Trade Agreement for goods  exported to <BR><BR><BR><BR> 
				'.$INV_DATA['DESTINATION_COUNTRY'].' BHIMAVARAM,25.05.2020
				<BR> Place and date, signature of the authorized Signatory.</font></th>
                
				
				
				
				
				<th  align="CENTER" width="50%">  <font size="9"><b> 12. Certificate </b>  <br>    
				It is hereby certified on the basis of control carried out, that the declaration 
				by the exporter is correct.  <br> <BR> <br> To verify this certificate, you may scan 
				the QR code here <br><br><br>            <b>              EIC  ROUND SEAL </b>
				<br><br><br> '.$DOT_LINE.' <BR> Place and date, signature and Stamp of Certifying authority
				</font></th>
                
				</tr>
				
				
				</table>'; 
	
	return $content;	
	
}

?>