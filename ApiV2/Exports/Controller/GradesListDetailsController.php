<?php

include_once '../Global/Util.php';
class GradesListDetailsController
{

    private $conn;
    private $db_table = "ProductionGrades";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getGradesListDetails()
    {
        $SqlFields = "Value,Options,GradeGroup";

        $sqlQuery = "SELECT   $SqlFields FROM " . $this->db_table . " ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayGradeList['SNO'] = ++$i;
            $ArrayGradeList['Type'] = $dataRow['Value'];
            $ArrayGradeList['Name'] = $dataRow['Options'];
            $ArrayGradeList['Group'] = $dataRow['GradeGroup'];
            $Data[] = $ArrayGradeList;

        }
        return $Data;
    }

}
?>