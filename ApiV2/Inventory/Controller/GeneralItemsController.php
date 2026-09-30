<?php
include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class GeneralItemsController
{
    private $conn;
    private $Error = null;
    private $auditLogger;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Inventory');
    }

    public function postGeneralItems($RequestBody)
    {
        // echo print_r($RequestBody);exit;
        $generalItem_id = isset($RequestBody['generalItem_id']) ? $RequestBody['generalItem_id'] : '';
        $name = isset($RequestBody['name']) ? $RequestBody['name'] : '';
        $code = isset($RequestBody['code']) ? $RequestBody['code'] : '';
        $item_type = isset($RequestBody['item_type']) ? $RequestBody['item_type'] : '';
        $reorder_qty = isset($RequestBody['reorder_qty']) ? $RequestBody['reorder_qty'] : '';
        // $valuation_method = isset($RequestBody['valuation_method']) ? $RequestBody['valuation_method'] : '';
        // $group = isset($RequestBody['group']) ? $RequestBody['group'] : '';
        $revision_date = isset($RequestBody['revision_date']) ? $RequestBody['revision_date'] : '';
        $category = isset($RequestBody['category']) ? $RequestBody['category'] : '';
        // $item_make = isset($RequestBody['item_make']) ? $RequestBody['item_make'] : '';
        // $printer = isset($RequestBody['printer']) ? $RequestBody['printer'] : '';
        // $sub_category = isset($RequestBody['sub_category']) ? $RequestBody['sub_category'] : '';
        $description = isset($RequestBody['description']) ? $RequestBody['description'] : '';
        $image = isset($RequestBody['image']) ? $RequestBody['image'] : '';
        // $peridhable_item = isset($RequestBody['peridhable_item']) ? $RequestBody['peridhable_item'] : '';
        $default_base_unit = isset($RequestBody['default_base_unit']) ? $RequestBody['default_base_unit'] : '';
        $default_sales_unit = isset($RequestBody['default_sales_unit']) ? $RequestBody['default_sales_unit'] : '';
        $default_purchase_unit = isset($RequestBody['default_purchase_unit']) ? $RequestBody['default_purchase_unit'] : '';
        $weight = isset($RequestBody['weight']) ? $RequestBody['weight'] : 0;
        $height = isset($RequestBody['height']) ? $RequestBody['height'] : 0;
        $width = isset($RequestBody['width']) ? $RequestBody['width'] : 0;
        $length = isset($RequestBody['length']) ? $RequestBody['length'] : 0;
        $standard_cost = isset($RequestBody['standard_cost']) ? $RequestBody['standard_cost'] : '';
        // $overhead_cost = isset($RequestBody['overhead_cost']) ? $RequestBody['overhead_cost'] : '';
        $indirect_cost = isset($RequestBody['indirect_cost']) ? $RequestBody['indirect_cost'] : '';
        $profit = isset($RequestBody['profit']) ? $RequestBody['profit'] : '';
        $expiring_days = isset($RequestBody['expiring_days']) ? $RequestBody['expiring_days'] : '';
        $gst = isset($RequestBody['gst']) ? $RequestBody['gst'] : '';
        // $abc_value = isset($RequestBody['abc_value']) ? $RequestBody['abc_value'] : '';
        // $abc_margin = isset($RequestBody['abc_margin']) ? $RequestBody['abc_margin'] : '';
        // $abc_revenue = isset($RequestBody['abc_revenue']) ? $RequestBody['abc_revenue'] : '';
        // $abc_carying_cost = isset($RequestBody['abc_carying_cost']) ? $RequestBody['abc_carying_cost'] : '';
        // $barcode1 = isset($RequestBody['barcode1']) ? $RequestBody['barcode1'] : '';
        // $barcode2 = isset($RequestBody['barcode2']) ? $RequestBody['barcode2'] : '';
        // $barcode3 = isset($RequestBody['barcode3']) ? $RequestBody['barcode3'] : '';


        $CurrentDateTime = getCurrentDateTime()['TimeStamp'];
        $existingItem = null;
        if ($generalItem_id != '') {
            $existingItem = $this->auditLogger->fetchRowById('GeneralItems', 'GeneralItemId', $generalItem_id);
        }
        if ($generalItem_id == '') {
            // INSERT

            $sql = "INSERT INTO GeneralItems (
                name, code, item_type, reorder_qty, revision_date, category,
                description, image, default_base_unit, default_sales_unit, default_purchase_unit, weight, height,
                width, length, standard_cost, expiring_days
            
            ) VALUES (
                '$name', '$code', '$item_type', '$reorder_qty', '$revision_date', '$category',
                '$description', '$image', '$default_base_unit', '$default_sales_unit', '$default_purchase_unit', '$weight', '$height',
                '$width', '$length', '$standard_cost', '$expiring_days'
            )";
            // echo $sql;exit;
        } else {
            // UPDATE
            $sql = "UPDATE GeneralItems SET
            name='$name', code='$code', item_type='$item_type', reorder_qty='$reorder_qty',
             revision_date='$revision_date', category='$category',
            `description`='$description', `image`='$image', default_base_unit='$default_base_unit',
            default_sales_unit='$default_sales_unit', default_purchase_unit='$default_purchase_unit', weight='$weight', height='$height',
            width='$width', length='$length', standard_cost='$standard_cost', expiring_days='$expiring_days'
            
            WHERE GeneralItemId=$generalItem_id";
        }

        // echo $sql;exit;
        $stmt = $this->conn->prepare($sql);
        if ($stmt->execute()) {
            $LastID = $generalItem_id == '' ? $this->conn->lastInsertId() : $generalItem_id;
            $savedItem = $this->auditLogger->fetchRowById('GeneralItems', 'GeneralItemId', $LastID);
            if ($generalItem_id == '') {
                $this->auditLogger->logInsert('GeneralItems', $LastID, $savedItem, $RequestBody, [
                    'ReferenceNo' => $code,
                    'Remarks' => 'Inventory general item created',
                ]);
            } else {
                $this->auditLogger->logUpdate('GeneralItems', $LastID, $existingItem, $savedItem, $RequestBody, [
                    'ReferenceNo' => $code,
                    'Remarks' => 'Inventory general item updated',
                ]);
            }
            $Data['Error'] = null;
            $Data['status'] = "success";
            $Data['Msg'] = "Item Saved Successfully";
            $Data['LastInsertedID'] = $LastID;
            $Data['Data'] = $this->loadGeneralItems($RequestBody);
        } else {
            $error = $stmt->errorInfo();
            $Data['Error'] = $error[2];
            $Data['status'] = "fail";
            $Data['Msg'] = "Error while saving General Item";
            $Data['Data'] = [];
        }

        return $Data;
    }

    // public function getGeneralItem($RequestBody)
    // {

    //     $generalItem_id = isset($RequestBody['generalItem_id']) ? $RequestBody['generalItem_id'] : '';
    //     $stmt = $this->conn->prepare("SELECT * FROM GeneralItems WHERE GeneralItemId=$generalItem_id");
    //     $stmt->execute();
    //     $GeneralItem = $stmt->fetch(PDO::FETCH_ASSOC);
    //     $Data['Error'] = null;
    //     $Data['status'] = "success";
    //     $Data['status'] = "fail";
    //     $Data['Msg'] = $GeneralItem ? "Success" : "No Item found";
    //     $Data['Data'] = (is_array($GeneralItem) || isset($GeneralItem)) ? $GeneralItem : [];

    //     return $Data;
    // }



    public function getGeneralItem($RequestBody)
{
    $generalItem_id = isset($RequestBody['generalItem_id']) ? $RequestBody['generalItem_id'] : '';

    if ($generalItem_id == '') {
        return [
            'Error' => 'Item ID Missing',
            'status' => 'fail',
            'Msg' => 'generalItem_id is required',
            'Data' => []
        ];
    }

    $stmt = $this->conn->prepare("SELECT * FROM GeneralItems WHERE GeneralItemId=:id");
    $stmt->bindParam(':id', $generalItem_id, PDO::PARAM_INT);
    $stmt->execute();
    $GeneralItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($GeneralItem) {
        return [
            'Error' => null,
            'status' => 'success',
            'Msg' => 'Success',
            'Data' => $GeneralItem
        ];
    } else {
        return [
            'Error' => null,
            'status' => 'fail',
            'Msg' => 'No Item found',
            'Data' => []
        ];
    }
}

    public function deleteGeneralItem($RequestBody)
    {
        $generalItem_id = isset($RequestBody['generalItem_id']) ? $RequestBody['generalItem_id'] : '';
        $existingItem = $this->auditLogger->fetchRowById('GeneralItems', 'GeneralItemId', $generalItem_id);
        $stmt = $this->conn->prepare("DELETE FROM GeneralItems WHERE GeneralItemId=$generalItem_id");

        if ($stmt->execute()) {
            $this->auditLogger->logDelete('GeneralItems', $generalItem_id, $existingItem, $RequestBody, [
                'ReferenceNo' => isset($existingItem['code']) ? $existingItem['code'] : $generalItem_id,
                'Remarks' => 'Inventory general item deleted',
            ]);
            $Data['Error'] = null;
            $Data['status'] = "success";
            $Data['Msg'] = "Item deleted successfully";
            $Data['generalItem_id'] = $generalItem_id;
            $Data['Data'] = $this->loadGeneralItems($RequestBody);
        } else {
            $error = $stmt->errorInfo();
            $Data['Error'] = $error[2];
            $Data['status'] = "fail";
            $Data['Msg'] = "Error deleting Item";
            $Data['Data'] = [];
        }

        return $Data;
    }

    public function loadGeneralItems($RequestBody)
    {
        $stmt = $this->conn->prepare("SELECT GeneralItemId,`name`,code,item_type,category,standard_cost,default_base_unit,reorder_qty,expiring_days FROM GeneralItems ORDER BY GeneralItemId DESC");
        $stmt->execute();
        // $GeneralItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $GeneralItemsList = [];
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayGeneralItem['SNo'] = ++$i;
            $ArrayGeneralItem['generalItemId'] = $dataRow['GeneralItemId'];
            $ArrayGeneralItem['name'] = $dataRow['name'];
            $ArrayGeneralItem['code'] = $dataRow['code'];
            $ArrayGeneralItem['item_type'] = $dataRow['item_type'];
            // $ArrayGeneralItem['group'] = $dataRow['group'];
            $ArrayGeneralItem['category'] = $dataRow['category'];
            $ArrayGeneralItem['standard_cost'] = $dataRow['standard_cost'];
            $ArrayGeneralItem['default_base_unit'] = $dataRow['default_base_unit'];
            $ArrayGeneralItem['reorder_qty'] = $dataRow['reorder_qty'];
            $ArrayGeneralItem['expiring_days'] = $dataRow['expiring_days'];

            $GeneralItemsList[] = $ArrayGeneralItem;
        }

        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = "Success";
        $Data['Data'] = $GeneralItemsList;

        return $Data;
    }
}
