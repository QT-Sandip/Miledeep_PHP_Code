<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_EMPLOYEE_DETAILS;

$DeptID = trim($_POST["DeptID"]);
$SubDeptID = trim($_POST["SubDeptID"]);
$DesigID = trim($_POST["DesigID"]);
$EmpStatus = trim($_POST["EmpStatus"]);
$SearchKey = trim($_POST["SearchKey"]);
$PageNo = trim($_POST["PageNo"]);
$LoadAll = trim($_POST["LoadAll"]);

$bodyContent = array(
    'DeptID' => $DeptID,
    'SubDeptID' => $SubDeptID,
    'DesigID' => $DesigID,
    'EmployeeStatus' => $EmpStatus,
    'SearchKey' => $SearchKey,
    'PageNo' => $PageNo,
    'LoadAll' => $LoadAll
);
// var_dump($bodyContent);

$resEmployeeDetails = CallAPI($api, "GET", "", $bodyContent);
// echo json_encode($resEmployeeDetails, JSON_PRETTY_PRINT);

if (isset($resEmployeeDetails) && !empty($resEmployeeDetails)) {

    $error = $resEmployeeDetails->Error;


    if (!isset($error) || empty($error)) {
        $PageCount = $resEmployeeDetails->PageCount;
        $resList = $resEmployeeDetails->Data;

?>
<!-- Durga -->
<?php
$currentPage = $PageNo;
$pageSize = count($resList);

$startEntry = ($currentPage - 1) * $pageSize + 1;
$endEntry = ($currentPage * $pageSize);

?>
<!-- Durga -->
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width:50px;">Bio ID</th>
                    <th style="min-width:120px;">Name</th>
                    <th style="min-width:100px;">Department</th>
                    <th style="min-width:140px;">SubDepartment</th>
                    <th style="min-width:350px;">Designation</th>
                    <th style="min-width:120px;">Contractor</th>
                    <th style="min-width:70px;"> Status</th>
                    <th style="min-width:100px;" class="text-center">Actions </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resList as $item) { ?>
                    <tr>

                        <td>
                            <?php echo $item->BioMetricID ?>
                        </td>
                        <td class="tdCInvNo">
                            <?php echo $item->EmpName ?>
                        </td>
                        <td>
                            <?php echo $item->DepartmentName ?>
                        </td>
                        <td>
                            <?php echo $item->SubDeptName ?>
                        </td>
                        <td>
                            <?php echo $item->Designation ?>
                        </td>
                        <td>
                            <?php echo $item->ContractorID ?>
                        </td>
                        <?php if ($EmpStatus == "1") {
                            $Color = "#025009";
                        } else if ($EmpStatus == "0") {
                            $Color = "#FA0F04";
                        }
                        ?>
                        <td>
                            <div class="bg-active color-palette text-center">
                                <button class="btn btn-link badge badge-warning font-size-10 " style="width:70px;background:<?php echo $Color ?>;color:white" >
                                    <?php //echo $EmpStatus 
                                        if ($EmpStatus == "1") {
                                            echo "ACTIVE";
                                        } else if ($EmpStatus == "0") {
                                            echo "IN ACTIVE";
                                        }
                                    ?>
                                </button>
                            </div>
                        </td>

                        <td class="text-center">

                            <?php // if ($resEmployeeDetails->EditAccess) {
                                    echo '
                                    <a href="' . $resEmployeeDetails->EditLink . $item->EmpID . '"
                                        class="mr-3 text-primary btnActions" data-placement="top" title="Edit Booking" data-original-title="Edit">
                                        <i class="mdi mdi-pencil font-size-16"></i>
                                    </a>';
                                // }
                            ?>
                            
                            <?php // if ($resEmployeeDetails->DeleteAccess) {

                                // btnDelete
                                    echo ' <button class="btn btn-link text-danger btnActions" style="position: relative;top: -3px" data-id="'.$item->EmpID.'" data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete">
                                            <i class="mdi mdi-trash-can font-size-16"></i>
                                           </button>';
                                //}
                            ?>

                        </td>
                    <?php } ?>
                    </tr>

            </tbody>
        </table>
        <!-- <div class="mt-3 mb-3">Showing 1 to
            <?php // echo count($resList) ?> out of
            <?php // echo $resEmployeeDetails->TotalCount ?> entries
        </div> -->
        <div class="mt-3 mb-3">Showing 1 to <?php echo count($resList) ?> out of <?php echo $resEmployeeDetails->TotalCount ?> entries</div>
        <?php $activepagestyle="border:2px solid #74788d"?>
        <div>
            <!-- <input type='hidden' id='hdnTotalRecords' value='<?php echo $resEmployeeDetails->TotalCount ?>' /> -->
            <ul class="pagination pagination-rounded justify-content-center">
                <?php
                for ($i = 1; $i <= $PageCount; $i++) {
                    $pageActive = ($PageNo == $i ? "active" : "");
                    echo '<li class="page-item"><button class="page-link ' . $pageActive . 'btnPageno" data-pageno="' . $i . '"style = "' . (($PageNo == $i) ? $activepagestyle: "") . '">' . $i . '</button></li>';
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