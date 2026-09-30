<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// $api = API_PREPARATION_MASTARS;

$StartDate = trim($_POST["startDate"]);
$PageNo = trim($_POST["pageNo"]);
$SearchKey = trim($_POST["searchKey"]);
$LoadAll = trim($_POST["loadAll"]);

// $bodyContent = array(
//     'StartDate' => $StartDate,
//     'SearchKey' => $SearchKey,
//     'PageNo' => $PageNo,
//     'LoadAll' => $LoadAll
// );

// $resList = CallAPI($api, "GET", "", $bodyContent);

// if (isset($resList) && !empty($resList)) {

//     $error = $resList->Error;

//     if (!isset($error) || empty($error)) {
//         $PageCount = $resList->PageCount;
//         $resTotal = $resList->TotalCount;
//         $List = $resList->Data;
?>
<?php // if (!isset($error) || empty($error)) { 
?>

<table id="tblDataTable" class="table table-hover table-bordered">
    <thead class="thead-light">
        <tr>
            <th style="min-width: 80px">S.No</th>
            <th style="min-width: 100px;">Plant</th>
            <th style="min-width: 100px;">Name of Audit</th>
            <th style="min-width: 100px;">Type of Audit</th>
            <th style="min-width: 100px;">Certifying Authority</th>
            <th style="min-width: 100px;">Audit Program</th>
            <th style="min-width: 100px;">Audit Grade</th>
            <th style="min-width: 100px;">Registration Number</th>
            <th style="min-width: 100px;">Certificate Number</th>
            <th style="min-width: 100px;">Audit Start Date</th>
            <th style="min-width: 100px;">Audit End Date</th>
            <th style="min-width: 100px;">Issued on</th>
            <th style="min-width: 100px;">Validity Upto</th>
            <th style="min-width: 100px;">Renewal Date</th>
            <th style="min-width: 80px">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php // foreach ($List as $item) { 
        ?>
        <tr>
            <td><?php echo "1" ?></td>
            <td><?php echo "TEST Plant" ?></td>
            <td><?php echo "TEST Name" ?></td>
            <td><?php echo "TEST Audit" ?></td>
            <td><?php echo "TEST Authority" ?></td>
            <td><?php echo "TEST Program" ?></td>
            <td><?php echo "TEST Grade" ?></td>
            <td><?php echo "TEST Registration" ?></td>
            <td><?php echo "TEST Certificate" ?></td>
            <td><?php echo "08-07-2024" ?></td>
            <td><?php echo "08-07-2024" ?></td>
            <td><?php echo "08-07-2024" ?></td>
            <td><?php echo "08-07-2024" ?></td>
            <td><?php echo "08-07-2024" ?></td>
            <td><?php echo "Test Status" ?></td>
        </tr>
        <?php // } 
        ?>
    </tbody>
</table>
<div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>
<?php $activepagestyle = "border:2px solid #74788d" ?>
<?php // } 
?>
<div>
    <input type='hidden' id='hdnTotalRecords' value='<?php echo $List->TotalCount ?>' />
    <ul class="pagination pagination-rounded justify-content-center">
        <?php
        for ($i = 1; $i <= $PageCount; $i++) {
            $pageActive = ($PageNo == $i ? "active" : "");
            echo '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
        }
        ?>
    </ul>
</div>
<?php
//     } else {
//         echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
//     }
// }
?>