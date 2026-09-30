<?php

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include "PrintHeaderInc.php";

 $B_TYPE=$_GET['TYPE'];
 $BAR_CODE_ID=$_GET['BID'];
 $LOT_ID=$_GET['ID'];

$from_date=$_GET['from_date']; 
  $to_date=$_GET['to_date']; 
$Exporter=$_GET['Exporter']; 
   

$file_name='DayCode Report -'.$from_date.'-'.$to_date.'.xlsx';

		$date_end=$to_date.' 23:59:59';  
	
	
		
	  $SqlPo="SELECT * FROM `ScrmPoData` SPD
	 LEFT JOIN ScrmConsigneeDetails SCD ON SCD.ConID=SPD.ConsigneeID
	 LEFT JOIN ScrmCountriesList SCL ON SCL.id=SPD.DestinationCountry
	 WHERE InvDate BETWEEN '$from_date'
	 and '$to_date' ORDER BY `InvNo` ASC; ";
			  $StmtPo=$iconn->query($SqlPo);
	

	
			 
			  

	
 
	$content='';


	$size=40;	 
 
	$size1=60;	



			  	
			  
			 
			  $width=11;
			  $width_s=8;
			  $width_w=$width+5;
              
			    $content .= '
				<h3 align="center">'.$ExporterName.'</h3>
			  <h3 align="center"> DayCode Report ( '.date('d-M-Y',strtotime($from_date)).' to  '.date('d-M-Y',strtotime($to_date)).' )</h3>
			  
			  
			  <table border="1" cellspacing="0" cellpadding="5">
			   <tr>  

           		

              <th  align="center"  width="3%"><B>S No.</B></th>           
				 
				<th  align="center" width="'.$width_s.'%" ><B>Day Code</B></th>
				<th  align="center"  width="'.$width.'%"><B>Lot No</B></th>	
				   <th  align="center"  width="'.$width_s.'%"><B>Traceability</B></th>					   
				   <th  align="center"  width="'.$width.'%"><B>Received Qty</B></th>
				   <th  align="center"  width="'.$width_s.'%"><B>Finished Qty</B></th>					   
			
				   <th  align="center"  width="'.$width.'%"><B>Sample Id/Code</B></th>
                <th  align="center"  width="'.$width.'%" <B>Name Of The Representation</B></th>
				   
				   <th  align="center"  width="'.$width.'%"><B>Date & Time Of Harvest</B></th>			
                <th  align="center"  width="'.$width.'%" <B>TPC Cfu/ gm @37°C</B></th>
                
                
    
               </tr> 
			   
			  '; 
			  
	
		//	
$TotalFOBUS=0;
$TotalFOB=0;
$GrandTotalDiscount=0;
$TotalNetWgtKgs=0;
$TotalNetWgtLbs=0;
$S_NO=1;
		while($RowPo = $StmtPo->fetch_array()){
			 
			$POID=$RowPo['PoID'];
			
			
			 $SqlPreHarvestReport="SELECT  DayCode,LabRep,TraceNumber,Tpc,ReceivedQty,FinishedWeight,
            ReportNo,HarvestingDate FROM `QcPhAbReports` as IHER
			left join QcCodeList as CLC on CLC.CLID=IHER.TracebilityID
            left join QcTracebilityList as QTL on QTL.TraceID=CLC.TracebilityID        
			left join QcMBReport as BMR on BMR.InvID=IHER.InvID
			left join QcMBResult as BMRE on BMRE.MbReportID=BMR.ID
			WHERE IHER.InvID='$POID'"; 

			
			$StmtPreHarvestReport=$iconn->query($SqlPreHarvestReport);
					while($RowPreHarvestReport=$StmtPreHarvestReport->fetch_array()){
 
			
			$content .= 
					
					'<tr>
				<th  align="center" width="3%">'.$S_NO.'</th>
				<th  align="center" width="3%">'.$RowPreHarvestReport['DayCode'].'</th>

				 <th  align="center" width="'.$width_s.'%">'.$RowPo['LotNo'].'</th>
				  <th  align="center" width="'.$width.'%">'.$RowPreHarvestReport['TraceNumber'].'</th>

				 <th  align="center" width="'.$width_s.'%">'.$RowPreHarvestReport['ReceivedQty'].'</th>
				 <th  align="center" width="'.$width.'%">'.$RowPreHarvestReport['FinishedWeight'].'</th>
				
				 <th  align="center" width="'.$width.'%" >'.$RowPreHarvestReport['ReportNo'].'</th>
				 <th  align="center" width="'.$width.'%" >'.$RowPreHarvestReport['LabRep'].'</th>
				 <th  align="center" width="'.$width.'%" > 
				 '.getFormatedDateTimeEmpty($RowPreHarvestReport['HarvestingDate']).'</th>
				 <th  align="center" width="'.$width.'%" >'.$RowPreHarvestReport['Tpc'].'</th>
				  
				 </tr>';
$S_NO++;
 			 
}
}

		/*  $content .= 
					
		 '<tr>             <th  align="center" width="15%">Total</th>				 
               <th  align="center" ></th>				 
              		 
               <th  align="center" >'.number_format($GrandTotalDiscount,2).'</th>				 
               <th  align="center" ></th>				 
               				 
			   <th  align="center" > </th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			  
 
		 </tr>';*/

		$content .= '</table>';


	echo $content;	


?>