<style>
table tr td{
	
	    font-size: 13px;
    text-align: left;
}

table tr th{
	
	    font-size: 15px;
    text-align: left;
	 background: white;
  position: sticky;
  top: 0;
}


</style>
<?php

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");


$QueryParms=$_GET['Q']; 

 if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }


$Year=$_GET['Year'];
  $Month=$_GET['Month'];
  $StationID=$_GET['PsID'];
  $Type=$_GET['Type'];



 $monthName = date("F", mktime(0, 0, 0, $Month, 10));

  if($Type==1){
	  $Header='Cooked Report-('.$monthName.')';

 }
 else{
 $Header='Cooked Report-('.getFormatedDate($Month).' To '.getFormatedDate($Year).')';
	 
 }

if($Type==1){
			$Query= "month(ProductionDate)='$Month'  and year(ProductionDate)='$Year' ";
		 }else {
			$Query= "ProductionDate between '$Month'  and '$Year'"; 
		 }
		 
   $content1 .= '<h3 align="center">'.$Header.'</h3>';
echo $content1;


 
$file_name='COOKED PACKING -'.$monthName.'.pdf';


	/*$sql_lots="SELECT * from RmLotDetatils as imd 
	left join ProdProductionData as SCK on imd.RmLotID =SCK.LotID 
	where Year(ProductionDate)=$Year and Month(ProductionDate)=$Month
	 AND Variety LIKE '%C%' order by PurchaseDate ,LotNumber asc"; */
	
	$sql_lots="SELECT * from RmLotDetatils as imd 
	left join ProdProductionData as SCK on imd.RmLotID =SCK.LotID 
	where $Query AND Variety LIKE '%C%' order by PurchaseDate ,LotNumber asc"; 
			  
			   $sql_lots;
			  
			  $stmt_lots=$iconn->query($sql_lots);
			 	  
 
	
 
	$content='';


	$size=40;	 
 
	$size1=60;	



			  	
			  
			  $S_NO=1;
			  $width=11;
			  $width_s=10;
			  $width_w=$width+5;
              
			  
			  $content .= '
			 <!-- <h3 align="center"> Cooked Report-'.$monthName.'</h3>-->
			  
			  
			  <table width=100% border="1" cellspacing="0" cellpadding="5">
	<tr>             		
                <th  align="center"><B>S No.	</B></th>        				
				<th  align="center"><B>Id No	</B></th>
				<th  align="center"><B>RM Date	</B></th>			
				<th  align="center"><B>IQF Date	</B></th>			
				<th  align="center"><B>Item		</B></th>
				<th  align="center"><B>Count 	</B></th>
                <th  align="center"><B>Grade 	</B></th>
				<th  align="center"><B>Brand 	</B></th>
                <th  align="center"><B>Cartons 	</B></th>
                <th  align="center"><B>Packing 	</B></th>               
                <th  align="center"><B>Net Qty 	</B></th>
                <th  align="center"><B>Remarks 	</B></th>				   
    </tr>  '; 

		//	
$TWeight=0;		
		while($r_lots = $stmt_lots->fetch_array()){
			$PurchaseDate = date("d-m-Y", strtotime($r_lots['PurchaseDate']));
		$IqfDate = date("d-m-Y", strtotime($r_lots['ProductionDate']));
		
				    $content .= 
					
	'<tr>
				 <td  align="center" >'.$S_NO.'</td>
				 <td  align="center" >'.$r_lots['LotNumber'].'</td>
				 <td  align="center" >'.$PurchaseDate.'</td>
				 <td  align="center" >'.$IqfDate.'</td>
				 <td  align="center" >'.$r_lots['Variety'].'</td>
				 <td  align="center" >'.$r_lots['Count'].'</td>
				 <td  align="center" >'.$r_lots['Grade'].'</td>
				 <td  align="center" >'.$r_lots['Brand'].'</td>				 
				 <td  align="center" >'.$r_lots['Mcs'].'</td>
				 <td  align="center" >'.$r_lots['PackingStyle'].'</td>
				 <td  align="center" >'.$r_lots['NetWeight'].' Kg</td>				 
				 <td  align="center" >'.$r_lots['Remarks'].' </td>				 
	 </tr>';

$TWeight +=$r_lots['NetWeight'];
 $S_NO++;			 
}
$content .= 					
		 '<tr>               
		 <td  align="center" width=""></td>				 
         <td  align="center" width="15%">Total Weight</td>				 
		 <td  align="center" ></td>				 
		 <td  align="center" ></td>				 
		 <td  align="center" ></td>				 
		 <td  align="center" ></td>				 
		 <td  align="center" ></td>				 
		 <td  align="center" ></td>				 
		 <td  align="center" ></td>				 
		 <td  align="center" ></td>				 
		 <td  align="left" 	 >'.$TWeight.' Kgs</td>
		 <td  align="center" ></td>				  
</tr>';

		$content .= '</table>';


	echo $content;	

//}

?>