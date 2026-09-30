<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
if(!empty($_POST['submit'])){
	$api = API_QC_QUALITY_ASSURANCE_CHECK_LIST_POST;
	unset($_POST['submit']);
	$bodyContentPost = $_POST;
	$result = callAPI($api, "POST", "", $bodyContentPost);

	// echo json_encode($result, JSON_PRETTY_PRINT);
	// exit;

	header('Content-Type: application/json');
	echo json_encode($result);die;
}elseif(!empty($_POST['methodType']) && $_POST['methodType'] == 'GetBatchNumbers'){
	$api = API_QC_QUALITY_ASSURANCE_CHECK_LIST_GET_BATCH_DATA;
	$bodyContentPost = $_POST;
	$result = callAPI($api, "POST", "", $bodyContentPost);
	header('Content-Type: application/json');
	echo json_encode($result);die;

}elseif(!empty($_POST['methodType']) && $_POST['methodType'] == 'deleteRow'){
	$api = API_QC_QUALITY_ASSURANCE_CHECK_LIST_DELETE;
	$bodyContentPost = $_POST;
	$result = callAPI($api, "POST", "", $bodyContentPost);
	header('Content-Type: application/json');
	echo json_encode($result);die;

}elseif (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    include_once  'QualityAssuranceCheckListReport.php';
}
else{
	$PageNo = !empty($_POST["pageNo"])?trim($_POST["pageNo"]):1;
	$isReportPage = !empty($_POST["isReportPage"])?trim($_POST["isReportPage"]):0;
	$api = API_QC_QUALITY_ASSURANCE_CHECK_LIST_GET_FOR_LISTING;
	$bodyContentPost = $_POST;
	$results = callAPI($api, "POST", "", $bodyContentPost);
	//echo '<pre>';print_r( $results);die;
	if(!empty($results->Data)){
		$resList = $results->Data;
		$error = $resList->Error;
		if (!isset($error) || empty($error)) {
			$PageCount = $resList->PageCount;
			$resTotal = $resList->TotalCount;
			$List = $resList->Data->ResultsData;
           // echo '<pre>';print_r($List);die;
			?>
			<table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 5px">S.No</th>
                        <th>Invoice No </th>
                        <th style="width: 30px">Production Date</th>
                        <th>Batch Number </th>
                        <th>Production Code</th>
						<th>FreezingType</th>
                        <th>Brand</th>
                        <th>Variety</th>
                        <th>Type of Packing</th>
                        <th>Diclared Size Grade</th>
                        <th>Colour</th>
                        <th>General Appearance</th>
                        <th>Freshness</th>
                        <th>Odour</th>
                        <th>Texture</th>
                        <th>Temperature</th>
                        <th>Filling Weight in Gms</th>
                        <th>Deglazed wt</th>
                        <th>Glaze %</th>
                        <th>Count</th>
                        <th>Big Pieces (gms)</th>
                        <th>Small Pieces (gms)</th>
                        <th>Uniformity</th>
                        <th>Total Pieces / Slab</th>
                        <th>Dehydration</th>
                        <th>Discolouration</th>
                        <th>Black Spots / Tails</th>
                        <th>BKN Damaged Pieces</th>
                        <th>Legs, Veins, Shell Bits</th>
                        <th>Hanging Meat</th>
                        <th>Soft Shell</th>
                        <th>Foreign Matter</th>
                        <th>Tail BKN </th>
                        <th>Total % of Defect</th>
                        <th>Sailing Intact</th>
                        <th>Remarks</th>
						<?php 
						if(empty($isReportPage)){?>
							<th>Action</th>
						<?php }?>
                        
                    </tr>
                </thead>
                <tbody>
				
                    <?php 
						$pageStart = 0;
						foreach ($List as $item) {
							if(empty($pageStart)) {
								$pageStart = $item->SNO;
							}
							?>
                        <tr>
							<td style="width: 5px"><?php echo $item->SNO;?></td>
                            <td style="width: 30px"><?php echo $item->InvoiceNo;?></td>
							<td><?php echo $item->ArrivalDate;?></td>
							<td><?php echo strlen($item->ArrivalNumber)>50? '<p  data-toggle="tooltip"  data-original-title="'.$item->ArrivalNumber.'">'.substr($item->ArrivalNumber,0,50).'...'.'</p>':$item->ArrivalNumber;?></td>
							<td><?php echo $item->ProductionCode;?></td>
							<td><?php echo $item->FreezingType;?></td>
							<td><?php echo $item->BrandName;?></td>
							<td><?php echo $item->Variety;?></td>
							<td><?php echo $item->Packing;?></td>
                            <td><?php echo $item->DiclaredSizeGrade;?></td>
							<td><?php echo $item->Colour;?></td>
							<td><?php echo $item->GeneralAppearance;?></td>
							<td><?php echo $item->Freshness;?></td>
                            <td><?php echo $item->Odour;?></td>
							<td><?php echo $item->Texture;?></td>
							<td><?php echo $item->Temperature;?></td>
							<td><?php echo $item->FillingWeightInGms;?></td>
                            <td><?php echo $item->DeglazedWt;?></td>
							<td><?php echo $item->Glaze;?></td>
							<td><?php echo $item->Count;?></td>
							<td><?php echo $item->BigPieces;?></td>                            
                            <td><?php echo $item->SmallPieces;?></td>
							<td><?php echo $item->Uniformity;?></td>
                            <td><?php echo $item->TotalPiecesPerSlab;?></td>
							<td><?php echo $item->Dehydration;?></td>
							<td><?php echo $item->Discolouration;?></td>
							<td><?php echo $item->BlackSpotsPerTails;?></td>
                            <td><?php echo $item->BKNDamagedPieces;?></td>
							<td><?php echo $item->LegsVeinsShellBits;?></td>
                            <td><?php echo $item->HangingMeat;?></td>
							<td><?php echo $item->SoftShell;?></td>
							<td><?php echo $item->ForeignMatter;?></td>
							<td><?php echo $item->TailBKN;?></td>
                            <td><?php echo $item->TotalPercentageOfDefect;?></td>
							<td><?php echo $item->SailingIntact;?></td>
                            <td><?php echo $item->Remarks;?></td>
							<?php
							if(empty($isReportPage)){?>
								<td>
									<a href="online-organoleptic-inspection-report-form.php?id=<?php echo $item->AssuranceId;?>" 									
									class="edit"><i class="mdi mdi-pencil font-size-16"></i></a>
									<a href="javascript:void(0);" data-Assurance_Id="<?php echo $item->AssuranceId;?>" class="delete text-danger"><i class="mdi mdi-delete font-size-16"></i></a>
								</td>
							<?php }?>							
						</tr>
						<?php }?>
				</tbody>
			</table>
			<div>
				<?php 
				if(!empty($resTotal)){?>
					<div class="mt-3 mb-3">Showing <?php echo $pageStart;?> to <?php echo $item->SNO ?> out of <?php echo $resTotal ?> entries </div>
					<ul class="pagination pagination-rounded <?php echo $PageCount<30?'justify-content-center':'';?>" style="overflow-x:auto;margin:0 auto;">

<?php
$current = $PageNo;
$total = $PageCount;


if ($current > 1) {
    echo '<li class="page-item">
            <button class="page-link btnPageno" data-pageno="'.($current-1).'">&laquo;</button>
          </li>';
}


for ($i = 1; $i <= min(2, $total); $i++) {
    $active = ($i == $current) ? "active" : "";
    echo '<li class="page-item"><button class="page-link '.$active.' btnPageno" data-pageno="'.$i.'">'.$i.'</button></li>';
}


if ($current > 4) {
    echo '<li class="page-item disabled"><span class="page-link">..</span></li>';
}


$start = max(3, $current - 1);
$end   = min($total - 2, $current + 1);

for ($i = $start; $i <= $end; $i++) {
    $active = ($i == $current) ? "active" : "";
    echo '<li class="page-item"><button class="page-link '.$active.' btnPageno" data-pageno="'.$i.'">'.$i.'</button></li>';
}


if ($current < $total - 3) {
    echo '<li class="page-item disabled"><span class="page-link">..</span></li>';
}


for ($i = max($total - 1, 3); $i <= $total; $i++) {
    $active = ($i == $current) ? "active" : "";
    echo '<li class="page-item"><button class="page-link '.$active.' btnPageno" data-pageno="'.$i.'">'.$i.'</button></li>';
}


if ($current < $total) {
    echo '<li class="page-item">
            <button class="page-link btnPageno" data-pageno="'.($current+1).'">&raquo;</button>
          </li>';
}
?>

</ul>
				<?php }?>
			</div>
		<?php }else {
			echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
		}
	}else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : No Data Found</div>";
    }
}

