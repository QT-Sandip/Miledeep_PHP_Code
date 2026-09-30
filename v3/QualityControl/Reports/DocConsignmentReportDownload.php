<?php

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
 include ($cp."Includes/IncludePage.php");
include "PrintHeaderInc.php";

$from_date=$_GET['from_date']; 
  $to_date=$_GET['to_date']; 

$file_name='Consignment Report -'.$from_date.'-'.$to_date.'.xlsx';

		$date_end=$to_date.' 23:59:59';  
	
	
		
	
	 /*$SqlPo="SELECT * FROM `ScrmPoData` SPD
	 LEFT JOIN ScrmConsigneeDetails SCD ON SCD.ConID=SPD.ConsigneeID
	 LEFT JOIN ScrmCountriesList SCL ON SCL.name=SPD.DestinationCountry
	 LEFT JOIN QcPayments QPS ON QPS.InvID=SPD.PoID
	 LEFT JOIN QcCoveringLetter QCL ON QCL.InvID=SPD.PoID
	 LEFT JOIN QcCodeList QCDL ON QCDL.InvID=SPD.PoID 
	 WHERE InvDate BETWEEN '$from_date'
	 and '$to_date' ORDER BY `InvNo` ASC; "; */
	 
	 $SqlPo="SELECT *,(SELECT distinct Brand FROM `QcCodeList` WHERE InvID=SPD.PoID) as Brand
	 FROM `ScrmPoData` SPD
	 LEFT JOIN ScrmConsigneeDetails SCD ON SCD.ConID=SPD.ConsigneeID
	 LEFT JOIN ScrmCountriesList SCL ON SCL.id=SPD.DestinationCountry
	 LEFT JOIN QcPayments QPS ON QPS.InvID=SPD.PoID
	 LEFT JOIN QcCoveringLetter QCL ON QCL.InvID=SPD.PoID
	 	 WHERE InvDate BETWEEN '$from_date'
	 and '$to_date' ORDER BY `InvNo` ASC ";
	$StmtPo=$iconn->query($SqlPo);

	
 
	$content='';


	$size=40;	 
 
	$size1=60;	



			  	
			  
			  $S_NO=0;
			  $width=11;
			//$width_s=9;
			  //$width_w=$width+5;
              
			    $content .= '
				
			  <h3 align="center"> Consignment Report ( '.date('d-M-Y',strtotime($from_date)).' to  '.date('d-M-Y',strtotime($to_date)).' )</h3>
			  
			  
			  <table border="1" cellspacing="0" cellpadding="4">
			   <tr>  

           		

                <th  align="center"  width="3%">S No.</th>              
				 
					<th  align="center" width="'.$width_s.'%" >Lot No</th>
					<th  align="center" width="'.$width_s.'%" >Buyer Name</th>
					<th  align="center" width="'.$width_s.'%" >Brand</th>
					<th  align="center"  width="'.$width_s.'%"><B> Invoice No</B></th>					   
					<th  align="center"  width="'.$width_s.'%"><B>Invoice_Date</B></th>					   
					<th  align="center"  width="'.$width_s.'%"><B>Po No</B></th>					   
					<th  align="center"  width="'.$width_s.'%"><B>Country</B></th>
					<th  align="center"  width="'.$width_s.'%" ><B>FOB Value</B></th>
					<th  align="center"  width="'.$width_s.'%"><B>CFE Number</B></th>	
					<th  align="center"  width="'.$width_s.'%"><B>Variety</B></th>						
					<th  align="center"  width="'.$width_s.'%"><B>Traceability</B></th>			
					<th  align="center"  width="'.$width_s.'%"><B>Day code</B></th>			
					<th  align="center"  width="'.$width_s.'%"><B>Packing Style</B></th>			
					<th  align="center"  width="'.$width_s.'%"><B>Grade</B></th>			
					<th  align="center"  width="'.$width_s.'%"><B>Mcs</B></th>			
					<th  align="center"  width="'.$width_s.'%"><B>Total Mcs</B></th>		

                
    
               </tr> 
			   
			  '; 
			  


		while($RowPo = $StmtPo->fetch_array()){
			$LotNo=	$RowPo['LotNo'];	
						
			
			$content .= 
			
					'<tr>
				 <td  align="center" width="3%">'.++$S_NO.'</td>

				 <td  align="center" width="'.$width_s.'%">'.$LotNo.'</td>
				 <td  align="center" width="'.$width_s.'%">'.$RowPo['ConsigneeName'].'</td>
				 <td  align="center" width="'.$width_s.'%">'.$RowPo['Brand'].'</td>
				 <td  align="center" width="'.$width_s.'%">'.$RowPo['InvNo'].'</td>
				 <td  align="left" width="'.$width_s.'%">'.date('d-m-Y',strtotime($RowPo['InvDate'])).'</td>
				 <!--<td  align="center" width="'.$width_s.'%"></td>-->
				 <td  align="center" width="'.$width_s.'%">'.$RowPo['PoNo'].'</td>
				 <td  align="center" width="'.$width_s.'%">'.$RowPo['name'].'</td>
				 <td  align="center" width="'.$width_s.'%">'.$RowPo['FobValue'].'</td>
				 <td  align="center" width="'.$width_s.'%">'.$RowPo['QCertNo'].'</td>
				 
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" >	</td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				  
				 </tr>';
				 
				 
				 

				
				
				$PoID=$RowPo['PoID']; 

  

			
			
			 $SqlVariety="SELECT *,PVL.ShortName as Variety  FROM `QcCodeList` as Cl 
			 Left JOIN QcPoVarietyList as PVL On PVL.VarietyID=Cl.VarietyID 
			 Left JOIN QcTracebilityList as QTL On QTL.TraceID=Cl.TracebilityID 
			 Left JOIN AdminPackingStyles as APS On APS.PCKID=Cl.PackingStyleID 
			 WHERE Cl.InvID='$PoID' ORDER BY `CLID` DESC";
			$Stmtvariety=$iconn->query($SqlVariety);
			while($RowVariety=$Stmtvariety->fetch_array()){
			
			
		    $content .= 
					
					'<tr>
				 <td  align="center" width="3%"></td>

				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="left" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				
				 <td  align="center" width="'.$width_s.'%" >'.$RowVariety['Variety'].'</td>
				 <td  align="center" width="'.$width_s.'%" >'.$RowVariety['Tracebility'].'</td>
				 <td  align="center" width="'.$width_s.'%" >'.$RowVariety['DayCode'].'</td>
				 <td  align="center" width="'.$width_s.'%" >'.$RowVariety['PackingStyle'].'</td>
				 <td  align="center" width="'.$width_s.'%" >'.$RowVariety['Grades'].'</td>
				 <td  align="center" width="'.$width_s.'%" >'.$RowVariety['McsGrade'].'</td>
				 <td  align="center" width="'.$width_s.'%" >'.$RowVariety['McsTotal'].'</td> 
				
				  
				 </tr>';
			}

			 
}


		$content .= '</table>';

$filename='ConsignmentReport -'.$from_date.'-'.$to_date.'.xls';

		header("Content-Type: application/xls");
			header("Content-Disposition: attachment; filename=".$filename."");

	echo $content;	


?>