<?php  
    
	include_once 'Config/Config.php';
    include_once 'Controller/AuthLogin.php';
	
    $database = new Database();
    $restApi = new REST(); 
	$db = $database->getConnection(); 
    $LoginController = new AuthLogin($db); 
    
   $RequestBody = $restApi->getRequestBody(); 
  
    $reqMethod = $restApi->get_request_method(); 
    $json = $restApi->getRequestHeader(); 
	$itemCount= $LoginController->postAuthorization($RequestBody);
   

echo json_encode($itemCount);
/*
 if($itemCount > 0){       
        $employeeArr = array();
        $employeeArr["body"] = array();
        $employeeArr["itemCount"] = $itemCount;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);
            $e = array(
                "id" => $id,
                "name" => $name,
                "email" => $email,
                "age" => $age,
                "designation" => $designation,
                "created" => $created
            );
            array_push($employeeArr["body"], $row);
        }
        echo json_encode($employeeArr);
    }else{
        http_response_code(404);
        echo json_encode(
            array("message" => "No record found.")
        );
    }
	
	*/
?>
