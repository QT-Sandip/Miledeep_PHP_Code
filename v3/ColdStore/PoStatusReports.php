<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$PageTitle = "Po Wise Stock Data";
$PageTitleFolder = "ColdStore";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
$AccessType = $_SESSION['AccessType'];
$UnitID = $_SESSION['UnitID'];
$processorQry = "";
if ($AccessType != 1) {

	//$processorQry= " and ProcessorID=$UnitID";
}
//ProductionStartedDate!='' and
$SqlPos = "SELECT *,SSC.Name as PoStatus ,SPC.Name as Port FROM ScrmPoData as POD 
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id 
left join ScrmPortCities as SPC on POD.PortOfDischarge	=SPC.PortId 
left join ScrmShipmentStatusCodes as SSC on POD.Status=SSC.Code
left join ScrmShipmentDetailsBreif as SDB on POD.PoID=SDB.PiID
WHERE   (TransportedDate='' or TransportedDate is null)  
 $processorQry
 order by PoID desc";
$StmtPos = $iconn->query($SqlPos);

function getFinalMcs($PoGradeId)
{
	global $iconn;
	$SqlGradeData = "SELECT COALESCE(sum(AvalMcs),0) as AvalMcs,
								COALESCE(sum(AvalMcs*TotalSlabs*SlabWeight*MFactor),0) as FinishedKgs
								FROM `CsStockData` as CSD LEFT JOIN AdminPackingStyles as APS
 								on APS.PCKID=CSD.PackingStyleID WHERE PoGradeId='$PoGradeId'";
	$StmtGradeData = $iconn->query($SqlGradeData);
	$RowGradeData = $StmtGradeData->fetch_array();
	return $RowGradeData;
}


function getDummyStock($VarietyID)
{
	global $iconn;


	$SqlGradeData = "SELECT * FROM `ScrmPIVarietyList` WHERE VarietyID=$VarietyID";
	$StmtGradeData = $iconn->query($SqlGradeData);
	$RowGradeData = $StmtGradeData->fetch_array();

	$TypeProduct = $RowGradeData['TypeProduct'];
	$Glaze = $RowGradeData['Glaze'];
	$Treatment = $RowGradeData['Treatment'];
	$Grade = $RowGradeData['Grade'];
	$FreezingType = $RowGradeData['FreezingType'];
	$ProductID = $RowGradeData['ProductID'];
	$Species = $RowGradeData['Species'];
	$PreparationType = $RowGradeData['PreparationType'];
	$GlazePerc = $RowGradeData['GlazePerc'];

	$SqlGradeData = "SELECT COALESCE(GROUP_CONCAT(VarietyID),0) as VarityID  FROM `ScrmPIVarietyList` WHERE 
			TypeProduct ='$TypeProduct' and Glaze='$Glaze' and Treatment='$Treatment'  and Grade='$Grade' 
			and FreezingType='$FreezingType' and ProductID='$ProductID' and Species='$Species' and PreparationType='$PreparationType' 
			and GlazePerc='$GlazePerc'";
	$StmtGradeData = $iconn->query($SqlGradeData);
	$RowGradeData = $StmtGradeData->fetch_array();
	$VarietyIDs = $RowGradeData['VarityID'];


	$SqlGradeData = "SELECT COALESCE(sum(AvalMcs*TotalSlabs*SlabWeight*MFactor),0) as StoreKgs FROM `CsStockData` as CSD left JOIN
			  AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
			  WHERE PoGradeId='0' and VarietyID in ($VarietyIDs)";
	$StmtGradeData = $iconn->query($SqlGradeData);
	$RowGradeData = $StmtGradeData->fetch_array();
	$StoreKg = $RowGradeData['StoreKgs'];



	return $StoreKg;
}

function getRmReq($VarietyID, $BalWeight)
{
	global $iconn;
	$BalWeight;
	$SqlGradeData = "SELECT * FROM `ScrmPIVarietyList` WHERE VarietyID=$VarietyID";
	$StmtGradeData = $iconn->query($SqlGradeData);
	$RowGradeData = $StmtGradeData->fetch_array();

	$Grade = $RowGradeData['Grade'];
	$ShortName = $RowGradeData['ShortName'];
	$GlazePerc = $RowGradeData['GlazePerc'];
	$AryGrade = explode('/', $Grade);
	$PackingCount = $AryGrade[1] - 2;

	if ($GlazePerc > 0) {
		$GlazePerc = 100 - $GlazePerc;
		$GlazePerc = $GlazePerc / 100;
		$PackingCount = round(($PackingCount / $GlazePerc), 2);
		$BalWeight = round(($BalWeight * $GlazePerc), 2);
	}

	if ($ShortName == 'HLSO' || $ShortName == 'EZPL') {
		$HLWeight = round($BalWeight * 0.95, 2);
		$HonCount = round(($PackingCount * 0.70 * 2.2) / 0.95, 2);
	} else {
		$HLWeight = round($BalWeight / 0.95, 2);
		$HonCount = round($PackingCount * 0.70 * 2.2 * 0.95, 2);
	}
	$HonWeight = round($HLWeight / 0.70, 2);
	$AryData['HonWeight'] = $HonWeight;
	$AryData['HonCount'] = $HonCount;
	return $AryData;
}

