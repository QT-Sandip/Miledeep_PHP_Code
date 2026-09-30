<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_GS_INDENTLIST_REPORT;

// echo print_r($api);exit;

$StartDate = trim($_POST["startDate"]);
$EndDate = trim($_POST["endDate"]);
$status = trim($_POST["status"]);



$bodyContent = array(
    'StartDate' => $StartDate,
    'EndDate' => $EndDate,
    'status' => $status,
);

$resList = CallAPI($api, "GET", "", $bodyContent);

// echo print_r($resList);exit;
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;

// echo print_r($api);exit;
$List = $resList->Data;
?>

<table id="tblDataTable" class="table table-hover table-bordered">
    <thead class="thead-light">
        <tr>
            <th style="min-width: 30px;">S.No</th>
            <th style="min-width: 120px;">Doc No</th>
            <th style="min-width: 120px;">Date</th>
            <th style="min-width: 120px;">Status </th>
            
        </tr>
    </thead>
    <tbody>
        <?php foreach ($List as $item) { 
            
              $Status = ($item->Status == 0 ? 'Approve' : ($item->Status == 1 ? 'Pending' : 'Completed'));
         $Color = ($item->Status == 0 ? 'Orange' : ($item->Status == 1 ? 'Red' : 'Green'));

           $clickAction = ($item->Status == 0)
    ? "onclick=\"updateStatus('{$item->Id}')\" style=\"cursor:pointer;color:$Color;\""
    : "style=\"color:$Color;\"";
            ?>
            
            <tr>
                <td><?php echo $item->SNo ?></td>
                <td><?php echo $item->DocNo ?></td>
                <td><?php echo date('d-m-Y', strtotime($item->Date)); ?></td>
                <td <?php echo $clickAction; ?>><?php echo $Status; ?></td>
            </tr>
        <?php  } ?>
    </tbody>
</table>