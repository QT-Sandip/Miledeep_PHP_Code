<?php
$PageTitle="Shipment Report";
$PageTitleFolder="ColdStore";

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
 
  $SqlPos="select * from ScrmPoData as SPD 
  left join  ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
  left join  ScrmPortCities as SPC on SPC.PortId=SPD.PortOfDischarge 
  left join ScrmShipmentDetailsBreif as SDB on SPD.PoID=SDB.PiID
  where TransportedDate!=''  and (ClosedDate=''  or ClosedDate is null)  order by PoID desc ";
  //where TransportedDate!=''  and (ClosedDate=''  or ClosedDate is null)  order by ShippedOutDate desc limit 100";
 $StmtPos=$iconn->query($SqlPos);

 ?>


<!doctype html>
<html lang="en">

    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>

        <!-- DataTables -->
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-select-bs4/css//select.bootstrap4.min.css" rel="stylesheet" type="text/css" />

        <!-- Responsive datatable examples -->
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />     


		<link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css" rel="stylesheet">
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet" />
        <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">
	
	 <?php include ($IncludesPath."PreLoader.php"); ?>
	


        <!-- Begin page -->
        <div id="layout-wrapper">

            <?php
			include ($IncludesPath."topbar.php");
			include ($IncludesPath."sidebar.php"); 
			?>

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <?php include ($IncludesPath."page-title.php");?>
						

   <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body" id="EmpList">
                                 
 <table  id="key-datatable" data-paging="false" class="table dt-responsive wrap w-100" >
    <thead  class="thead-light">
                
		<tr>	  
        <th>S.No</th>      
        <th>Buyer</th>	
		<th>Po No</th>
		<th>Inv No</th>
		<th>Po Date</th>
		<th>Port</th>
		<th>Brand</th>
		<th>Shipment Date</th>
		<th>Actual Shipment Date</th>
		<th>Variety</th>
        <th>Packing</th>
		<th>Grade</th>
        <th>Ordered Mcs</th>
        		
		<th>Ordered Kgs</th>
		<th>Price per Unit</th>
		<th>Total</th>
        

		</tr>               
