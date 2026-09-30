<?php
session_start();
$IsHeader=0;
$IsFooter=0;
$QueryParms=$_GET['Q'];  

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
include ($cp."Includes/IncludePage.php");

$PoID=$_GET['PoID'];
$ReportName=$_GET['ReportName']; 
$TYPE=$_GET['TypeProduct']; 
$FileType=$_GET['Type'];

$SqlScrmPodata="SELECT PoNo FROM `$TblScrmPoData` WHERE PoID='$PoID'";
$StmtPo=$iconn->query($SqlScrmPodata);
$RowPo=$StmtPo->fetch_array();

$sql_DSP_DATA="SELECT * FROM `$TblQcDspReports` WHERE InvID='$PoID'";
	$stmt_DSP_DATA=$iconn->query($sql_DSP_DATA);
	$DSP_DATA = $stmt_DSP_DATA->fetch_assoc();
	 $DOEX=getFormatedDate($DSP_DATA['ExpiryDate']);
			

class MYPDF extends TCPDF {

    public function Header() {	
 global $PoID,$DOEX;
	
			
        $image_file = 'images/ds_logo.jpg';
        $this->Image($image_file, 15, 5, 25, 25, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', '', 6);
		
		$this->Cell(0, 2, 'OMB APPROVAL NO. 1405-0095', 0,1, 'R', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 2, 'EXPIRATION DATE: ' .$DOEX, 0,0, 'R', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 2,  '', 0,1, 'R', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 2, 'ESTIMATED BURDEN : 10 minutes', 0,1, 'R', 0, '', 0, false, 'T', 'M');
		 $this->SetFont('helvetica', 'B', 12);
		$this->Cell(0, 5, 'US Department of State', 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, 'SHRIMP EXPORTER’S / IMPORTER’S DECLARATION', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, '(SEE INSTRUCTIONS ON REVERSE)', 0, 1, 'C', 0, '', 0, false, 'T', 'M');		
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
    $pdf->SetTitle('D S Purchase Register: '.$row['PoNo'].' - '.$row['PoDate']);  
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
	
  $pdf->Cell(0, 2, 'DS-2031', 0,0, 'L', 0, '', 0, false, 'T', 'M');
  $pdf->Cell(0, 2, '(Formerly DSP -121)', 0,1, 'R', 0, '', 0, false, 'T', 'M');
  
ob_clean();
if($FileType==''){
$FileType='I';
$FileName=$ReportName.'_'.$RowPo['PoNo'];
$FileName=getFormatedFileName($FileName);
$pdf->Output($FileName, 'I');
}else{
	$Fname=$MailTempFolder;
	$FileName=$_GET['FileName'];
	$FileName = str_replace('/','', $FileName );
	$FileName=$Fname.$FileName;
	$pdf->Output($FileName, $FileType);

}

			


function getDS_TABLE($iconn,$PoID){
	global $PoID;
	global $TblQcDspReports;
	global $TblQcCodeList;
	
			$F_NAME='';
			$T_O_MCS='';
			$DOE='';
			$DOS='';
			$DOEX='';
			
			
			
			$sql_INV_DATA="SELECT * FROM `ScrmPoData` as pd LEFT JOIN QcCoveringLetter as cvl on cvl.InvID=pd. PoID WHERE
			PoID='$PoID'";
			$stmt_INV_DATA=$iconn->query($sql_INV_DATA);
			$INV_DATA = $stmt_INV_DATA->fetch_assoc();
		
		
			$sql_DSP_DATA="SELECT * FROM `$TblQcDspReports` WHERE InvID='$PoID'";
			$stmt_DSP_DATA=$iconn->query($sql_DSP_DATA);
			$DSP_DATA = $stmt_DSP_DATA->fetch_assoc();
			
			
			$DOE=getFormatedDate($DSP_DATA['ExportDate']);
			$DOS=getFormatedDate($DSP_DATA['ReportDate']);
			 $DOEX=getFormatedDate($DSP_DATA['ExpiryDate']);
			
			//$NAME_ADD_CONS=$INV_DATA['NAME_ADD_OF_CONSIGNEE'].$INV_DATA['ADDRESS_OF_CONSIGNEE']; CMNTD BY ME
			$QUANTITY_IN_KGS=$INV_DATA['Quantity'];
	
	
			$sql_code_list="SELECT * FROM `$TblQcCodeList` WHERE InvID='$PoID'";
			$stmt_code_list=$iconn->query($sql_code_list);
			
			$COUNT=1;
			while($r_code_list = $stmt_code_list->fetch_assoc()){
				$T_O_MCS=$T_O_MCS+$r_code_list['T_O_MCS'];
				if($F_NAME==''){
				$F_NAME=$COUNT.'.'	.$r_code_list['NAME_OF_FARMER'].'<br>'.$r_code_list['ADD_OF_FARMER'];
				}else{
					$F_NAME=$F_NAME.'<br>' .$COUNT.' .'.	$r_code_list['NAME_OF_FARMER'].'<br>'.$r_code_list['ADD_OF_FARMER'];
				}
				$COUNT++;
			}
	
	$content='';
	
	$size=40;	
	$size1=60;	
	
	$EXP=explode(',',$QUANTITY_IN_KGS);
	$LBS=$EXP[0];
	$KGS=$EXP[1];
 
	 $content .= '<table border="1" cellspacing="0" cellpadding="3">  
				<tr>  
           		<th  align="LEFT"  width="'.($size).'%"><font size="7">1. HARVESTING NATION <br><H1 align="center">INDIA </H1></font></th>
                <th  align="LEFT" width="'.($size1).'%"><font size="7">2. AQUACULTURE FACILITY (if applicable) (Name &amp; Address)<br><b>'.$F_NAME.'</b></font></th>
                

				</tr>        
				
				
				<tr>  
           		<th  align="LEFT"  width="'.($size).'%"><font size="9">3.EXPORTER (Name Address,and Tel / Fax)
				<br> <b>'.$COMPANY_NAME.',<br>'.$COMPANY_ADD.'.Ph No. +91 8816 250999 </b> </font></th>

                <th  align="center" width="'.($size1).'%"><font size="8">4. U.S. IMPORTER 
				/ ULTIMATE CONSIGNEE (Name, address and Tel/Fax)<br><b>'.$INV_DATA['NAME_ADD_OF_CONSIGNEE'].'<br>'. $INV_DATA['ADDRESS_OF_CONSIGNEE'].'</b></font></th>
                

				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="9">5.	DATE OF EXPORT (mm-dd-yyyy)   <b>'.$DOE.'</b> </font></th>
               </tr>
				
				<tr>  
           		<th  align="center"  width="100%"><font size="10"><b>6. </b>DESCRIPTION OF PRODUCT </font></th>
               </tr>
				
				
				<tr>  
           		<th  align="LEFT"  width="33%"><font size="9"> U.S. HTS Tariff Schedule Number </font></th>
                <th  align="LEFT" width="33%"><font size="9"> Number of Units </font></th>
                <th  align="LEFT" width="34%"><font size="9"> Net Weight in Kilograms </font></th>
                
				</tr>
				
				
				
				<tr>  
           		<th  align="center"  width="33%"><font size="9"><b> 0306 17 20</b> </font></th>
                <th  align="LEFT" width="33%">  <font size="9"><b>'.$T_O_MCS.' Master Cartons</b></font></th>
                <th  align="center" width="34%"><font size="9"><b>'. $LBS.'<br>'. $KGS.'</b> </font></th>
                
				</tr>
				
				
				
				<tr>
			
           		<th  align="LEFT"  width="100%"><font size="9">
				7.EXPORTER’S DECLARATION (To be completed by a responsible agent of the exporter of the product)	
				<br><br><span>     </span> <span>     </span><span>     </span>I hereby declare that the shipment of shrimp accompanying this declaration (check one):
				<br><span>     </span> <span>     </span>A.[x] Harvested in a manner not harmful to sea turtles. Check the condition of harvest which applies: 
				<br><br><span>     </span> <span>     </span><span>     </span> <span>     </span>1. --X-- Harvested by aquaculture <span>     </span> <span>     </span><span>     </span> <span>     </span> <span>     </span><span>      </span> 2. ----- Harvested using TEDs.
				<br><span>     </span> <span>     </span><span>     </span> <span>     </span>3. ----- Harvested using non-mechanical net <span>     </span> <span> </span> 4. ----- Shrimp harvested in a manner or under  circumstances 
				<br><span>     </span> <span>     </span><span>     </span> <span>     </span><span>     </span><span>     </span>Retrieval or by special gear<span>     </span> <span>     </span><span>     </span> <span>     </span> <span>     </span> <span>     </span><span>     </span> <span>     </span> determined by the Department of State not to pose a 
				<br><span>     </span> <span>     </span><span>     </span> <span>     </span> (see the instructions)                          threat of the incidental taking of sea turtles   
				<br><br><span>     </span> <span>     </span>B.[] Harvested in the waters of a nation currently certified pursuant to Section 609 of P.L. 101-162
</font> </th>
              </tr>
				
				
				<tr>  
           		<th  align="center"  width="33%"><font size="9"> EXPORTER (Name and Title) <br><b>'.$COMPANY_NAME.'<br>'.$COMPANY_ADD.' <br> Ph No. +91 8816 250999
 </b></font></th>
            
                <th  align="center" width="33%"><font size="9">    SIGNATURE <br> For <B>'.$COMPANY_NAME.'</B> <br> <br>  <br> Dr.ANIL PANDIT  <br> AGM-QA </font></th>
                <th  align="center" width="34%"><font size="9"> DATE(MM-DD-YYYY)<br><br> <b>'.$DOS.'</b> </font></th>
                
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="6">8.  GOVERNMENT CERTIFICATION (Necessary only if box 7A above is checked; to be
				signed by a responsible Government official of the harvesting nation).<br>   I hereby declare that the statements 
				signed above by the exporter of this shipment of shrimp are true and accurate to the best of my knowledge. </font></th>

               </tr>
				
				<tr>  
           		<th  align="center"  width="20%"><font size="9">NAME/AGENCY/TITLE <br> <br> <b> The Marine Products Export 
				<br> Development Authority  </b></font></th>
                <th  align="center" width="23%"><font size="8"> ADDRESS/TELEPHONE/FAX <br><br> <b>Sub-Regional Office,<br>Flat No.102,1st Floor,
				<br>Kranti Residency,Behind <br> Bank of Maharashtra,<br> Near Old Bus Stand, <br> 
Juvvalapalem Road, <br>BHIMAVARAM – 534 202<br>Tele Phone:+918816 297219 </b></font></th>

                <th  align="center" width="39%"><font size="9"> SIGNATURE </font></th>
                <th  align="center" width="18%"><font size="9"> DATE (mm-dd-yyyy) </font></th>
                
				</tr>
				
				
				<tr>  
           		<th  align="center"  width="100%"><font size="7"> 9.IMPORT INFORMATION (To be completed by U.S. importer or 
				/customs broker).</font></th>    
				</tr>
				
				
								<tr>  
           		<th  align="LEFT"  width="30%"><font size="7"> DATE OF ENTRY (mm-dd-yyyy) </font></th>
                <th  align="LEFT" width="20%"><font size="7">  PORT OF ENTRY </font></th>
                <th  align="LEFT" width="20%"><font size="7"> ENTRY  NUMBER </font></th>
                <th  align="center" width="30%"><font size="7"> SIGNATURE </font></th>
                
				</tr>
				
				<tr>  
           		<th  align="center"  width="100%"><font size="7"> THIS FORM MUST ACCOMPANY ALL SHIPMENTS OF SHRIMP AND SHRIMP PRODUCTS INTO THE UNITED STATES </font></th>    
				</tr>
				
				<font size="5">Public reporting burden for this collection of information is estimated to average 10 minutes per
				response, including time required for searching existing date sources. Gathering the necessary documentation,
				providing the information and/or documents required, and reviewing the final collection. You do not have 
				supply this information unless this collection displays valid OMB control number. If you have comments on the
				accuracy of this burden estimate and/or
				recommendations for reducing it, please send them to:a/gis/dir, Room 2400 SA-22,  U.S. Department of 
				Sate Washington, D.C. 20522-2202.</font>
				
				</table>'; 
	
	return $content;	
	
}

?>