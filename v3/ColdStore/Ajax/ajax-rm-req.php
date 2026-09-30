<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include ($cp . "Includes/IncludePage.php");
include_once ROOT . 'config/api-call.php';

$api = API_RAW_MATERIAL_REQUIREMENT_REPORT;

$PageNo =  trim($_POST["pageNo"]);

$bodyContent = array(
    'PoID' => trim($_POST["ordernumber"]),
    'VarietyID' => trim($_POST["variety"]),
    'Species' => trim($_POST["species"]),
    'ProductType' => trim($_POST["productform"]),
    'FreezingType' => trim($_POST["freezingtype"]),   
    'Grade' => trim($_POST["grade"]),
    'Plant' => trim($_POST["plant"]),
    'PageNo' => trim($_POST["pageNo"]),
    'SearchKey' => trim($_POST["searchKey"]),
    'LoadAll' => trim($_POST["loadAll"])
);
// var_dump($bodyContent); exit;
// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
// exit;

$resList = CallAPI($api, "GET", "", $bodyContent);
// var_dump($bodyContent);

// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->Data;

        if (!isset($error) || empty($error)) {
            ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>SNO</th>
                        <th>Species</th>
                        <th>Grade</th>
                        <th>Variety</th>
                        <th>Product Form</th>
                        <th>FreezingType</th>
                        <th>Frozen Col</th>
                        <th>Glaze</th>
                        <th>Soaked</th>
                        <th>Pending Order Quantity</th>
                        <th>Floor Balance Quantity</th>
                        <th>Store Quantity</th>
                        <th>Required Quantity</th>
                        <th>Hon Count</th>
                        <th>Qty in KGS</th>
                        <th>Qty in TONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td><?php  echo $item->SNO; ?></td>
                            <td><?php  echo $item->Species; ?></td>
                            <td><?php  echo $item->Grade; ?></td>
                            <td><?php  echo $item->Species .'-'. $item->Variety; ?></td>
                            <td><?php  echo $item->ProductForm; ?></td>
                            <td><?php  echo $item->FreezingType; ?></td>
                            <td><?php  echo $item->FrozenCol; ?></td>
                            <td><?php  echo $item->Glaze; ?></td>
                            <td><?php  echo $item->Soaked; ?></td>                           
                            <td><?php  echo $item->OrderQuantity; ?></td>                           
                            <td><?php  echo $item->FloorBalanceQuantity; ?></td>                           
                            <td><?php  echo $item->StoreQuantity; ?></td>
                            <td style="color:<?php  echo $item->DisplayColour; ?>"><?php  echo $item->RequiredQuantity; ?></td>                                                      
                            <td><?php  echo $item->HeadonCount; ?></td>  
                            <td><?php  echo $item->QuantityinKgs; ?></td>  
                            <td><?php  echo $item->QuantityinTons; ?></td>                            
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>
            <?php $activepagestyle = "border:2px solid #74788d" ?>
        <?php } ?>
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
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>Note : " . $error . "</div>";
    }
}
?>