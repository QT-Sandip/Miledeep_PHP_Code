<style>
table tr td{
	
	    font-size: 17px;
    text-align: left;
}

table tr th{
	
	    font-size: 13px;
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
  
  $Month=$_GET['Month']; 
  $Year=$_GET['Year']; 
  $PsID=$_GET['PsID']; 
  $Type=$_GET['Type']; 
  
 
 
  

if($Type==1){
			$Query= "month(ProductionDate)='$Month'  and year(ProductionDate)='$Year'  ";
		 }else {
			$Query= "ProductionDate between '$Month'  and '$Year'  "; 
		 }
if($PsID!=''){
	  $PsID=$_GET['PsID'];	  
	  $Query.="and Source='$PsID'";
  }


  
 
  $monthName = date("F", mktime(0, 0, 0, $Month, 10));


 if($Type==1){
	  $Header='Cooked Report-('.$monthName.')';

 }
 else{
 $Header='Cooked Report-('.getFormatedDate($Month).' To '.getFormatedDate($Year).')';
	 
 }
 
 
 
 
 $content1 .= ' <h3 align="center">'.$Header.'</h3>';
echo $content1;


	 $table_data=getP_TABLE($iconn,$Month,$Year);

echo $table_data;
	
function getP_TABLE($iconn,$Month,$Year){
	global $Query;
	
 	
/*$SqlBlockData="SELECT  PPD.Mcs,RLD.LotNumber,RLD.PurchaseDate,PPD.TubNo,PPD.ProductionDate,PPD.Variety,PPD.Count,
PPD.Grade,PPD.Brand,PPD.SLAB_WEIGHT,PPD.NetWeight,PackingStyle,PPD.Remarks from RmLotDetatils as RLD 
left join ProdProductionData as PPD on RLD.RmLotID=PPD.LotID 
where  $Query AND Variety LIKE '%C%'order by RLD.PurchaseDate,RLD.LotNumber asc"; */

 $SqlBlockData="SELECT  * from RmLotDetatils as RLD 
left join ProdProductionData as PPD on RLD.RmLotID=PPD.LotID 
where  $Query AND Variety LIKE '%C%'order by RLD.PurchaseDate,RLD.LotNumber asc"; 


			
			$SqlBlockData;
			  
			  $stmt_lots=$iconn->query($SqlBlockData);
			  $Count=mysqli_num_rows($stmt_lots);
			 	  
 
	
 
	$content='';


	$size=40;	 
 
	$size1=60;	



			  	
			  
			  $S_NO=1;
			  $width=120/15;
			  $width_s=$width-5;
			  $width_w=$width+5;
              
			  
			  $content .= '<table width=100% border="1" cellspacing="0" cellpadding="3">
			   <tr>  

           		
<th  align="center"  width="'.$width_s.'%"><B>S No.</B></th>              
				 
				<th  align="center" width="'.$width.'%" ><B> Id No</B></th>
				   <th  align="center"  width="'.$width.'%"><B>RM Date</B></th>			
				   <th  align="center"  width="'.$width.'%"><B>Production Date</B></th>			
				 <th  align="center" width="'.$width.'%" ><B>Item</B></th>
                <th  align="center"  width="'.$width.'%" <B>Count </B></th>
                <th  align="center"  width="'.$width.'%" ><B>Grade </B></th>
                <th  align="center"  width="'.$width.'%" ><B>Brand </B></th>
                <th  align="center"  width="'.$width.'%" ><B>Cartons </B></th>
                <th  align="center"  width="'.$width.'%" ><B>Packing </B></th>
               
                <th  align="center"  width="'.$width.'%" ><B>Net Qty </B></th>
                <th  align="center"  width="'.$width.'%" ><B>Remarks </B></th>
				
               </tr> 
			   
			  '; 
			  
	$TotalWeight=0;

		while($r_lots = $stmt_lots->fetch_array()){
			
		
		$PurchaseDate = date("d-m-Y", strtotime($r_lots['PurchaseDate']));
		$ProductionDate = date("d-m-Y", strtotime($r_lots['ProductionDate']));
		
			
		
				    $content .= 
					
					'<tr>
				<th  align="center" width="'.$width_s.'%">'.$S_NO.'</th>

				 <th  align="center" width="'.$width.'%">'.$r_lots['LotNumber'].'</th>
				 <th  align="center" width="'.$width.'%">'.getFormatedDate($PurchaseDate).'</th>
				 <th  align="center" width="'.$width.'%">'.getFormatedDate($ProductionDate).'</th>
				 <th  align="center" width="'.$width.'%" >'.$r_lots['Variety'].'</th>
				 <th  align="center" width="'.$width.'%" >'.$r_lots['Count'].'</th>
				 <th  align="center" width="'.$width.'%" >'.$r_lots['Grade'].'</th>
				 <th  align="center" width="'.$width.'%" >'.$r_lots['Brand'].'</th>
				 
				 <th  align="center" width="'.$width.'%">'.$r_lots['Mcs'].'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['PackingStyle'].'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['NetWeight'].' Kg</th>
				 
				 <th  align="center" width="'.$width.'%">'.$r_lots['Remarks'].' </th>	 
				 </tr>';

 $S_NO++;
$TotalWeight+= $r_lots['NetWeight'];
}
		  
    $content .= 
					
		 '<tr>			   
		  <th  align="center" width=""></th>	
				 <th  align="center" width="15%">Total Weight</th>				 
               <th  align="center" ></th>				 
               <th  align="center" ></th>				 
               <th  align="center" ></th>				 
               <th  align="center" ></th>				 
               <th  align="center" ></th>				 
               <th  align="center" ></th>				 
               <th  align="center" ></th>				 
               <th  align="center" ></th>				 
			   <th  align="left" >'.$TotalWeight.' Kgs</th>
				               <th  align="center" ></th>	
		 </tr>';

		$content .= '</table>';


	return $content;	

}

?>