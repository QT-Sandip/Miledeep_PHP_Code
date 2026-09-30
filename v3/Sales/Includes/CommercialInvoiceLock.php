<?php
$ClientConfigData = $_SESSION['ClientConfigData'];
$sales_edit_delte_disabled = $ClientConfigData['sales_edit_delte_disabled'];
if (!function_exists('isCompletedCommercialInvoiceStatus')) {
    function isCompletedCommercialInvoiceStatus($status)
    {
        $status = strtolower(trim((string) $status));

        return in_array($status, array('1', 'completed'), true);
    }
}

if (!function_exists('isCompletedRealizationStatus')) {
    function isCompletedRealizationStatus($status)
    {
        $status = trim((string) $status);

        return $status === '1';
    }
}

if (!function_exists('isRejectedCommercialInvoiceStatus')) {
    function isRejectedCommercialInvoiceStatus($status)
    {
        $status = trim((string) $status);

        return in_array($status, array('Rejected', 'Cancelled', 'Destroyed', 'Container ROB', 'Return Container'), true);
    }
}

if (!function_exists('isCommercialInvoiceLockedByDisplayStatus')) {
    function isCommercialInvoiceLockedByDisplayStatus($displayStatus)
    {
        return isCompletedCommercialInvoiceStatus($displayStatus);
    }
}

if (!function_exists('getCommercialInvoiceLockMessage')) {
    function getCommercialInvoiceLockMessage($action)
    {
        return 'Invoices with completed realization cannot be ' . $action . '.';
    }
}

if (!function_exists('isCommercialInvoiceLockedByPiId')) {
    function isCommercialInvoiceLockedByPiId($iconn, $piId)
    {
        global $sales_edit_delte_disabled;
        if(!$sales_edit_delte_disabled){
            return false;
        }
        $piId = trim((string) $piId);
        if ($piId === '') {
            return false;
        }

        $sql = "SELECT SRD.RezStatus
            FROM ScrmPoData SPD
            LEFT JOIN ScrmRealizationDetails SRD ON SRD.PoID = SPD.PoID
            WHERE SPD.PoID = '$piId'
            LIMIT 1";
        $stmt = $iconn->query($sql);
        if (!$stmt) {
            return false;
        }

        $row = $stmt->fetch_array();
        if (!$row) {
            return false;
        }

        return isCompletedRealizationStatus(isset($row['RezStatus']) ? $row['RezStatus'] : '');
    }
}

if (!function_exists('isCompletedCollection')) {
    function isCompletedCollection($iconn, $piId){
        global $sales_edit_delte_disabled;
        if(!$sales_edit_delte_disabled){
            return false;
        }
        $piId = trim((string) $piId);
        if ($piId === '') {
            return false;
        }

        $sql = "SELECT POD.PoID,
            POD.ExporterID,
            POD.ProcessorID,
            POD.PoNO,
            SCD.ConsigneeName,
            SCI.CustomsInvNo as CInvNo,
            (Select BankRefNo from ScrmCollectionBreakUP  where PIID=POD.PoID order by ColBrkID  desc limit 1) as BankRefNo,
            SCI.CInvDate FROM  ScrmCustomInvoices as SCI
                left join ScrmPoData as POD on SCI.PiID=POD.PoID
                left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
                left join ScrmISFDetails as SIFD on POD.PoID=SIFD.PIID
                left join ScrmCommercialData as SCOM on POD.PoID=SCOM.PoID
                left join ScrmPaymentCollections as SPC on POD.PoID=SPC.PIID
                left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID
                WHERE POD.PoID  in (SELECT PIID FROM `ScrmPaymentCollections` where Status=1 ) and  POD.Status not in (2,3,4) and POD.Status not in (3,7)
                and TransportedDate!='' and POD.PoID = '$piId'
                     and SCI.PaymentType='Collections'
            LIMIT 1";
        $stmt = $iconn->query($sql);
        if (!$stmt) {
            return false;
        }

        $row = $stmt->fetch_array();
        if (!$row) {
            return false;
        }
        $sql = "SELECT UtilizedDate FROM `ScrmCollectionBreakUP` WHERE PiID = '".$piId."' order by ColBrkID desc limit 1;";
        $stmt = $iconn->query($sql);
        if (!$stmt) {
            return false;
        }

        $row = $stmt->fetch_array();
        if (!$row) {
            return false;
        }

        $oneWeekLater = date('Y-m-d', strtotime($row['UtilizedDate'] . ' +23 week'));
        $currentdate = date("Y-m-d");
        if ($oneWeekLater > $currentdate) {
            return false;
        }
        return true;
    }
}

if( !function_exists('isOnlyCompletedCommercialInvoiceStatus') ){
    function isOnlyCompletedCommercialInvoiceStatus($iconn, $piId){
        global $sales_edit_delte_disabled;
        if(!$sales_edit_delte_disabled){
            return false;
        }
        $piId = trim((string) $piId);
        if ($piId === '') {
            return false;
        }
        $sql = "
            SELECT
                POD.PoID,

                POD.PoNo,

                POD.InvNumFull,

                SCI.CustomsInvNo as CInvNo,

                SCI.CInvDate,

                SCI.StockOutDate,

                SCOM.CommercialID,

                SCD.ConsigneeName, ScID.FDAContainerStatus FROM ScrmPoData as POD

                left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID

                left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID

                left join ScrmCommercialData as SCOM on POD.PoID=SCOM.PoID

                LEFT JOIN ScrmISFDetails AS ScID ON POD.PoID=ScID.PIID

                WHERE  SCI.PiID  in (SELECT PoID FROM `ScrmCommercialData`) and  POD.Status not in (2,3,4)
                and SCI.PiID = '".$piId."'
                LIMIT 1";

            $stmt = $iconn->query($sql);
            if (!$stmt) {
                return false;
            }

            $dataRow = $stmt->fetch_array();
            if (!$dataRow) {
                return false;
            }
            $rejectedStatusArray = ["Rejected", "Cancelled", "Destroyed", "Container ROB", "Return Container"];

            $FDAContainerStatus = $dataRow['FDAContainerStatus'];

            if ($FDAContainerStatus != '' && in_array($FDAContainerStatus, $rejectedStatusArray)) {

                //$PoStatus = $dataRow['FDAContainerStatus'];

                //$CLR = 'red';
                return false;

            } else if ($dataRow['CommercialID'] == '') {

                //$PoStatus = 'Pending';

                //$CLR = 'red';
                return false;

            } else if ($dataRow['CommercialID'] != '') {

                //$PoStatus = 'Completed';

                //$CLR = 'Green';
                return true;
            }
        return true;
    }
}