</thead>
    
	
			

    <tbody>

	<?php 
				
				
							
							$PoCount=1;
							
		//$tot_Price=0;
		// 
	
				while($RowPos=$StmtPos->fetch_array()){
			   
			    $SNO=$PoCount;
				$InvId=$RowPos['PoID'];
			    $PoNum=$RowPos['PoNo'];
			    $InvNum=$RowPos['InvNo'];
			    $PoDate=date('d-m-Y',strtotime($RowPos['PoDate']));
			    $Port=$RowPos['Name'];
			    //$Brand=$RowPos['Brand'];
			    $ConsigneeName=$RowPos['ConsigneeName'];
				$ShipmentDate=date('d-m-Y',strtotime($RowPos['ShipByDate']));
				$ActShipmentDate=date('d-m-Y',strtotime($RowPos['ShippedOutDate']));
				
		if($ShipmentDate=='01-01-1970'){
	$ShipmentDate='*****';
}

if($ActShipmentDate=='01-01-1970'){
	$ActShipmentDate='*****';
}		
							
 $SqlPoBreakUp="SELECT CPID.Grade, Mcs as RequiredCases ,ShortName,Brand,PackingStyle,Price as Usd, round((TotalSlabs*SlabWeight*Mcs*Price),2) as PriceLBS,

(SELECT COALESCE(sum(AvalMcs*TotalSlabs*SlabWeight*MFactor),0) FROM CsStockData as AvalCPID 
 left join AdminPackingStyles as AvalCps on AvalCPID.PackingStyleID=AvalCps.PCKID where AvalCPID.Grade=PGD.Grade 
 and AvalCPID.VarietyID=PGD.VarietyID and AvalCPID.PoGradeId=0 ) as StoreAvalKgs,

(SELECT COALESCE(sum(LooseBags*SlabWeight*MFactor),0) FROM CsStockData as AvalCPID 
 where AvalCPID.Grade=PGD.Grade and AvalCPID.VarietyID=PGD.VarietyID and AvalCPID.PoGradeId=0 ) as StoreAvalLKgs, 

(SELECT COALESCE(sum(Mcs*TotalSlabs*SlabWeight*MFactor),0) FROM ScrmPoGradeData as SPGD where SPGD.ID=PGD.ID ) as OrderKgs, 
(SELECT COALESCE(sum(AvalMcs),0) FROM CsStockData  as AvalCPID where AvalCPID.PoGradeId=PGD.ID and PackingTypeID='DUMMY' ) as DummyMcs,

(SELECT COALESCE(sum(AvalMcs*TotalSlabs*SlabWeight*MFactor),0) FROM CsStockData as AvalCPID where AvalCPID.PoGradeId=PGD.ID and PackingTypeID='DUMMY' ) as DummyKgs, 

(SELECT COALESCE(sum(AvalMcs),0) FROM CsStockData where CPID.PoGradeId=PGD.ID and PackingTypeID='MASTER' ) as FinishedMcs, 

(SELECT COALESCE(sum(AvalMcs*TotalSlabs*SlabWeight*MFactor),0) FROM CsStockData 
where CPID.PoGradeId=PGD.ID and PackingTypeID='MASTER' ) as FinishedKgs 
FROM `ScrmPoGradeData` as PGD LEFT JOIN AdminPackingStyles as CPS on CPS.PCKID= PGD.PackingStyleID 
LEFT JOIN CsStockData as CPID on CPID.PoGradeId=PGD.ID 
left join ScrmPIVarietyList as AGO on CPID.VarietyID=AGO.VarietyID where CPID.PoID='$InvId' GROUP by PGD.ID;";

 
	//	echo $SqlPoBreakUp; 	
				$StmtPoBreakUp=$iconn->query($SqlPoBreakUp);	 
				 $IsGradeAval=$StmtPoBreakUp->num_rows;
				
				if($IsGradeAval>0){
			  
			   }else{
				   continue;
			   }
				
				$tot_Price=
					$PoTotalOrderdMcs= 
					$PoTotalFinishedMcs= 
					$PoTotalDummyMcs= 
					$PoTotalReqMcs= 
					$PoTotalOrderKgs=
					$PoTotalFinishedKgs=
					$PoTotalDummyKgs=
					$PoTotalBalKgs=0;
				
				while($RowPoBreakUp=$StmtPoBreakUp->fetch_array()){ 

				 $Price=$RowPoBreakUp['PriceLBS']; 
	
				$tot_Price+=$Price;
	
				  $Variety=$RowPoBreakUp['ShortName'];
				  $Usd=$RowPoBreakUp['Usd'];
				  
				  $PackingStyle=$RowPoBreakUp['PackingStyle'];
				  $Brand=$RowPoBreakUp['Brand'];
				  $Grade=$RowPoBreakUp['Grade'];
				  
				  $OrderedMcs=$RowPoBreakUp['RequiredCases'];
				  $FinishedMcs=$RowPoBreakUp['FinishedMcs'];
				  $DummyMcs=$RowPoBreakUp['DummyMcs'];
				  $ReqMcs=$OrderedMcs-$FinishedMcs;
				  
				    $StoreAvalKgs=$RowPoBreakUp['StoreAvalKgs']+$RowPoBreakUp['StoreAvalLKgs'];
				    $OrderKgs=$RowPoBreakUp['OrderKgs'];
				    $FinishedKgs=$RowPoBreakUp['FinishedKgs'];
				    $DummyKgs=$RowPoBreakUp['DummyKgs'];
				    $BalKgs=$OrderKgs-($FinishedKgs+$DummyKgs);
					
					if($FinishedMcs > $OrderedMcs){
					$Style='font-weight:bold;font-size:11px;color:#0bb50b';	
					}else{
					$Style='font-weight:bold;font-size:11px;color:black';		
					}
					
					$StyleTotal='font-weight:bold;font-size:11px;color:red;background: #d2d6de;';
					
					
					/*po Total*/
					$PoTotalOrderdMcs+= $OrderedMcs;
					$PoTotalFinishedMcs+= $FinishedMcs;
					$PoTotalReqMcs+= $ReqMcs;
					
					$PoTotalOrderKgs+= $OrderKgs;
					$PoTotalFinishedKgs+= $FinishedKgs;
					$PoTotalDummyKgs+= $DummyKgs;
					$PoTotalBalKgs+= $BalKgs;
					
					
					
					
					/*po Total*/
				
				?>
				
				 
				
				
				<tr style="<?php echo $Style ?>">
				<td ><?php echo $SNO;  $SNO=''?></td>
				<td ><?php echo $ConsigneeName ?></td>
				<td ><?php echo $PoNum;?></td>
				<td ><?php echo $InvNum;?></td>
				<td ><?php echo $PoDate;?></td>
				<td ><?php echo $Port;?></td>
				<td ><?php echo $Brand;?></td>
				<td ><?php echo $ShipmentDate;?></td>
				<td ><?php echo $ActShipmentDate;?></td>
				<td ><?php echo $Variety ;?></td>
				<td ><?php echo $PackingStyle; ?></td>
				<td ><?php echo $Grade; ?></td>
				<td><?php echo $OrderedMcs; ?></td>
				<td><?php echo  round($OrderKgs,2); ?></td>
				<td><?php echo  $Usd; ?></td>
				<td><?php echo  round($Price,2); ?></td>
				
				</tr>
				
				<?php  } 
				$PoCount++;
				?>
				
				
				<tr style="<?php echo $StyleTotal ?>">
				<td ></td>
				<td >Total</td>
				<td ></td>
				<td ></td>
				<td ></td>
				<td ></td>
				<td ></td>
				<td ></td>
				<td ></td>
				<td ></td>
				<td ></td>
				<td ></td>
				<td><?php echo $PoTotalOrderdMcs; ?></td>
				<td><?php echo  round($PoTotalOrderKgs,2); ?></td>
				<td></td>
				<td><?php echo  round($tot_Price,2); ?></td>
				
				</tr>
				
				
				
				
			<?php	}?>
	 

     </tbody>

  </table>
