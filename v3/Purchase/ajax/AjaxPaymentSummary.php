<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
    include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
    include_once ROOT.'config/api-call.php';

    $methodType = $_POST["methodType"];

    $api = API_RM_PAYMENT_SUMMARY_REPORT;

    $center = trim($_POST["center"]);
    $billerType = trim($_POST["billerType"]);
    $supplierID = trim($_POST["supplierID"]);
    $agentID = trim($_POST["agentID"]);
    $arrivalPlant = trim($_POST["arrivalPlant"]);
    $paymentMode = trim($_POST["paymentMode"]);
    $startDate = trim($_POST["startDate"]);
    $endDate = trim($_POST["endDate"]);
    $paymentId = trim($_POST["paymentId"]);

    $bodyContent = array(
        "CenterID" => $center,
        "BillerType" => $billerType,
        "SupplierID" => $supplierID,
        "AgentID" => $agentID,
        "Plant" => $arrivalPlant,
        "PaymentMode" => $paymentMode,
        "StartDate" => $startDate,
        "EndDate" => $endDate,
        "PaymentID" => $paymentId
    );

    if(isset($paymentId) && !empty($paymentId)) {
        $resList = CallAPI($api, "DELETE", "", $bodyContent);
    } else {
        $resList = CallAPI($api, "GET", "", $bodyContent);
    }

    // echo json_encode($resList, JSON_PRETTY_PRINT);

    // $error = $resList->Error;
     $List = $resList->Data->PaymentDateWiseReportData;

    if(isset($resList) && !empty($resList)) {
        $error = $resList->Error;

        if (!isset($error) || empty($error)) {
            $List = $resList->Data->PaymentDateWiseReportData;

            if($billerType==1){
                $Biller='Agent';
            }

            else{
                $Biller='Supplier';
            }

    ?>
                                                
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>S.No</th>	
                    <th>Pay Date</th> 
                     <th> <?php  echo $Biller; ?>  </th>                   
                    <th>Voucher</th>	
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php  foreach ($List as $item) { 
                    $PayDate=date('Y-m-d',strtotime($item->PayDate));
                    if($billerType==1){
                        $BillerName=$item->AgentName;
                    }
        
                    else{
                        $BillerName=$item->Supplier;
                    }
                    ?>
                        <td><?php  echo $item->SNO ?></td>	
                        <td><?php  echo $item->PayDate ?></td>                                          
                        <td><?php  echo $BillerName ?></td>
                        <td><?php  echo $item->Voucher ?></td>	
                        <td><?php  echo $item->Amount ?></td>
                        <td class="text-center">
                            <a target="_blank" href="<?php echo $item->PrintLink ."?SupplierID=" .$item->SupplierID. "&PayDate=".$PayDate."&Voucher=".$item->Voucher."&BillerType=".$billerType."&AgentID=".$agentID ?>" class="mr-3 text-primary"><i class="mdi mdi-printer  font-size-16"></i></a>
                            <?php  if ($resList->EditAccess) { ?>
                                <a target="_blank" href="<?php  echo $resList->EditLink."?BookingID=". $item->BookingID ?>" class="mr-3 text-primary" data-placement="top" title="Edit Booking" data-original-title="Edit">
                                    <i class="mdi mdi-pencil font-size-16"></i>
                                </a>
                            <?php  } ?>

                            <?php // if ($resList->DeleteAccess) { ?>
                                &nbsp;&nbsp;&nbsp;
                                <button class="mr-3 text-primary btnActions btnDelete" data-paymentid="<?php echo $item->PaymentID ?>" data-placement="top" title="Delete Booking" data-original-title="Delete">
                                    <i class="mdi mdi-delete text-danger font-size-16"></i>
                                </button> 
                            <?php // } ?>
                        </td>
                    </tr>
                <?php  } ?>
            </tbody>
        </table>

<?php 
        } else {
                echo "<div class='text-center mt-3 mb-3 text-bold'>NOTE : " . $error . "</div>";
            }
        } 

?>