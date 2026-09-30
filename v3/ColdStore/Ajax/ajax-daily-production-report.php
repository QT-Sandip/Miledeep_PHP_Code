<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_DAILY_PRODUCTION_REPORT;

$bodyContent = array(
    "ProductionDate" => trim($_POST["productionDate"]),
    "Species" => trim($_POST["species"]),
    "ProductionPlant" => trim($_POST["productionPlant"]),
    "TypeProduct" => trim($_POST["typeProduct"]),
    "FreezingType" => trim($_POST["freezingType"]),
    "Variety" => trim($_POST["Variety"]),
    "PackingStyleID" => trim($_POST["PckgStyle"]),
    "Treatment" => trim($_POST["Soaking"]),
    "Type" => trim($_POST["type"]),
    "ColdStore" => trim($_POST["coldStore"]),
    "PackingType" => trim($_POST["packingType"]),
    "Group" => trim($_POST["group"]),
    // "balanceType" => trim($_POST["balanceType"]),

    "isReturn" => trim($_POST["isReturn"]),
    "transtype" => trim($_POST["transtype"]),
);

// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
// exit;   
$resList = CallAPI($api, "GET", "", $bodyContent);
        //echo '<pre>';print_r($resList);die;



if (isset($resList) && !empty($resList)) {
    $error = $resList->Error;
    

    

    if (!isset($error) || empty($error)) {
        $List = $resList->Data;
        $resGrades = $resList->Grades;
        $resData = $resList->Data;
        $resSlabsData = $List[0]->SlabsData;
        $keys = array_keys((array)$resSlabsData);
        $Data = $resList->Data;
        $TotalOpening = $resList->TotalOpening;
        $TotalClosing = $resList->TotalClosing;
        


        $colspan = count($resGrades) + 1;
        // echo json_encode($List, JSON_PRETTY_PRINT);
?>
        <table id="tblDataTable" data-paging="false" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width:50px;width: 50px;">S.No</th>
                    <th style="min-width: 100px;width: 100px;">Variety</th>
                    <th style="min-width: 100px;width: 100px;">PackingStyle</th>
                    <th style="min-width: 100px;width: 100px;">Treatment (Soaking Type)</th>
                    <th style="min-width: 200px;width: 200px;">StockType</th>
                    <?php
                    foreach ($resGrades as $Grades) {  ?>
                        <th style="min-width: 80px;width: 80px;"><span style="opacity: 0;">'</span><?php echo $Grades->ValueGrade; ?></th>
                    <?php } ?>

                </tr>
            </thead>
            <tbody>
                <?php foreach ($resData as $item) { ?>
                    <tr>
                        <td style=" min-width:50px;width: 50px;"><?php echo $item->SNo ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->ShortName ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->PackingStyle ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->SockingType ?></td>
                        <td style="padding: 0px !important;" colspan="<?php echo $colspan ?>">
                            <table class="subTable table table-bordered mb-0" style="width: 100%" id="subTable">
                                <tbody>
                                    <?php foreach ($item->SlabsData as $slabKeys) { ?>
                                        <tr>
                                            <td style="min-width: 200px;width: 200px;"><?php echo $slabKeys->StockType ?></td>
                                            <?php foreach ($slabKeys->GradeSlabs  as $ItemMasterGrades) {
                                                if ($ItemMasterGrades->Grade != 'Total') {
                                            ?>
                                            <td style="min-width: 80px;width: 80px;"><?php echo ($ItemMasterGrades->ValueGrade != '') ? $ItemMasterGrades->ValueGrade : '-' ?></td>
                                            <?php }
                                            } ?>
                                            <td style="min-width: 80px;width: 80px;"><?php echo $slabKeys->TotalStock ? $slabKeys->TotalStock : '-' ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </td>
                        <?php foreach ($resGrades as $Grades) { ?>
                            <td style="display: none"></td>
                        <?php } ?>

                    </tr>
                <?php } ?>
            </tbody>

             <tfoot>
                    <tr>
                        <th></th>
                        <th>Opening Balance <?php echo $TotalOpening ?> </th>
                        <th></th>
                        <th></th>
                        <th></th>
                        
                    </tr>
                    <tr>
                        <th></th>
                        <th>Closing Balance <?php echo $TotalClosing ?> </th>
                        <th></th>
                        <th></th>
                        <th></th>
                        
                    </tr>
                </tfoot>
        </table>

        <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo count($List) ?> entries</div>
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
        echo "<div class='text-center mt-3 mb-3 text-bold'>NOTE : " . $error . "</div>";
    }
}
?>