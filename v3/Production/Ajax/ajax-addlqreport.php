<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_ADDLQREPORT;

$PageNo = trim($_POST["PageNo"]);
$SearchKey = trim($_POST["SearchKey"]);
$LoadAll = trim($_POST["LoadAll"]);
$LotId = trim($_POST["LotId"]);

$bodyContent = array(
    'SearchKey' => $SearchKey,
    'PageNo' => $PageNo,
    'LoadAll' => $LoadAll,
    'LotId' => $LotId
);

$resList = CallAPI($api, "GET", "", $bodyContent);

// var_dump($resList);
 echo json_encode($resList, JSON_PRETTY_PRINT);
 exit;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->Data;
        ?>
        <?php if (!isset($error) || empty($error)) { ?>

            <table id="tblDataTable" data-paging="false" class="table dt-responsive nowrap w-100">

						<thead class="thead-light">
							<tr>
								<th>Lot No</th>
								<th>Grade</th>
								<th>Freezing<br> Type</th>
								<th>Packing<br> Style</th>
								<th>Date</th>
								<th>Brand</th>
								<th>Shift</th>
								<th>Mcs</th>
								<th>Options</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($List as $RowIqfData) {								
								/*$PIID = $RowIqfData->PoID;
								$SqlPoList = "SELECT * FROM `ScrmPoData` WHERE PoID='$PIID'";
								$StmtPoList = $iconn->query($SqlPoList);
								$RowPoList = $StmtPoList->fetch_array();*/
							?>
								<tr>
									<td> <?php echo $RowPoList['PoNo']; ?> </td>
									<td> <?php echo $RowIqfData->Grade; ?> </td>
									<td> <?php echo $RowIqfData->FreezingType; ?> </td>
									<td> <?php echo $RowIqfData->PackingStyle; ?> </td>
									<td> <?php echo date('d-m-Y', strtotime($RowIqfData->ProductionDate)); ?> </td>
									<td> <?php echo $RowIqfData->Brand; ?> </td>
									<td> <?php echo $RowIqfData->Shift; ?> </td>
									<td> <?php echo $RowIqfData->Mcs; ?> </td>
									<td>

										<a target="_blank" href="EditIqfReport.php?ProdID=<?php echo $RowIqfData->ProdID ?>&LotID=<?php echo $LotId ?>" class="mr-3 text-primary" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i>
										</a>
										<button class="btn btn-link text-danger Delete" data-id="<?php echo $RowIqfData->ProdID; ?>" data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete"><i class="mdi mdi-trash-can font-size-16">
											</i></button>
									</td>
								</tr>
							<?php } ?>

						</tbody>

					</table>
					<?php 
					    // Set the number of items per page
    					$itemsPerPage = 20;
    					// Calculate the start and end item numbers
    					$startItem = ($PageNo - 1) * $itemsPerPage + 1;
    					$endItem = min($PageNo * $itemsPerPage, $resTotal);
					?>
            <div class="mt-3 mb-3">
            	Showing <?php echo $startItem; ?> to <?php echo $endItem; ?> out of <?php echo $resTotal; ?> entries            	
            </div>
            <?php $activepagestyle = "border:2px solid #74788d" ?>
        <?php } ?>        
        <div>
    <input type='hidden' id='hdnTotalRecords' value='<?php echo $List->TotalCount ?>' />
    <ul class="pagination pagination-rounded justify-content-center">
        <?php
        $range = 5; 
        $start = max(1, $PageNo - $range);  
        $end = min($PageCount, $PageNo + $range);          
        
        if ($PageNo > 1) {
            echo '<li class="page-item"><button class="page-link btnPageno" data-pageno="1">First</button></li>';
        }        
        
        if ($PageNo > 1) {
            echo '<li class="page-item"><button class="page-link btnPageno" data-pageno="' . ($PageNo - 1) . '">Previous</button></li>';
        }        
        
        for ($i = $start; $i <= $end; $i++) {
            $pageActive = ($PageNo == $i) ? "active" : "";
            echo '<li class="page-item ' . $pageActive . '"><button class="page-link btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
        }        
        
        if ($PageNo < $PageCount) {
            echo '<li class="page-item"><button class="page-link btnPageno" data-pageno="' . ($PageNo + 1) . '">Next</button></li>';
        }        
        
        if ($PageNo < $PageCount) {
            echo '<li class="page-item"><button class="page-link btnPageno" data-pageno="' . $PageCount . '">Last</button></li>';
        }
        ?>
    </ul>
</div>


        <?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
}
?>