?>
<!DOCTYPE html>

<html lang="en">


<head>

	<?php include($IncludesPath . "title-meta.php"); ?>
	<?php include($IncludesPath . "DbLinks.php"); ?>



	<?php include($IncludesPath . "head-css.php"); ?>

</head>

<body data-sidebar="dark">
	<?php include($IncludesPath . "PreLoader.php"); ?>

	<!-- Begin page -->
	<div id="layout-wrapper">


		<?php
		include($IncludesPath . "topbar.php");
		include($IncludesPath . "sidebar.php");
		?>



		<!-- ============================================================== -->
		<!-- Start right Content here -->
		<!-- ============================================================== -->
		<div class="main-content">

			<div class="page-content">
				<div class="container-fluid">

					<?php include($IncludesPath . "page-title.php"); ?>


					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body" id="ClientList">



									<!--<table id="datatable-buttons" 
										class="table table-striped 
										table-bordered dt-responsive nowrap w-100" >  -->

									<table id="key-datatable" class="table table-bordered   nowrap w-100" data-paging="false" style="background:#fff;margin-top:6px 
	!important;margin-bottom:-6px !important; height:100px">
										<thead class="thead-light ">


											<th>S.No</th>
											<th>Buyer</th>
											<th>Po No</th>
											<th>Internal LotNo</th>
											<!--<th>Port</th>-->
											<th>Brand</th>
											<th>Shipment Date</th>
											<th>Variety</th>
											<th>Packing</th>
											<th>Grade</th>
											<th>Ordered Mcs</th>
											<th>Final Mcs</th>
											<th>Bal Mcs</th>
											<th>Ordered Kgs</th>
											<th>Final Kgs</th>
											<!-- <th>Dummy Kgs</th>	 -->
											<th>Bal Kgs</th>
											<th>Aval In Store Kgs</th>

											<th>Hon Count(Req)</th>
											<th>Hon Qty(Req)</th>


										</thead>
										<tbody>
											<?php



											$PoCount = 1;


											// 

											while ($RowPos = $StmtPos->fetch_array()) {


												$SNO = $PoCount;
												$InvId = $RowPos['PoID'];
												$PoNum = $RowPos['PoNo'];
												$LotNo = $RowPos['LotNo'];
												$Port = $RowPos['ConsigneePort'];
												$Brand = $RowPos['Brand'];
												$ConsigneeName = $RowPos['ConsigneeName'];
												$ShipmentDate = getFormatedDate($RowPos['ShipByDate']);

												$SqlPoBreakUp =
													"SELECT *,Sum(MCS*TotalSlabs*SlabWeight*MFactor) as OrderKgs FROM `ScrmPoGradeData`
 as SPGD 
left JOIN ScrmPIVarietyList as SPVL on SPGD.VarietyID=SPVL.VarietyID
LEFT join AdminPackingStyles as APS On APS.PCKID=SPGD.PackingStyleID
WHERE InvId='$InvId' group by SPGD.ID";



												//echo '<br>';

												$StmtPoBreakUp = $iconn->query($SqlPoBreakUp);
												$IsGradeAval = $StmtPoBreakUp->num_rows;

												if ($IsGradeAval > 0) {
												} else {
													continue;
												}




												$PoTotalOrderdMcs =
													$PoTotalFinishedMcs =
													$PoTotalDummyMcs =
													$PoTotalReqMcs =
													$PoTotalOrderKgs =
													$PoTotalFinishedKgs =
													$PoTotalDummyKgs =
													$PoTotalBalKgs = 0;

												while ($RowPoBreakUp = $StmtPoBreakUp->fetch_array()) {

													$HonWeight = $AryData['HonWeight'];
													$HonCount = $AryData['HonCount'];

													$Variety = $RowPoBreakUp['ProductDescription'];
													$OrderedMcs = $RowPoBreakUp['Mcs'];
													$PackingStyle = $RowPoBreakUp['PackingStyle'];
													$Grade = $RowPoBreakUp['Grade'];
													$GradeWiseBrand = $RowPoBreakUp['Brand'];
													$OrderKgs = $RowPoBreakUp['OrderKgs'];
													$FinishedMcs = 0;
													$FinishedMcs = getFinalMcs($RowPoBreakUp['ID'])['AvalMcs'];
													$FinishedKgs = getFinalMcs($RowPoBreakUp['ID'])['FinishedKgs'];
													$BalKgs = $OrderKgs - $FinishedKgs;
													$ReqMcs = $OrderedMcs - $FinishedMcs;


													/*$OrderedMcs=$RowPoBreakUp['RequiredCases'];
				  	$DummyMcs=$RowPoBreakUp['DummyMcs'];
				  	$ReqMcs=$OrderedMcs-$FinishedMcs;
				    $StoreAvalKgs=$RowPoBreakUp['StoreAvalKgs']+$RowPoBreakUp['StoreAvalLKgs'];			   
				    $FinishedKgs=$RowPoBreakUp['FinishedKgs'];
				    $DummyKgs=$RowPoBreakUp['DummyKgs'];
				    $BalKgs=$OrderKgs-($FinishedKgs+$DummyKgs);
					*/

													if ($FinishedMcs > $OrderedMcs) {
														$Style = 'font-weight:bold;font-size:11px;color:#0bb50b';
													} else {
														$Style = 'font-weight:bold;font-size:11px;color:black';
													}

													$StyleTotal = 'font-weight:bold;font-size:11px;color:red;background: #d2d6de;';



													$PoTotalOrderdMcs += $OrderedMcs;
													$PoTotalFinishedMcs += $FinishedMcs;
													$PoTotalReqMcs += $ReqMcs;

													$PoTotalOrderKgs += $OrderKgs;
													$PoTotalFinishedKgs += $FinishedKgs;
													$PoTotalDummyKgs += $DummyKgs;
													$PoTotalBalKgs += $BalKgs;



													$AryData = getRmReq($RowPoBreakUp['VarietyID'], $BalKgs);
													$StoreStock = getDummyStock($RowPoBreakUp['VarietyID']);

													$HonWeight = $AryData['HonWeight'];
													$HonCount = $AryData['HonCount'];

													/*po Total*/

											?>



													<tr style="<?php echo $Style ?>">
														<td><?php echo $SNO;
															$SNO = '' ?></td>
														<td><?php echo $ConsigneeName ?></td>
														<td><?php echo $PoNum; ?></td>
														<td><?php echo $LotNo; ?></td>
														<!--<td ><?php echo $Port; ?></td> -->
														<td><?php echo $GradeWiseBrand; ?></td>
														<td><?php echo $ShipmentDate; ?></td>
														<td><?php echo $Variety; ?></td>
														<td><?php echo $PackingStyle; ?></td>
														<td><?php echo $Grade; ?></td>
														<td><?php echo $OrderedMcs; ?></td>
														<td><?php echo $FinishedMcs; ?></td>
														<td><?php echo $ReqMcs; ?></td>
														<td><?php echo  round($OrderKgs, 2); ?></td>
														<td><?php echo  round($FinishedKgs, 2); ?></td>
														<!-- <td><?php echo  round($DummyKgs, 2); ?></td> -->
														<td><?php echo  round($BalKgs, 2); ?></td>
														<td><?php echo  round($StoreStock, 2); ?></td>

														<td><?php echo $HonCount ?></td>
														<td><?php echo $HonWeight ?></td>
													</tr>

												<?php  }
												$PoCount++;
												?>


												<tr style="<?php echo $StyleTotal ?>">
													<td></td>
													<td>Total</td>
													<td></td>
													<!--<td ></td>-->
													<td></td>
													<td></td>
													<td></td>
													<td></td>
													<td></td>
													<td></td>
													<td><?php echo $PoTotalOrderdMcs; ?></td>
													<td><?php echo $PoTotalFinishedMcs; ?></td>
													<td><?php echo $PoTotalReqMcs; ?></td>
													<td><?php echo  round($PoTotalOrderKgs, 2); ?></td>
													<td><?php echo  round($PoTotalFinishedKgs, 2); ?></td>
													<!--	<td><?php echo  round($PoTotalDummyKgs, 2); ?></td>-->
													<td><?php echo  round($PoTotalBalKgs, 2); ?></td>
													<td></td>
													<td></td>
													<td></td>

												</tr>




											<?php	} ?>



										</tbody>
									</table>

								</div>

								<!-- end col -->
							</div> <!-- end row -->





						</div> <!-- end card body-->
					</div> <!-- end card -->


					<?php include($IncludesPath . "footer.php"); ?>

				</div>
				<!-- end main content-->

			</div>
			<!-- END layout-wrapper -->



			<?php include($IncludesPath . "vendor-scripts.php"); ?>
			<?php include($IncludesPath . "NewScripts.php"); ?>



