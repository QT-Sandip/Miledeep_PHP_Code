<?php
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once '../App/Controller/AppUtilController.php';

class RmTdsBillCalcBgProcessController
{
    private $conn;
    private $db_table = "ScrmApplicants";
    private $utilController;
    // private $Util;
    private $Error = null;
    private $Msg = '';
    public $i1 = 0;
    // Db connection
    public function __construct($db, $clientid)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn, $clientid);
        $this->appController = new AppUtilController($this->conn);
    }

    function updateAutoOtherBills($RequestBody)
    {
        $ClientID = $RequestBody['ClientID'];
        //error_log("This is a debug message");
        //error_log("received ArrivalID: " . $RequestBody['ArrivalID']);
        $ArrivalID = $RequestBody['ArrivalID'];
        $Billdate = date("Y-m-d", strtotime($RequestBody['BillDate']));
        $tdsLimit = 5000000;
        $Billdate1 = new DateTime($RequestBody['BillDate']);
        $TdsCalEndDate = new DateTime('2024-04-01');
        if ($Billdate1 < $TdsCalEndDate) {
            $tdsLimit = 0;
        }
        $start = microtime(true); // Execute query or function $end = microtime(true); $executionTime = $end - $start;
        $isEditBill = false;

        $sqlSupplierDetails = "SELECT GROUP_CONCAT(ID) as supplierIDs,PanNumber from RmSuppliers
        WHERE PanNumber in (SELECT RS.PanNumber from RmArrivalDetatils as RAD
        left Join RmSuppliers AS RS on RAD.RmSupplierID=RS.ID WHERE RAD.RmArrivalID=$ArrivalID)";
        $stmtSupplierDetails = $this->conn->prepare($sqlSupplierDetails);
        $stmtSupplierDetails->execute();
        $dataSupplierDetails = $stmtSupplierDetails->fetch(PDO::FETCH_ASSOC);
        $supplierIDs = $dataSupplierDetails['supplierIDs'];
        $checkPAN = $dataSupplierDetails['PanNumber'];

        $dataRequest['checkPAN'] = $checkPAN;
        $dataRequest['ArrivalID'] = $ArrivalID;
        $dataRequest['supplierIDs'] = $supplierIDs;
        $dataRequest['tdsLimit'] = $tdsLimit;
        $dataRequest['isEditBill'] = $isEditBill;
        $dataRequest['BillDate'] = $Billdate;

        // Check for Arrival IDs under TDS limit
        $aryArrivalUnderTdsLimit = $this->utilController->checkArrivalUnderTdsLimit($dataRequest);
        $isUnderTdsBill = $aryArrivalUnderTdsLimit['isUnderTdsLimit'];
        $aryUnderLimitArrivalIDs = $aryArrivalUnderTdsLimit['aryArrivalID'];

        //error_log("✅ Under TDS Limit Arrival IDs: " . print_r($aryUnderLimitArrivalIDs, true));

        $this->updateArrivalSet($aryUnderLimitArrivalIDs, $tdsLimit, $ClientID);
        //$this->queueTdsUpdate($aryUnderLimitArrivalIDs, $checkPAN, $Billdate, $tdsLimit, $ClientID);

        // Check if TDS limit is exceeded (Single Record)
        $dataRequest['limitRecords'] = false;
        $aryIsTdsExcessBill_Single = $this->utilController->isTdsLimitExceedBill($dataRequest);
        $isTdsLimitExceed_Single = $aryIsTdsExcessBill_Single['isTdsLimitExceeBill'];
        $aryExceedSingleArrivalIDs = $aryIsTdsExcessBill_Single['aryArrivalID'];

        //error_log("⚠️ Exceed TDS Limit (Single): " . print_r($aryExceedSingleArrivalIDs, true));

        $this->updateArrivalSet($aryExceedSingleArrivalIDs, $tdsLimit, $ClientID);
        //$this->queueTdsUpdate($aryExceedSingleArrivalIDs, $checkPAN, $Billdate, $tdsLimit, $ClientID);

        // Check if TDS limit is exceeded (Multiple Records Except 50L)
        $dataRequest['limitRecords'] = true;
        $aryIsTdsExcessBill_Multiple = $this->utilController->isTdsLimitExceedBill($dataRequest);
        $isTdsLimitExceed_Multiple = $aryIsTdsExcessBill_Multiple['isTdsLimitExceeBill'];
        $aryExceedMultipleArrivalIDs = $aryIsTdsExcessBill_Multiple['aryArrivalID'];

        //error_log("⚠️ Exceed TDS Limit (Multiple): " . print_r($aryExceedMultipleArrivalIDs, true));
        //error_log("⚠️ Exceed TDS Limit (Multiple): " . print_r($aryIsTdsExcessBill_Multiple, true));

        $this->updateArrivalSet($aryExceedMultipleArrivalIDs, $tdsLimit, $ClientID);
        //$this->queueTdsUpdate($aryExceedMultipleArrivalIDs, $checkPAN, $Billdate, $tdsLimit, $ClientID);
        // $end = microtime(true); $executionTime = $end - $start;
        // debug_report($executionTime);
        // error_log("Execution Time: $executionTime seconds");
        // $ArrivalIDTest=$RequestBody['ArrivalID'];
        // error_log("ArrivalID: $ArrivalIDTest");
    }

    private function updateArrivalSet($arrivalIDs, $tdsLimit, $ClientID)
    {
        foreach ($arrivalIDs as $ArrivalID) {
            //error_log("🔄 Updating Arrival ID: $ArrivalID and TDS limt:  $tdsLimit");

            $updateBillDetails = $this->utilController->updateBillTable($ArrivalID, $tdsLimit, $ClientID);
            $updateArrivalTds = $this->utilController->updateArrivalBillAmountandTds($ArrivalID);

            // Optional: log return values if needed
            // error_log("Bill update result: " . print_r($updateBillDetails, true));
            // error_log("Arrival TDS update result: " . print_r($updateArrivalTds, true));

            usleep(500000); // Sleep for 0.5 second to reduce load
        }
    }
    
    private function queueTdsUpdate($arrivalIDs, $pan, $billDate, $tdsLimit, $ClientID)
    {
        foreach ($arrivalIDs as $arrivalID) {
            $sql = "INSERT INTO tdsprocessingqueue (ArrivalID, PanNumber, BillDate, TdsLimit)
                    VALUES (:arrivalID, :pan, :billDate, :tdsLimit)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':arrivalID' => $arrivalID,
                ':pan'       => $pan,
                ':billDate'  => $billDate,
                ':tdsLimit' => $tdsLimit
            ]);
        }
        $this->processTdsQueue($ClientID);
    }

    public function processTdsQueue($ClientID)
    {
        

        // Fetch only one row per unique ArrivalID
        /*$sql = "SELECT MIN(ID) as ID, ArrivalID, TdsLimit 
                FROM tdsprocessingqueue 
                WHERE Status = 'pending' 
                GROUP BY ArrivalID 
                ORDER BY MIN(CreatedAt) 
                LIMIT 10";*/

        $sql = "SELECT * FROM `tdsprocessingqueue` WHERE Status = 'pending' ORDER by ID asc;";                

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($jobs as $job) {
            try {
                $arrivalID = $job['ArrivalID'];
                $tdsLimit  = $job['TdsLimit'];
                // Mark ALL entries for that ArrivalID as 'processing' and increment attempts
                $update = "UPDATE tdsprocessingqueue 
                           SET Status = 'processing', Attempts = Attempts + 1 
                           WHERE ArrivalID = :arrivalID AND Status = 'pending'";
                $stmtUpdate = $this->conn->prepare($update);
                $stmtUpdate->execute([':arrivalID' => $arrivalID]);

                // Run your actual business logic
                $this->utilController->updateBillTable($arrivalID, $tdsLimit,$ClientID); // default TDS limit
                $this->utilController->updateArrivalBillAmountandTds($arrivalID);

                // Mark ALL rows for that ArrivalID as done
                $done = "UPDATE tdsprocessingqueue 
                         SET Status = 'done', ProcessedAt = NOW() 
                         WHERE ArrivalID = :arrivalID AND Status = 'processing'";
                $stmtDone = $this->conn->prepare($done);
                $stmtDone->execute([':arrivalID' => $arrivalID]);

            } catch (Exception $e) {
                // On failure, mark all processing rows as failed
                $fail = "UPDATE tdsprocessingqueue 
                         SET Status = 'failed' 
                         WHERE ArrivalID = :arrivalID AND Status = 'processing'";
                $stmtFail = $this->conn->prepare($fail);
                $stmtFail->execute([':arrivalID' => $arrivalID]);

                // Optional: log the error
                error_log("TDS Processing failed for ArrivalID: $arrivalID. Error: " . $e->getMessage());
            }
        }

        //echo "All done";
    }

}
?>