</div>
</div>
</div>
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  </div>
                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
      

        <!-- Required datatable js -->
		 <script src="<?php echo $BaseDist ?>assets/libs/select2/js/select2.min.js"></script>
		  
        <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
		 <script src="<?php echo $BaseDist ?>assets/js/pages/VarietyID-advanced.init.js"></script>

		 
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        <!-- Buttons examples -->
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/jszip/jszip.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/pdfmake/build/pdfmake.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/pdfmake/build/vfs_fonts.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-select/js/dataTables.select.min.js"></script>
        
        <!-- Responsive examples -->
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

        <!-- Datatable init js -->
        <script src="<?php echo $BaseDist ?>assets/js/pages/datatables.init.js"></script>

        <script src="<?php echo $BaseDist ?>assets/js/app.js"></script>
</body>



<Script>

$('document').ready(function(){
	

	showData(data);
	// $.stickysidebarscroll("#tests",{offset: {top: 10, bottom: 200}});
    
    	$(document).on('click','#payBtn',function(e){
			
			//alert("test");
    	e.preventDefault();
			 $('.trremove').remove();
                         
			var refid=$(this).val();
			
						var PO_ID = document.getElementById('PO_ID'); //or grab it by tagname etc
						PO_ID.value = ""+refid;  

					
					//	alert(refid);

						var large2='TEST'                  
                      						
						$('.modheading').text('Update Status');

						$('#myModal').modal('toggle');						

		 
			
			//return false;
			
    		
    
    	});
    
    	$('#elisa').on('click',function(){
    alert("test");
    		$('VarietyID').submit();
    	});
    });  

//ROBO2		
$(document).on('click','#BtnViewPoStatus',function(e){			
			e.preventDefault();
		//	alert("INT_LOT_NUM Required");

			var INT_LOT_NUM=$(this).val();
			//alert(INT_LOT_NUM);
		
						var UNIT=$('#JME_MOURYA').val();
			if(INT_LOT_NUM ==''){
				$("#INT_LOT_NUM").focus();
				alert("PO Number Required");
				return;
			}else{
				
				

		$.ajax({
		   url:'/ColdStore/Ajax/AjPopUpPoStatus.php',
		  type:'POST',
		  data:{'INT_LOT_NUM':INT_LOT_NUM , 'TYPE':'KK','UNIT':UNIT},
		  success:function(data){
			 
			 $('.modal-title').text('PO Status');
			$("#PopUpTable").html(data);
			$('#myModal').modal('show');
					}
			});
			}
    	});	

			

</script>




<script>
  function status_update_click(status) {

	var po_id=$('#PO_ID').val();
	//alert(status);
	
	  
		$.ajax({
		  url:'po_status.php',
		  type:'POST',
		  data:{'STATUS':status,'INVOICE_ID':po_id},
		  success:function(data){
			  //console.log(data);			  
		
			 alert("DONE");
location.reload();
			}
			});	
   			
  }
</script>
<script>

function showData(data){
			
		$("#EmpList").html(data);	

		$('#key-datatable').dataTable({
        "bStateSave": true,
		 bSort : false, 
		
		
			language:{
				paginate:{
					previous:"<i class='mdi mdi-chevron-left'>",
					next:"<i class='mdi mdi-chevron-right'>"
					}
					},
					drawCallback:function(){
						$(".dataTables_paginate > .pagination").addClass("pagination-rounded")},
        "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
    });


</script>


</html>
