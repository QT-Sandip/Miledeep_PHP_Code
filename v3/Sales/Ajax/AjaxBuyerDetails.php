<style>
th {
    font-size: 15px;
}


td {
    font-size: 15px;

}

#myModal .modal-fullscreen .modal-body div#key-datatable_wrapper  {
    overflow: auto;
    height: 80vh;
}
</style>
<?php 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");


 $FinClsYear=$_POST['Year'];
$ExpID=trim($_POST['ExpID']);
$ConsigneeID=trim($_POST['ConsigneeID']);
$CountryID=trim($_POST['CountryID']);
 $Month=trim($_POST['Month']); 
 $Brand=trim($_POST['Brand']); 



if($Month!=''){
	$Month=sprintf('%02d',$Month);
	if($Month>3){
	$DateStart=$FinClsYear.'-'.$Month.'-01';
	$DateEnd=($FinClsYear).'-'.$Month.'-31';
	}else{
		$DateStart=($FinClsYear+1).'-'.$Month.'-01';
		$DateEnd=($FinClsYear+1).'-'.$Month.'-31';
	}
	$DateFilter="BETWEEN '$DateStart' and '$DateEnd'";
}else{
$DateStart=$FinClsYear.'-04-01';
$DateEnd=($FinClsYear+1).'-03-31';
$DateFilter="BETWEEN '$DateStart' and '$DateEnd'";
}


function getFilterQry(){
	global $iconn,$FinClsYear,$ExpID,$ConsigneeID,$CountryID,$Brand;
		$Qry="";
		 
		if($ExpID!=''){
			$Qry.=" and SPD.ExporterID='$ExpID'";
		}
		if($ConsigneeID!=''){
			$Qry.=" and SPD.ConsigneeID='$ConsigneeID'";
		}
		if($CountryID!=''){
			$Qry.=" and SPD.DestinationCountry='$CountryID'";
		}
		if($Brand!=''){
			$Qry.=" and PGD.Brand='$Brand'";
		}
	
return $Qry;
	
}

$Qry=getFilterQry();
	
// 	   $SqlLate="SELECT VL.ProductDescription,VL.Species,VL.FreezingType,VL.PreparationType,PoNo,PoID,PoDate,ShortName as Variety,
// 	   PGD.Grade,Mcs,Brand,APS.PackingStyle,Price,
// sum(Mcs*TotalSlabs*SlabWeight*LbsFactor ) as Lbs,
// sum(Mcs*TotalSlabs*SlabWeight*Price*PriceFactor ) as TotalPrice
// FROM `ScrmPoData` as SPD
// LEFT JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
// LEFT JOIN ScrmPoGradeData as PGD on PGD.InvId=SPD.PoID 
// LEFT JOIN ScrmPIVarietyList as VL on PGD.VarietyID=VL.VarietyID
// LEFT JOIN AdminPackingStyles as APS on APS.PCKID=PGD.PackingStyleID 
// WHERE SPD.PoDate $DateFilter  $Qry GROUP BY PGD.ID
// ORDER by PoID ,Abs(PGD.Grade) ASC";

$SqlLate="SELECT VL.ProductDescription,VL.Species,VL.FreezingType,VL.PreparationType,PoNo,PoID,PoDate,ShortName as Variety,QtyUnit,
	   PGD.Grade,Mcs,Brand,APS.PackingStyle,Price,
sum(Mcs*TotalSlabs*SlabWeight*LbsFactor ) as Lbs,
sum(Mcs*TotalSlabs*SlabWeight*Mfactor ) as Kgs,
sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs
FROM `ScrmPoData` as SPD
LEFT JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
LEFT JOIN ScrmPoGradeData as PGD on PGD.InvId=SPD.PoID 
LEFT JOIN ScrmPIVarietyList as VL on PGD.VarietyID=VL.VarietyID
LEFT JOIN AdminPackingStyles as APS on APS.PCKID=PGD.PackingStyleID 
WHERE SPD.PoDate $DateFilter  $Qry GROUP BY PGD.ID
ORDER by PoID ,Abs(PGD.Grade) ASC";
$StmtLate=$iconn->query($SqlLate);
	
	 $data .='<table  id="key-datatable" data-paging="false" 
