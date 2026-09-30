<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = isset($_POST["methodType"]) && !empty($_POST["methodType"]) ? $_POST["methodType"] : "";



$api = API_ADMIN_DAILY_PRODUCTION_DETAILS;

if ($methodType == "delete") {
    $bodyContent = array(
        'ProductionID' => trim($_POST["ProductionID"]),
        'ProductionDate' => trim($_POST["ProductionDate"])        
    );

    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // exit;

    $resList = CallAPI($api, "DELETE", "", $bodyContent);
} else {

    $Date = trim($_POST["date"]);
    $ColdStores = trim($_POST["coldStore"]);
    $StockBlnTo = trim($_POST["stockBlnTO"]);
    $PackingType = trim($_POST["packingType"]);
    $Group = trim($_POST["group"]);
    $PageNo = trim($_POST["PageNo"]);
    $SearchKey = trim($_POST["SearchKey"]);
    $LoadAll = trim($_POST["LoadAll"]);

    $bodyContent = array(
        'ProductionDate' => $Date,
        'ColdStoreID' => $ColdStores,
        'StockHolderID' => $StockBlnTo,
        'PackingType' => $PackingType,
        'Group' => $Group,
        'SearchKey' => $SearchKey,
        'PageNo' => $PageNo,
        'LoadAll' => $LoadAll
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);
}
// var_dump($resList);
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        // $PageCount = $resList->PageCount;
        // $resTotal = $resList->TotalCount;
        $List = $resList->DailyProductionList;
        $Count = count($List);
?>
        <div class="card">
            <input type="hidden" id="hdnAPIRes" data-restype="success" value="<?php echo $resList->Msg ?>" />
            <div class="card-body">
                <table id="tblDataTable" class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>S.NO</th>
                            <th>Group</th>
                            <th>SUPC</th>
                            <th style="min-width:150px;" >Product </th>
                            <th>Grade</th>
                            <th>Packing Type</th>
                            <th>Packing Style</th>
                            <th>Mcs</th>
                            <th>Balance Mcs</th>
                            <th>Bags</th>
                            <th>Balance Bags</th>
                            <th>Kgs</th>
                            <th>Balance Kgs</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($List as $item) { ?>
                            <tr>
                                <td>
                                    <?php echo $item->SNo ?>
                                </td>
                                <td>
                                    <?php echo $item->Group ?>
                                </td>
                                <td>
                                    <?php echo $item->OurSupc ?>
                                </td>
                                <td>
                                    <?php echo $item->ProductDescription ?>
                                </td>
                                <td>
                                    <?php echo $item->Grade ?>
                                </td>
                                <td>
                                    <?php echo $item->PackingType ?>
                                </td>
                                <td>
                                    <?php echo $item->PackingStyle ?>
                                </td>
                                <td>
                                    <?php echo $item->Mcs ?>
                                </td>
                                <td>
                                    <?php echo $item->BalanceMcs ?>
                                </td>
                                <td>
                                    <?php echo $item->LooseBags ?>
                                </td>
                                <td>
                                    <?php echo $item->BalanceBags ?>
                                </td>
                                <td>
                                    <?php echo $item->Kgs ?>
                                </td>

                                <td>
                                    <?php echo $item->BalanceKgs ?>
                                </td>
                                <td>
                                    <?php if ($item->Status == "1") { //  0 - pending ,  1- comleted 
                                    ?>

                                        <button class="btn btn-link badge badge-warning font-size-10" style="width:70px;background:#025009;color:white">
                                            Completed
                                        </button>
                                    <?php } else { ?>
                                        <button class="btn btn-link badge badge-danger font-size-10" style="width:70px;background:#e20000;color:white">
                                            Pending
                                        </button>
                                    <?php } ?>
                                </td>
                                <td>
                                    <!-- <a href="./daily-production-data.php?ProductionDataID=<?php // echo  $item->ProductionID; 
                                                                                                ?>" id="btnEdit" name="btnEdit"  data-stockid="<?php // echo $item->StockID 
                                                                                                                                                                                    ?>" class="btn btn-link p-0 deleteBrkUp btnEdit">
                                <i class="mdi mdi-eye font-size-16 text-primary"></i>
                            </a> -->
                                    
                                    <a href="./daily-production-data.php?ProductionDataID=<?php  echo  $item->ProductionID; ?>"  id="btnEdit" name="btnEdit" data-stockid="<?php echo $item->StockID ?>" class="btn btn-link p-0 deleteBrkUp btnEdit" >
                                        <i class="mdi mdi-pencil font-size-16 text-primary" title="Edit"></i>
                                    </a>
                                    <button  type="button" id="btnDelete" name="btnDelete" data-prodid="<?php echo $item->ProductionID ?>" class="btn btn-link p-0 deleteBrkUp btnDelete">
                                        <i class="mdi mdi-delete font-size-16 text-danger" title="Delete"></i>
                                    </button>


                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="mt-3 mb-3">Showing 1 to
                    <?php echo count($List) ?> out of
                    <?php echo $Count ?> entries
                </div>

                <?php // $activepagestyle = "border:2px solid #74788d" 
                ?>
                <div>
                    <!-- <input type='hidden' id='hdnTotalRecords' value='<?php echo $resTotal ?>' /> -->
                    <ul class="pagination pagination-rounded justify-content-center">
                        <?php
                        // for ($i = 1; $i <= $PageCount; $i++) {
                        //     $pageActive = ($PageNo == $i ? "active" : "");
                        //     echo '<li class="page-item"><button class="page-link ' . $pageActive . 'btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
                        // }
                        ?>
                    </ul>
                </div>
<?php } else {
        echo "
        <input type='hidden' id='hdnAPIRes' data-restype='error' value='.$resList->Error .' />
        <div class='text-center mt-3 mb-3 text-bold'>NOTE : " . $error . "</div>";
    }
} ?>

            </div>
        </div>