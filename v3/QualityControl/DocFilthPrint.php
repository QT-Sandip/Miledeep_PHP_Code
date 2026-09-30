<?php
session_start();
error_reporting(E_ALL);
include "../includes/config.php";
include "../includes/Globals.php";
$ID=$_GET['id'];
$INV_ID=$_GET['INV_ID'];
require_once('../tcpdf/tcpdf.php');

 
 include "PrintHeaderInc.php";
 include "Constants.php";
  $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	
	
    
	$pdf->SetCreator(PDF_CREATOR);  

   
$pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(5); 
	$pdf->SetHeaderMargin(50);	
    $pdf->SetMargins(PDF_MARGIN_LEFT, 5, PDF_MARGIN_RIGHT,5);  
    $pdf->setPrintHeader(false);  
    $pdf->setPrintFooter(false);  
	
    $pdf->SetAutoPageBreak(TRUE, 2);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage('L');  
	

			
	
	$table_data=getDS_TABLE3($iconn,$ID,$INV_ID,$CLID);
	$pdf->writeHTML($table_data);
	
	$logo = "images/sme_logo.jpg";
	
  	  $SqlPhElisa="select CLC.TracebilityID,IHREP.InvID ,Tracebility from QcPhAbReports as IHREP
 left Join ScrmPoData as INV on INV.InvNo=IHREP.InvID 
 left Join QcCodeList as CLC on CLC.CLID=IHREP.TracebilityID
 left Join QcTracebilityList as QTL on QTL.TraceID=CLC.TracebilityID
 WHERE IHREP.ID='$ID'"; 
			$StmtPhElisa=$iconn->query($SqlPhElisa);
			$RowPhElisa = $StmtPhElisa->fetch_array();
				$TraceNumber=$RowPhElisa['Tracebility']; 
 


