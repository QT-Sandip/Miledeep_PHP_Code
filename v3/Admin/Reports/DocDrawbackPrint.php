<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PoID=$_GET['id'];

include ($_SERVER['DOCUMENT_ROOT']."/".$_SESSION["IncludePath"]."SalesInculdes/SalesConstants.php");
include ($_SERVER['DOCUMENT_ROOT']."/".$_SESSION["IncludePath"]."Includes/Globals.php");

include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");




$PoID=$_GET['id'];
 $ID=$_GET['id'];
 $Type=$_GET['Type'];
include "PrintHeaderInc.php";
 

class MYPDF extends TCPDF { 
 
    public function Header() {
		
	
global $Header;		
        $image_file = 'images/ds_logo.jpg';
        $this->SetFont('helvetica', 'BU', 20);
		$this->Cell(120, 0, 'APPENDIX - III', 0, 1, 'R', 0, '', 0, false, 'T', 'M');
		        $this->SetFont('helvetica', '', 12);

		$this->Cell(135, 0, 'DRAW BACK / DEEC DECLARATION', 0, 1, 'R', 0, '', 0, false, 'T', 'M');
		$this->Cell(142, 0, '(to be filled for export goods under for draw back)', 0, 1, 'R', 0, '', 0, false, 'T', 'M');
		 $this->SetTextColor(255,0,0);
		
		$this->Cell(10, 0, ''.$StatusTerm, 0, 1, 'L', 0, '', 0, false, 'T', 'M');

    }
	
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
   }
}


    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	
	
    
	$pdf->SetCreator(PDF_CREATOR);
	 //$pdf->SetFont('helvetica', 'BU', 10);
	
    $pdf->SetTitle('APPENDIX-III');  
    $pdf->SetTitle(''.$Header2);  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(10);
	
	$pdf->SetHeaderMargin(8);	
	     
    $pdf->SetMargins(10, 10, 10,5);  
	
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(false);  
	
    $pdf->SetAutoPageBreak(TRUE, 10);

    $pdf->SetFont('helvetica', '', 11);  
    //$pdf->ln;  
	
	    $pdf->AddPage();  

	
	$table_data=getDS_TABLE($iconn,$ID);
	$pdf->writeHTML($table_data);
	 $pdf->SetFont('helvetica', '', 6);

  
//$file_name=$ExporterData['InvShortCode'].'_'.$PoDataRow['InvNo'].'.pdf';

/***uma 28-07-2022**/
$CurrentMonth = date('n',strtotime($CustomInvoiceRow['CInvDate']));   
if($PoDataRow['ExporterID']==5){		
if($CurrentMonth<=3){
	
 
 $NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']));  	
 $CurrentYear = date('y',strtotime($CustomInvoiceRow['CInvDate']))-1;  	
	
} 
else{ 

 $CurrentYear = date('y',strtotime($CustomInvoiceRow['CInvDate']));  	 
$NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']))+1;  	
 
}
		}
		else{		
if($CurrentMonth<=3){
	
 	
 
 $NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']));  	
 $CurrentYear = date('Y',strtotime($CustomInvoiceRow['CInvDate']))-1;  	

	
} 
else{ 
 
$CurrentYear = date('Y',strtotime($CustomInvoiceRow['CInvDate']));  	 
$NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']))+1;  	
  
}
		}
/***uma 28-07-2022**/

$file_name=$ExporterData['InvShortCode'].'-A.C-'.$CustomInvoiceRow['CInvNo'].'-'.$CurrentYear.'-'.$NxtYear.'.pdf';
//sme/pi/233/2022-23

//echo $Type;

ob_end_clean();
if($Type==''){
$Type='I';
}else{
	$Fname=__DIR__ ."/TempFolder/";
	$Fname=$_SERVER['DOCUMENT_ROOT']."SalesNew/Reports/TempFolder/";
	$file_name=$_GET['FileName'];
	$file_name = str_replace('/','', $file_name );;
	$file_name=$Fname.$file_name;
}
$pdf->Output($file_name, $Type);


			


