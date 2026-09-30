<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

    $api = API_RM_BILLS_LIST;

    if ($methodType == "get") {

        $PsId = trim($_POST["PsId"]);
        $BillDate = trim($_POST["BillDate"]);
        $Type = trim($_POST["Type"]);
        $ArrivalPlant = trim($_POST["ArrivalPlant"]);

        $bodyContent = array(
            'BillDate' => $BillDate,
            'Type' => $Type,
            'ArrivalPlant' => $ArrivalPlant,
            'PsId' => $PsId
        );

        $resList = CallAPI($api, "GET", "", $bodyContent);
        
    } else if ($methodType == "delete") {

        $RmArrivalID = trim($_POST["breakUpID"]);

        $bodyContentPost = array(
            "RmArrivalID" => $RmArrivalID
        );
        $resList = callAPI($api, "DELETE", "", $bodyContentPost);
    }
    $error = $resList->Error;
    $List = $resList->Data;
    // var_dump($List);

    // echo json_encode($resList, JSON_PRETTY_PRINT);

?>


    <?php if (!isset($error) || empty($error)) { ?>

        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>S.No</th>
                    <th>ArrivalNumber</th>
                    <th>ArrivalDate </th>
                    <th>Supplier</th>
                    <!-- <th>PurchaseType</th>	 -->
                    <!-- <th>Grader</th>	 -->
                    <!-- <th>Agent</th> -->
                    <th>ArrivalCount</th>
                    <th>ArrivalWeight</th>
                    <!-- <th>ArrivalRate</th> -->
                    <th>Total Bill Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($List as $item) { ?>
                    <tr>
                        <td><?php echo $item->SNO ?></td>
                        <td><?php echo $item->ArrivalNumber ?></td>
                        <td><?php echo date('d-m-Y', strtotime($item->ArrivalDate)) ?></td>
                        <td><?php echo $item->Supplier ?></td>
                        <!-- <td><?php // echo $item->PurchaseType 
                                    ?></td> -->
                        <!-- <td><?php // echo  $item->Grader 
                                    ?></td>	 -->
                        <!-- <td><?php // echo  $item->Agent 
                                    ?></td> -->
                        <td><?php echo $item->ArrivalCount ?></td>
                        <td style="text-align:right;"><?php echo number_format($item->ArrivalWeight, 3) ?></td>
                        <!-- <td  style="text-align:right;"><?php // echo number_format($item->ArrivalRate,2) 
                                                            ?></td>   -->
                        <td><?php echo $item->TotalBillAmount ?></td>
                        <td>
                            <?php
                            if ($item->BillStatus == "0") {
                                echo '<span class="statusPending"> Pending <span>';
                            } else {
                                echo '<span class="statusCompleted"> Completed <span>';
                            }
                            ?>
                        </td>
                        <td class="text-center">
                            <?php if ($resList->EditAccess) { ?>
                                <?php if ($item->BillStatus == "0") { ?>
                                    <a target="_blank" href="<?php echo $resList->EditLink . "?ArrivalID=" . $item->RmArrivalID ?>" class="mr-3 text-primary" data-placement="top" title="Add Bill" data-original-title="Add">
                                        <i class="mdi mdi-plus  font-size-16"></i>
                                    </a>
                                <?php } else { 
                                         if($item->UnderTdsAmount == "0") { ?>
                                        <a target="_blank" href="<?php echo $resList->EditLink . "?ArrivalID=" . $item->RmArrivalID ?>" class="mr-3 text-primary" data-placement="top" title="Edit Bill" data-original-title="Edit">
                                            <i class="mdi mdi-pencil  font-size-16"></i>
                                        </a> &nbsp;
                                    <?php } ?>
                                    <a target="_blank" href="<?php echo WORKING_URL ?>Purchase/Reports/T1/commission-bill-page1?ID=<?php echo $item->RmArrivalID ?>" class="mr-3 text-primary" data-placement="top" title="Print Bill" data-original-title="printer">
                                        <i class="mdi mdi-printer font-size-16"></i>
                                    </a> &nbsp;
                                    <a target="_blank" href="<?php echo WORKING_URL ?>Purchase/Reports/T1/commission-bill-page2?ID=<?php echo $item->RmArrivalID ?>" class="mr-3 text-primary" data-placement="top" title="Commission Bill" data-original-title="printer">
                                        <i class="mdi mdi-printer font-size-16"></i>
                                    </a>
                                    <?php 
                                        if($item->UnderTdsAmount == "0") { 
                                            if ($item->DeleteAccess) { ?>
                                        <button type="button" class="mr-3 text-primary btnDelete" data-id="<?php echo $item->RmArrivalID ?>" style="border: 0px; background: none;" title="Delete">
                                            <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                        </button>
                                    <?php   } 
                                        } 
                                    ?>
                                <?php } ?>

                            <?php } ?>

                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

<?php }
} ?>