//$file_name='FILTH_REPORT.php'.$row['PoNo'];
$file_name='FlithReport-'.$RowPhElisa['PoNo'].'-'.$TraceNumber.'.pdf';
ob_clean();
$pdf->Output($file_name, 'I');




	function getDS_TABLE3($iconn,$ID,$INV_ID,$CLID){
	//uma	
		 $INV_ID=$_GET['INV_ID']; 
		   $CODE_LIST_ID=$_GET['CLID']; 
		   		
		   $SqlTotalMcs="SELECT SUM(McsTotal) AS TotalMcs FROM `QcCodeList` WHERE InvID='$INV_ID'";   
			$StmtSqlTotalMcs=$iconn->query($SqlTotalMcs);
			$RowTotalMcs = $StmtSqlTotalMcs->fetch_assoc();
			
			$TotalMcs=$RowTotalMcs['TotalMcs'];
			
		    $SqlCodeList="SELECT * FROM `QcCodeList` AS QCL
            LEFT JOIN QcPoVarietyList AS QPV ON QPV.InvID=	QCL.InvID
            LEFT JOIN QcTracebilityList AS QTL ON QTL.TraceID=	QCL.TracebilityID	
			WHERE QCL.InvID='$INV_ID' and CLID='$CODE_LIST_ID'"; 
			$StmtCodeList=$iconn->query($SqlCodeList);
			$RowCodeList = $StmtCodeList->fetch_assoc();
			$FullName=$RowCodeList['FullName'];
			$McsTotal=$RowCodeList['McsTotal'];
			$ShortName=$RowCodeList['ShortName'];
			
			 $TotalSamples =round((sqrt($TotalMcs)+1)/2); 			
			 $TotalSamples=($McsTotal/$TotalMcs)*$TotalSamples;	
			 $TotalSamples =round($TotalSamples);

//uma

	$content='';
	
	$size=40;	
	$size1=60;	
 
           $count='';
				$RESULT='Nill';
				$REMARKS='GOOD';
 
	 $content .= '<table border="1" cellspacing="0" cellpadding="0">  
	
	<tr> 
 
 <th  align="center" width="100%"><font size="20"> SURYAMITRA EXIM PVT LTD - BHIMAVARAM <br>
				REGISTER FOR FILTH TEST </font></th>

             
				</tr>
	
	          <tr> 
           		
                <th  align="center" width="8%"><font size="9"> Date </font></th>

<th  align="center" width="8%"><font size="9"> Code </font></th>
                <th  align="center" width="8%"><font size="9"> Type of Product </font></th>
                <th  align="center" width="8%"><font size="9"> Regd No.  </font></th>
                <th  align="center" width="8%"><font size="9"> No of Cartons </font></th>
                <th  align="center" width="8%"><font size="9"> No of Samples Drawn </font></th>
                <th  align="center" width="7.4%"><font size="9"> Filth Fly </font></th>
                <th  align="center" width="7.4%"><font size="9"> Incidental Fly  </font></th>
                <th  align="center" width="7.4%"><font size="9"> Fly Fragments of any Size </font></th>
                <th  align="center" width="7.4%"><font size="9"> Cockroaches(Whole of including even excreta) </font></th>
                <th  align="center" width="7.4%"><font size="9"> Rat/Mouse </font></th>
                <th  align="center" width="7.4%"><font size="9"> Straited bot not Rat or Mouse </font></th>
                <th  align="center" width="7.6%"><font size="9"> Remarks  </font></th>
               
				</tr>';
				

			$count=1;
			
			
				
				for($i=1; $i<$TotalSamples ;$i++)	{
				//for($i=0; $i<$each_sample ;$i++)	{
					
					
					if($i==1){
					 $content .= '	 <tr>
           		
                <th  align="center" width="8%"><font size="9">'.getDayCodeDate($RowCodeList['DayCode']).'  </font></th>
                <th  align="center" width="8%"><font size="9">'.$RowCodeList['DayCode'].' </font></th>
                <th  align="center" width="8%"><font size="9"> '.$FullName.' </font></th>
                <th  align="center" width="8%"><font size="9"> '.$RowCodeList['TraceNumber'].' </font></th>
                <th  align="center" width="8%"><font size="9">'.$McsTotal.'  </font></th>
                <th  align="center" width="8%"><font size="9"> '.$count.' </font></th>
                <th  align="center" width="7.4%"><font size="9"> '.$RESULT.' </font></th>
                <th  align="center" width="7.4%"><font size="9"> '.$RESULT.' </font></th>
                <th  align="center" width="7.4%"><font size="9"> '.$RESULT.' </font></th>
                <th  align="center" width="7.4%"><font size="9"> '.$RESULT.' </font></th>
                <th  align="center" width="7.4%"><font size="9"> '.$RESULT.' </font></th>
                <th  align="center" width="7.4%"><font size="9">'.$RESULT.'  </font></th>
                <th  align="center" width="7.6%"><font size="9"> '.$REMARKS.' </font></th>
				</tr>';
				$count++;
					}
					
			$content .= '	 <tr>
				 <th  align="center" width="8%"><font size="9">  </font></th>
                <th  align="center" width="8%"><font size="9"> </font></th>
                <th  align="center" width="8%"><font size="9"></font></th>
                <th  align="center" width="8%"><font size="9">  </font></th>
                <th  align="center" width="8%"><font size="9"></font></th>
				<th  align="center" width="8%"><font size="9"> '.$count.' </font></th>
                <th  align="center" width="7.4%"><font size="9"> '.$RESULT.' </font></th>
                <th  align="center" width="7.4%"><font size="9"> '.$RESULT.' </font></th>
                <th  align="center" width="7.4%"><font size="9"> '.$RESULT.' </font></th>
                <th  align="center" width="7.4%"><font size="9"> '.$RESULT.' </font></th>
                <th  align="center" width="7.4%"><font size="9"> '.$RESULT.' </font></th>
                <th  align="center" width="7.4%"><font size="9">'.$RESULT.'  </font></th>
                <th  align="center" width="7.6%"><font size="9">  '.$REMARKS.' </font></th>
               
               
                
				</tr>';
				
				$count++;
				
	}
			
				
				
				
	       $content .= ' <P>Monitered By </P>    
		   
		   
		   <p>Verified By</p>
								
				
				
		</table>'; 
	
	return $content;
}



?>