function getDS_TABLE($iconn,$ID){
	
	global $PoDataRow,$PoID;
	
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/func.php");
//include ($_SERVER['DOCUMENT_ROOT']."/QualityControl/Constants.php");
		
	include "../SalesInculdes/SalesConstants.php"; 
	
			
		
			
	$content='';
	                                                                  

	$size=45;	
	$size1=11;	
	$FontSize=9;	

	
				$Whitspace='<b style="color:white"> yuhjujrbfnbjfkkdrujn</b>';
				$Space='<b style="color:white"> bjfkkdrujn</b>';
				$Sppace='<b style="color:white">b</b>';

$StyleTbl='Style="border: 1px solid #ccc;
  color: #37393b;
  margin: 10px 0 0 0;
  text-shadow: #fff 1px -1px 1px;
  text-align: left;
  width: 100%;"';
  
  $StyleTd='Style=" margin: 0;
  padding: 0;
  border: 0;
  font-size: 100%;
  vertical-align: baseline;
  background: transparent;"';
  $w1='100%';
$w2='70%';
 
	 $content .= '
	 
	 
	 <table border="0" cellspacing="0" cellpadding="0">  
				<tr> <br> <br> <br> <br>
								
			<th  align="Left"  width="100%"><font size="'.$size1.'"> Shipping Bill No. and date........................................</font></th>
           </tr>
		   <tr> 				
           		
			<th  align="Right"  width="100%"><font size="'.$size1.'">
			(Name of the exporter) do hereby</font></th>
                
                </tr>
			<th  align="Left"  width="100%"><font size="'.$size1.'">I/We,</font></th>
                
                </tr>

		   <tr>  
				<th  align="Left"  width="100%"><font size="'.$size1.'">further declare as follows:- </font></th>
           </tr> 
		   <tr>  
				<th  align="Left"  width="100%"><font size="'.$size1.'">
				1.'.$Sppace.'That the quality and specification of the goods as stated in this shipping bill are in accordance with the 
				<br> '.$Sppace.''.$Sppace.''.$Sppace.'term of the export contract entered into with the buyer / consignee in pursuance of which the goods are 
				<br> '.$Sppace.''.$Sppace.''.$Sppace.'being exported.   
				</font></th>
           </tr>

			<tr>  
				<th  align="Left"  width="100%"><font size="'.$size1.'">
				2. That there is change in the manufacturing formula and in the quantum per unit of the imported material or 
				<br>'.$Sppace.''.$Sppace.''.$Sppace.'components, if any,utilized in the manufacture of the export goods and that the materials or components
				<br>'.$Sppace.''.$Sppace.''.$Sppace.'which have been stated in the applicationunder rule 6 or rule 7 to have been imported, 
				<br>'.$Sppace.''.$Sppace.''.$Sppace.'continue to be so imported and have not been obtained from indigenous sources.
</font></th>
           </tr>
  
				<th  align="Left"  width="100%"><font size="'.$size1.'">
				3. *(A) That the export goods have not been manufactured by availing the procedure under Rule 12(1) (b) 
				<br>  '.$Sppace.''.$Sppace.'/13(1)(b) of the central excise rule 1944., OR Rule 18 and 19 of C.E.Rules.
				<br>  '.$Sppace.''.$Sppace.'*(B) That the export goods have been manufactured by availing the produce under rule 12(1) (b) /13(1) 
				<br>  '.$Sppace.''.$Sppace.'(b) of the central excise rule, 1944 but we are not claiming DBK on the basis of all industry rates. We shall 
				<br>  '.$Sppace.''.$Sppace.'be claiming DBK on basis of special brand rate in terms of rule 6 of the DBK rules, 1995.
</font></th>
           </tr>
			<tr>  
				<th  align="Left"  width="100%"><font size="'.$size1.'">
				4. *(A) That the goods are not manufactured and/ or exported in discharge of export obligation against an
				<br>  '.$Sppace.''.$Sppace.'advance license issued under the duty exemption scheme(DEEC) vide relevant import and export policy
				<br>  '.$Sppace.''.$Sppace.'in force Or
				<br>  '.$Sppace.''.$Sppace.'*(B) The goods are not manufactured and/ or exported in discharge of export obligation under the duty
				<br>  '.$Sppace.''.$Sppace.'exemption scheme (DEEC) in term of notification No.31/97 dated 1.4.97 or in discharge of export obliga-
				<br>  '.$Sppace.''.$Sppace.'tion against duty free replenishment certificate license in terms of notification No.48/2000- cus dated 25/
				<br>  '.$Sppace.''.$Sppace.'4/2000 but, l/we are claiming draw back of only the central excise portion of the duties on inputs specified
				<br>  '.$Sppace.''.$Sppace.' in the drawback schedule. Or,
				<br>  '.$Sppace.''.$Sppace.'*(C) That the goods are not manufactured and are being exported in discharge of export obligation under
				<br>  '.$Sppace.''.$Sppace.'the duty exemption scheme (DEEC), but l/we are claiming brand rate of drawback fixed.
				<br>  '.$Sppace.''.$Sppace.'*(Strike out whichever is inapplicable)
</font></th>

           </tr>
			<tr>  
				<th  align="Left"  width="100%"><font size="'.$size1.'">
				5. That goods are not manufactured and or exported after availing of the facility under the duty entitlement
				<br>  '.$Sppace.''.$Sppace.'passbook scheme as contained in para 7.14, read with para 7.17 of the export and import policy (April,
				<br>  '.$Sppace.''.$Sppace.'1997-31st March 2002),
</font></th>
           </tr>
			<tr>  
				<th  align="Left"  width="100%"><font size="'.$size1.'">
				6. Those goods are not manufactured and or exported licensed as 100% export oriented unit terms of
				<br>  '.$Sppace.''.$Sppace.'imports and exports policy in force.
				
</font></th>
           </tr>
			<tr>  
				<th  align="Left"  width="100%"><font size="'.$size1.'">
				7. That goods are not manufactured and or exported by a unit situated in any free trade zone / export
				<br>  '.$Sppace.''.$Sppace.'processing zone or any such zone.
</font></th>
           </tr>
			<tr>  
				
				<th  align="Left"  width="100%"><font size="'.$size1.'">
				8. That the goods are not manufactured partly or wholly in bond under section 65 of the Customs act, 1962.
</font></th>
           </tr>
		   <tr>  
				<th  align="Left"  width="100%"><font size="13">
				9. That the present market value of goods is as follows :- </font>
<br>'.$Space.'<b><u>S.NO.</b></u>'.$Whitspace.' <b><u>Item No. in Invoice</b></u>'.$Whitspace.' <b><u>Market Value</b></u></th>

</tr>

		<tr>  
			<br>
			<th  align="Left"  width="100%"><font size="'.$size1.'">
				10. That the export value of the goods covered any this shipping bill is not less then the total value of all
				<br>'.$Sppace.''.$Sppace.''.$Sppace.''.$Sppace.' imported material used in manufactured of such goods.
</font></th>


           </tr>
		<tr>  
			<th  align="Left"  width="100%"><font size="'.$size1.'">
				11. That the market price of the goods being exported is not less than the drawback amount being claimed.
</font></th> 

		</tr>
		<tr>  
			<th  align="Left"  width="100%"><font size="'.$size1.'">
				12. That the drawback amount claimed in more than 1% of the FOB value of the export product; or the
				<br>'.$Sppace.''.$Sppace.''.$Sppace.''.$Sppace.'drawback amount claimed is less than 1% of the FOB value but more than Rs.500.00 against the ship-
				<br>'.$Sppace.''.$Sppace.''.$Sppace.''.$Sppace.'ping bill.
</font></th>
           </tr>
		<tr>  
			<th  align="Left"  width="100%"><font size="'.$size1.'">
				13. I/ We under take to repatriate export proceed within 6 months from the date of export and submit B.R.C.
				<br>'.$Sppace.''.$Sppace.''.$Sppace.''.$Sppace.' to Deputy Commissioner/Asst. Commissioner (Drawback). In case, the export proceed are not realized
				<br>'.$Sppace.''.$Sppace.''.$Sppace.''.$Sppace.' within 6 months from the date of export, I/ We will either furnish extension of time from R.B.I and submit
				<br>'.$Sppace.''.$Sppace.''.$Sppace.''.$Sppace.' B.R.C. with in such extended period or will pay back the drawback received against this shipping bill.
	</font>
	<br><br>
	<b style="color:white"> b</b>Dated:
	</th>
           </tr>
		  
		<tr> 
				<th width="100%" >
				<table border="0" cellspacing="0" cellpadding="2">	
<tr> 				
           	<th  align="Right"  width="100%"><font size="11">
			NAME AND SIGNATURE OF THE EXPORTER</font></th>
                
                </tr>
				
				
				
				</table>
				</th>
				</tr>
				
				';
				
				
			$content.='</table>';
			  return $content;
	
	}


?>