</body>

</html>
<Script>
	$('document').ready(function() {
		/*var table = $('#key-datatable').DataTable( {
        scrollY:"400px",
         scrollX:true,
         sScrollX: "100%",
  sScrollXInner: "150%", 
        paging:true,
        destroy: true,
        fixedColumns: true
        
    } );*/
		showData();



		// $.stickysidebarscroll("#tests",{offset: {top: 10, bottom: 200}});

		$(document).on('click', '#payBtn', function(e) {

			//alert("test");
			e.preventDefault();
			$('.trremove').remove();

			var refid = $(this).val();

			var PO_ID = document.getElementById('PO_ID'); //or grab it by tagname etc
			PO_ID.value = "" + refid;


			//	alert(refid);

			var large2 = 'TEST'

			$('.modheading').text('Update Status');

			$('#myModal').modal('toggle');



			//return false;



		});

		$('#elisa').on('click', function() {
			alert("test");
			$('form').submit();
		});
	});

	//ROBO2		
	$(document).on('click', '#BtnViewPoStatus', function(e) {
		e.preventDefault();
		//	alert("INT_LOT_NUM Required");

		var INT_LOT_NUM = $(this).val();
		//alert(INT_LOT_NUM);

		var UNIT = $('#JME_MOURYA').val();
		if (INT_LOT_NUM == '') {
			$("#INT_LOT_NUM").focus();
			alert("PO Number Required");
			return;
		} else {



			$.ajax({
				url: 'Ajax/AjPopUpPoStatus.php',
				type: 'POST',
				data: {
					'INT_LOT_NUM': INT_LOT_NUM,
					'TYPE': 'KK',
					'UNIT': UNIT
				},
				success: function(data) {

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

		var po_id = $('#PO_ID').val();
		//alert(status);


		$.ajax({
			url: 'po_status.php',
			type: 'POST',
			data: {
				'STATUS': status,
				'INVOICE_ID': po_id
			},
			success: function(data) {
				//console.log(data);			  

				alert("DONE");
				location.reload();
			}
		});

	}
</script>



<script>
	function showData(data) {

		$("#ClientList").html(data);
		$('#key-datatable').dataTable({
			"bStateSave": true,
			keys: !0,
			scrollY: "600px",
			scrollX: true,
			sScrollX: "100%",
			sScrollXInner: "150%",
			paging: true,
			destroy: true,
			fixedColumns: true,
			bSort: false,
			dom: 'Blfrtip',
			buttons: [{
				extend: 'excel',
				text: 'Download Excel',
				className: 'btnExcelDataTable',
				title: 'PO WISE STOCK DATA',
				// footer: true,
				exportOptions: {
					columns: ':visible'
				}
			}],
			language: {
				paginate: {
					previous: "<i class='mdi mdi-chevron-left'>",
					next: "<i class='mdi mdi-chevron-right'>"
				}
			},
			drawCallback: function() {
				$(".dataTables_paginate > .pagination").addClass("pagination-rounded")
			},
			"fnStateSave": function(oSettings, oData) {
				localStorage.setItem('key-datatable', JSON.stringify(oData));
			},
			"fnStateLoad": function(oSettings) {
				return JSON.parse(localStorage.getItem('key-datatable'));
			}
		});

	}
</script>
<script>
	/*

function showData(data){
			
					$("#ClientList").html(data);
 
 		var Stable=    $('#scroll-vertical-datatable').DataTable({
        "scrollY":        "450px",
        "scrollCollapse": true,
        "paging":         false,
        "language": {
            "paginate": {
                "previous": "<i class='mdi mdi-chevron-left'>",
                "next": "<i class='mdi mdi-chevron-right'>"
            }
        },
        "drawCallback": function () {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        },
		buttons: ['copy', 'excel', 'pdf', 'colvis']
    });
	
	Stable.buttons().container()
        .appendTo('#scroll-vertical-datatable_wrapper .col-md-6:eq(0)');
		}


		
*/
</script>