class="table dt-responsive wrap w-100" >										
		
<thead class="thead-light">
                   
                   
                                                        
													<!--<th class="sticky sno">Sno </th>-->
                                                        <th class="sticky sno">Po No</th>
                                                        <th>Po Date</th>
                                                        <th>Variety</th>
                                                        <th>Brand</th>
                                                        <th>Grade</th>
                                                        <th>Mcs</th>
                                                        <th>Price($)</th>
														<th>Qty</th>
														<th>Unit</th>
														<th>Value($)</th>
                                                        
                                                    
                                                </thead>
                                                <tbody>';
												
 while($LateRow=$StmtLate->fetch_array()){ 
	
	$Variety=$LateRow['Species'] .'-'.$LateRow['Variety'].'-'.$LateRow['PreparationType'].'-'.$LateRow['FreezingType'];
	$Variety=$LateRow['ProductDescription']? $LateRow['ProductDescription'] :$Variety;
	$QtyUnit=$LateRow['QtyUnit'];

		if($QtyUnit=='KGS'){	
		$TotalPrice=$LateRow['PriceKgs'];
		$Qty=$LateRow['Kgs'];
		}else{		
		$TotalPrice=$LateRow['PriceLbs'];
		$Qty=$LateRow['Lbs'];
		}

		//$TotalPrice=$LateRow['TotalPrice'];
         $data.='<tr>
		 
		
                                                        
		<!-- <td class="sticky sno">'.  ++$Sno .'</a></td> -->
		 <td class="sticky sno">'.  $LateRow['PoNo'] .'</a></td>
                                                        <td>'. getFormatedDate($LateRow['PoDate']) .'</a></td>
                                                        <td>'.  $Variety .'</td>
                                                        <td>'.  $LateRow['Brand'] .'</td>
                                                        <td>'.  $LateRow['Grade'] .'</td>
                                                        <td>'.  $LateRow['Mcs'] .'</td>
														<td>'.  $LateRow['Price'] .'</td>
														<td>'.  round($Qty,2) .'</td>
														<td>'.  $LateRow['QtyUnit'] .'</td>
														<td>'.  number_format($TotalPrice,2).'</td>
														<!--<td>'.  round($LateRow['TotalPrice'],2).'</td>-->
                                                    
                                                        
                                                    </tr>';
													
 }
                                               

	

 		echo $data .='</tbody></table>';
	
	
?>


<script>
function showData(data,sort){
			
		var IsSort=true;
			if(sort==false){
				IsSort=false;
			}else{
				IsSort=true;
			}
		$("#ShowDataTable").html(data);	

		var table;

		/* Destroy the Datatable if initialize */
        if ($.fn.dataTable.isDataTable('#myTable')) {
            table.destroy();               
        }

		  table = $('#key-datatable').DataTable({			 
			// scrollY: "380px",
			// scroller: true,
			// scrollX:true,
			// sScrollXInner: "150%", 
			// paging:true,
			// destroy: true,
			// fixedColumns: true,
			fixedHeader: true,
			bSort : IsSort,
			"bStateSave": true,
			dom: 'Blfrtip',
			buttons: [{extend: 'excel', text: 'Download Excel', className: 'btnExcelDataTable',title: 'Orders List', footer: true,
				exportOptions: {
					columns: ':visible'
				}
			}],

			"fnStateSave": function (oSettings, oData) {
				localStorage.setItem('key-datatable', JSON.stringify(oData));
			},
			"fnStateLoad": function (oSettings) {
				return JSON.parse(localStorage.getItem('key-datatable'));
			}
		});
			

}


$(document).ready(function(){
	  showData();
	});  
</script>