<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$apiMethod = trim($_POST["apiMethod"]);
$BillerType = trim($_POST["AgentType"]); //uma






if (isset($apiMethod) && !empty($apiMethod)) { // GET Method

    $api = API_PAYMENT_LIST;

    // exit;
    $ArrivalID = trim($_POST["ArrivalID"]);
    $SupplierID = trim($_POST["SupplierID"]);
    $BillDate = trim($_POST["BillDate"]);
    $AgentID = trim($_POST["AgentID"]);
    // $Type = trim($_POST["Type"]);

    $bodyContent = array(
        'ArrivalID' => $ArrivalID,
        'SupplierID' => $SupplierID,
        'BillDate' => $BillDate,
        'AgentID' => $AgentID,
        'BillerType' => $BillerType,
        'Type' => "1"
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);

    // echo json_encode($resList, )

    // var_dump($resList); exit;

    if (isset($resList) && !empty($resList)) {
        $error = $resList->Error;

        if (!isset($error) || empty($error)) {
            $List = $resList->Data;
            ?>

            <table id="tblPaymentMode" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th style="min-width: 80px;">Bill No.</th>
                        <th style="min-width: 80px;">Bill Date</th>
                        <th style="min-width: 120px;">Supplier Name</th>
                        <th style="min-width: 120px;">Agent Name</th>
                        <th style="min-width: 80px;">Actual Bill</th>
                        <th style="min-width: 70px;">TDS</th>
                        <th style="min-width: 80px;">Bill Amt</th>
                        <th style="min-width: 80px;">Already Paid</th>
                        <th style="min-width: 100px;">Balance Value</th>
                        <th style="min-width: 80px;">Amount</th>
                        <th style="min-width: 60px;">Remainings</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td>
                                <?php echo $item->ArrivalNumber ?>
                            </td>
                            <td>
                                <?php echo $item->BillDate ?>
                            </td>
                            <td>
                                <?php echo $item->SupplierName ?>
                            </td>
                            <td>
                                <?php echo $item->Agent ?>
                            </td>
                            <td>
                                <?php echo $item->BillAmount ?>
                            </td>
                            <td>
                                <?php echo $item->TDS ?>
                            </td>
                            <td>
                                <?php echo $item->BillAmt ?>
                            </td>
                            <td>
                                <?php echo $item->PaidAmount ?>
                            </td>
                            <td class="tdBalance">
                                <?php echo $item->BalanceAmount ?>
                            </td>
                            <td><input class="form-control txtAmount" id="txtAmount1" name="txtAmount1"
                                    data-arrivalid="<?php echo $item->RmArrivalID ?>" value="<?php echo $item->BalanceAmount ?>" /></td>
                            <td><input class="form-control txtRemaning" id="txtRemaning1" name="txtRemaning1" disabled /></td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th class="tdAmount"></th>
                        <th class="tdRemaning"></th>
                    </tr>
                </tfoot>
            </table>

            <?php
        } else {
            echo "<div class='text-center mt-3 mb-3 text-bold'>Note : " . $error . "</div>";
        }
    }


} else {
    if(isset($_POST["methodType"]) && !empty($_POST["methodType"])) {

        $api = API_RM_PAYMENT;
    
        $centerID = trim($_POST["centerID"]);
        $billerType = trim($_POST["billerType"]);
        $paymentDate = trim($_POST["paymentDate"]);
        $paymentMode = trim($_POST["paymentMode"]);
    
        $bodyContent = array(
            'CenterID' => $centerID,
            'BillerType' => $billerType,
            'PaymentDate' => $paymentDate,
            'PaymentMode' => $paymentMode
        );
    
        $resVocher = CallAPI($api, "GET", "", $bodyContent);
    
        // echo $resVocher->VoucherNo;
    
        $resResults = json_encode($resVocher);
    
        header('Content-Type: application/json');
        echo $resResults;
   
    } else { // POST Mrthod Method
    
    
    
        $api = API_RM_PAYMENT;
    
        $bodyContent = $_POST["jsonData"];
    
        // echo json_encode($bodyContent, JSON_PRETTY_PRINT); exit;
    
        $resPayment = CallAPI($api, "POST", "", $bodyContent);
    
    
        $resResults = json_encode($resPayment);
    
        header('Content-Type: application/json');
        echo $resResults;    
    
    }
}
?>