<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$HeaderID = $_POST['HeaderID'];

$SqlSubMenus = "SELECT ID, Name FROM `ClientSideBarOptions` WHERE HeaderId='$HeaderID' ORDER BY Name";
$StmtSubMenus = $iconn->query($SqlSubMenus);

while($Row = $StmtSubMenus->fetch_array()) {
    echo '<option value="'.$Row['ID'].'">'.$Row['Name'].'</option>';
}

?>