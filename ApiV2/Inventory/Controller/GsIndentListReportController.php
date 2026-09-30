<?php

include_once '../Global/Util.php';
// include_once 'UtilController.php';

class GsIndentListReportController
{

    private $conn;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getGsIndentListReportDetails($RequestBody)
    {

        $StartDate = $RequestBody['StartDate'];
        $EndDate = $RequestBody['EndDate'];
        $status = $RequestBody['status'];

        $Gsindentlist = [];

        $sql = "SELECT PM.Id,
                       PM.DocNo,
                       PM.Date,
                       PM.Status
                FROM IndentDetailsGs AS PM
                WHERE 1=1";

        // Apply filters dynamically
        if (!empty($status) && strtolower($status) != 'all') {
            $Status = 0;
            if ($status == "Pending") {
                $Status = 1;
            } else if ($status == "Completed") {
                $Status = 2;
            } else {
                $Status = 0;
            }
            $sql .= " AND PM.Status = $Status";
        }

        if (!empty($StartDate) && !empty($EndDate)) {
            $sql .= " AND PM.Date BETWEEN '" . $StartDate . "' AND '" . $EndDate . "'";
        }

        // if (!empty($filters['doc_no'])) {
        //     $sql .= " AND PM.PoNo LIKE '" . $filters['doc_no'] . "'";
        // }

        $sql .= " ORDER BY PM.Id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $sn = 1;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Gsindentlist[] = [
                'SNo' => $sn++,
                'Date' => $row['Date'],
                'Status' => $row['Status'],
                'DocNo' => $row['DocNo'],
                'Id' => $row['Id']
            ];
        }

        $Data['Error'] = null;
        $Data['Msg'] = "success";
        $Data['Data'] = $Gsindentlist;

        return $Data;
    }


}
