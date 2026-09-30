<?php
session_start();

if (!empty($_SESSION["MenuList"])) {
    $menuList = $_SESSION["MenuList"];

    $firstMenuItem = reset($menuList);
    $firstSubMenuItem = reset($firstMenuItem->SubMenu);
    $url = $firstSubMenuItem->Directory . $firstSubMenuItem->FileName;
    // echo $_SERVER['DOCUMENT_ROOT'] . '/v3' . '/' . $firstSubMenuItem->PathUrl;
    // if ($firstSubMenuItem->Directory != 'Sales') {
    //     header("Location: " . '/v3' . '/' . $firstSubMenuItem->PathUrl . '.php');
    //     exit;
    // } else {
    //     include $_SERVER['DOCUMENT_ROOT'] . '/v3' . '/' . $firstSubMenuItem->PathUrl . '.php';

    // }
    //  include $_SERVER['DOCUMENT_ROOT'] . '/v3/Sales/index_ex.php';
} else {
    // include $_SERVER['DOCUMENT_ROOT'] . '/v3/Sales/index_ex.php';
}


// if ($_SESSION["ClientID"] && $_SESSION["ClientID"] == 'DFLV3') {
//     if ($_SESSION["AccessType"] && $_SESSION["AccessType"] == '3') {
//         include $_SERVER['DOCUMENT_ROOT'] . '/v3/Sales/proforma-list.php';
//     } else {
//         include $_SERVER['DOCUMENT_ROOT'] . '/v3/Sales/dashboard.php';
//     }
// } else {
//     include $_SERVER['DOCUMENT_ROOT'] . '/v3/Sales/index_ex.php';
// }

// include $_SERVER['DOCUMENT_ROOT'] . '/v3/Sales/devi-dashboard.php';
// include 'po-list.php';


if ($_SESSION["AccessType"] && $_SESSION["AccessType"] == '1') {
    include $_SERVER['DOCUMENT_ROOT'] . '/v3/Sales/dashboard.php';

} else {
    include $_SERVER['DOCUMENT_ROOT'] . '/v3/Sales/proforma-list.php';
}
?>