<?php

include_once '../Global/Util.php';
class LinersListDetailsController
{

    private $conn;
    private $db_table = "ScrmLinerMaster";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getLinersListDetails()
    {
        $SqlFields = "LinerName,LinerAddress,ContactNo,ContainerTrackingUrl";

        $sqlQuery = "SELECT $SqlFields FROM " . $this->db_table . " ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 1;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayLinerList['SNO'] = $i;
            $ArrayLinerList['LinerName'] = $dataRow['LinerName'];
            $ArrayLinerList['Address'] = $dataRow['LinerAddress'];
            $ArrayLinerList['ContactNo'] = $dataRow['ContactNo'];
            $ArrayLinerList['TrackingUrl'] = $dataRow['ContainerTrackingUrl'];

            $Data[] = $ArrayLinerList;


            $i++;
        }
        return $Data;
    }

}
?>