<?php 
$PageTitle="MPEDA Report";
$PageTitleFolder="Reports";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");


?>

<!doctype html>
<html lang="en">


<link href="<?php echo  $href ?>"  rel="stylesheet" type="text/css" />
    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>

        <!-- DataTables -->
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-select-bs4/css//select.bootstrap4.min.css" rel="stylesheet" type="text/css" />

        <!-- Responsive datatable examples -->
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />     


		<link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css" rel="stylesheet">
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet" />
        <?php include ($IncludesPath."head-css.php");?>

        <style>

            @media print {
                @page {
                    size: A4;
                    margin: 10; 
                }
                .generate{
                    display: none;
                }
            }
            body{
                font-family: "Helvetica";
                font-size: 14px;
            }
            h1{
                font-size: 16px;
            }
            .generate, .generate:hover{
                cursor: pointer;
                border: 0px;
                background: #1a996a;
                color: #FFFFFF;
                padding: 10px 15px;
                font-weight: bolder;
                font-size: 14px;
            }
            .tblMain{
                width: 980px;
                margin: 0 auto;
            }
            table{
                border-collapse: collapse;
                border: 0px;
            }
            td{
                padding: 5px;
                vertical-align: top;
            }
            .w50{
                width: 50%
            }
            .w100{
                width: 100%;
            }
            .d-block{
                display: block;
            }
            .p-0{
                padding: 0px;
            }
            .mb-3{
                margin-bottom: 3px;
            }
            .bt2{
                border-top: 2px solid #000000;
            }
            .br2{
                border-right: 2px solid #000000;
            }
            .bb2{
                border-bottom: 2px solid #000000;
            }
            .bl2{
                border-left: 2px solid #000000;
            }

            .bt{
                border-top: 1px solid #000000;
            }
            .br{
                border-right: 1px solid #000000;
            }
            .bb{
                border-bottom: 1px solid #000000;
            }
            .bl{
                border-left: 1px solid #000000;
            }
            .f-8{
                font-size: 8px;
            }
            .f-10{
                font-size: 10px;
            }
            .f-12{
                font-size: 12px;
            }
            .f-13{
                font-size: 13px;
            }
            .f-14{
                font-size: 14px;
            }
            .f-15{
                font-size: 15px;
            }
            .f-16{
                font-size: 16px;
            }
            .f-17{
                font-size: 17px;
            }
            .f-18{
                font-size: 18px;
            }
            .bold{
                font-weight: 600;
            }
            .bolder{
                font-weight: bolder;
            }
            .page-title{
                text-align: center;
                font-size: 20px;
                margin: 10px;
                font-weight: 600;
            }
            .text-center{
                text-align: center;
            }
            .text-left{
                text-align: left;
            }
            .visible-hide{
                visibility: hidden;
            }
            .trBreakUpHeader th{
                padding: 7px 5px;
            }
            .tblHeader{

            }
            .tblBody{

            }
            .tblFooter{

            }
            table td, table th{
                border: 0px;
            }
        </style>    


    </head>

    <!--<body data-sidebar="dark" class="sidebar-enable vertical-collpsed">-->
    <body data-sidebar="dark" class="sidebar-enable">
	
	 <?php include ($IncludesPath."PreLoader.php"); ?>
	


        <!-- Begin page -->
        <div id="layout-wrapper">

            <?php
			include ($IncludesPath."topbar.php");
			include ($IncludesPath."sidebar.php"); 
			?>

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body" id="ShowDataTable">
                                        
                                        

                                        
                                                <div  class="pdf-content" id="printArea">
                                                    <div style="text-align: center">
                                                        <a href="Demohtml" target="_blank" class="generate" >Get PDF / PRINT </a>
                                                        <!-- <button class="generate" id="generate">Get PDF / PRINT </button> -->
                                                    </div>
                                                    <h1 class="page-title">PROFORMA INVOICE</h1>

                                                    <table class="bt2 bl2 br2 bb2 tblMain" >
                                                        <tr>
                                                            <td class="p-0 tblHeader">
                                                                <table class="w100" >
                                                                    <tr class="bb">
                                                                        <td class="br w50">
                                                                            <span class="f-14 mb-3 d-block">Exporter:</span>
                                                                            <span class="bold">M/s.COASTAL AQUA PRIVATE LIMITED <br>
                                                                            68-6-7/1, ASHOK NAGAR, KAKINADA - 533003<br>
                                                                            ANDHRA PRADESH, INDIA</span>
                                                                        </td>
                                                                    
                                                                        <td class="p-0">
                                                                            <table class="w100">
                                                                                <tr>
                                                                                    <td class="br bb" style="width: 70%">
                                                                                        Invoice No. & Date <br>
                                                                                        <b>CA/104/2023-24, DT: 03.05.2023</b>
                                                                                    </td>
                                                                                    <td class="bb f-12">Exporter's Ref: <br>
                                                                                        I.E.CODE: 2611000417
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td colspan="2" class="f-14">
                                                                                        Buyer's Order No. & Date:<br>
                                                                                        Other Reference(s) :
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td class="br bb">
                                                                            <span class="mb-3 d-block f-12">Consignee:</span>
                                                                            <span class="bold">
                                                                                MPO GLOBAL TRADE GMBH <br>
                                                                                ALTER WANDRAHM 15 <br>
                                                                                20457 HAMBURG <br>
                                                                                GERMANY. <br>
                                                                            </span>
                                                                        </td>
                                                                        <td class="p-0 bb">
                                                                            <table class="w100 p-0">
                                                                                <tr>
                                                                                    <td class="f-12 bb">
                                                                                        Buyer (if other than consignee):
                                                                                        <br><br>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="bolder bb f-13">
                                                                                        PROCESSED & PACKED BY: M/s.COASTAL AQUA PRIVATE LIMITED <br>
                                                                                        1-164, PATHARLAGADDA, KAKINADA-533468,ANDHRA PRADESH, INDIA
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="bolder f-13">
                                                                                        EU APPROVAL No.1866
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="p-0 br"> 
                                                                            <table class="w100">
                                                                                <tr>
                                                                                    <td class="bb br">Pre-carriage by </td>
                                                                                    <td class="bb">Place of Receipt by pre-carrier</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="bb">REFRIGERATED CONTAINER</td>
                                                                                    <td class="">Port of loading</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="bb br">Vessel/Voyage -</td>
                                                                                    <td class="bb">ANY INDIAN PORT</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="bb br">Port of Discharge: HAMBURG</td>
                                                                                    <td class="bb">Final Destination: HAMBURG</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="br">
                                                                                        Marks & Nos./ <br>
                                                                                        Container No
                                                                                    </td>
                                                                                    <td>
                                                                                        No. & Kind of Packages<br>
                                                                                        2016 <br>
                                                                                        MASTER CARTONS
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                        <td class="p-0 ">
                                                                            <table class="w100">
                                                                                <tr>
                                                                                    <td class="br bb">
                                                                                        Country of origin of goods <br> INDIA
                                                                                    </td>
                                                                                    <td class="bb">
                                                                                        Country of final Destn. <br> GERMANY
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td colspan="2">
                                                                                        Terms of Delivery and Payment
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td colspan="2">
                                                                                        <span class="bold">LC AT SIGHT</span>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td colspan="2">
                                                                                        <span class="f-18 bold">SHIPMENT : BY JUNE 2023</span>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="p-0 tblBody">
                                                                <table class="w100  text-center" >

                                                                    <tr class="text-center bb f-12 trBreakUpHeader">
                                                                        <th class="br bt text-left" style="width: 50%">Description of goods:</th>
                                                                        <th class="br bt" style="width: 10%">Grade</th>
                                                                        <th class="br bt" style="width: 5%">Cases</th>
                                                                        <th class="br bt" style="width: 10%">Net Weight <br> in Kgs</th>
                                                                        <th class="br bt" style="width: 10%">Frozen Weight <br> in Kgs</th>
                                                                        <th class="br bt" style="width: 5%">Rate <br> per Kg</th>
                                                                        <th class=" bt" style="width: 10%">Amount <br> in US$</th>
                                                                    </tr>

                                                                    <tr>
                                                                        <td class="p-0 br text-left" > 
                                                                            FROZEN RAW HEADLESS SHELL ON <span class="bold">EASY PEEL</span> VANNAMEI SHRIMPS IQF <br>
                                                                            SPECIES : LITOPENAEUS VANNAMEI <br>
                                                                            PACKING: 10X1KG, 25% GLAZE, 750GM NET WT <br>
                                                                            <span class="bold">BRAND : mpOcean</span>
                                                                            <br><br>
                                                                        </td>
                                                                        <td class="br ">21/25</td>
                                                                        <td class="br ">392 </td>
                                                                        <td class="br ">2940.000</td>
                                                                        <td class="br ">3920.000</td>
                                                                        <td class="br visible-hide">&nbsp;</td>
                                                                        <td class="visible-hide">&nbsp;</td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td class="p-0 br text-left" > 
                                                                            FROZEN RAW HEADLESS SHELL ON <span class="bold">EASY PEEL</span> VANNAMEI SHRIMPS IQF <br>
                                                                            SPECIES : LITOPENAEUS VANNAMEI <br>
                                                                            PACKING: 10X1KG, 25% GLAZE, 750GM NET WT <br>
                                                                            <span class="bold">BRAND : mpOcean</span>
                                                                            <br><br>
                                                                        </td>
                                                                        <td class="br ">21/25 <br> 31/40 <br> 41/50 <br> 71/90</td>
                                                                        <td class="br ">392 <br> 392 <br> 392 <br> 504</td>
                                                                        <td class="br ">2940.000 <br> 2940.000 <br> 2940.000 <br> 3780.000</td>
                                                                        <td class="br ">3920.000 <br> 3920.000 <br> 3920.000 <br> 5040.000</td>
                                                                        <td class="br visible-hide">&nbsp;</td>
                                                                        <td class="visible-hide">&nbsp;</td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td class="p-0 br text-left" > 
                                                                            MPO COUNT,REALY BIG & DARK COLOUR <br>
                                                                        </td>
                                                                        <td class="br ">&nbsp;</td>
                                                                        <td class="br ">&nbsp; </td>
                                                                        <td class="br ">&nbsp;</td>
                                                                        <td class="br ">&nbsp;</td>
                                                                        <td class="br visible-hide">&nbsp;</td>
                                                                        <td class="visible-hide">&nbsp;</td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td class="p-0 br bb" > 
                                                                            &nbsp;
                                                                        </td>
                                                                        <td class="br bt bb"><span class="bold"><span></td>
                                                                        <td class="br bt bb"><span class="bold">2016</span></td>
                                                                        <td class="br bt bb"><span class="bold">15120.000</span></td>
                                                                        <td class="br bt bb"><span class="bold">20160.000</span></td>
                                                                        <td class="br bt bb"><span class="bold"></span></td>
                                                                        <td class="bt bb"><span class="bold"></span></td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td class="p-0 br bb text-left" > 
                                                                            <table class="w100 bold">
                                                                                <tr>
                                                                                    <td>Net Weight in KGs </td>
                                                                                    <td>15120.000</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>Frozen Weight in KGs</td>
                                                                                    <td>20160.000</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>Gross Weight in KGs</td>
                                                                                    <td>23184.000</td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                        <td colspan="6" class="bb">&nbsp;</td>
                                                                    </tr>

                                                                    

                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="p-0">
                                                                <table class="w100 tblFooter" >
                                                                    <tr>
                                                                        <td colspan="7" class="text-left">Amount charagble (in words )(USD : ONLY ) </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td colspan="7" class="bold text-left">
                                                                            BANK DETAILS <br>
                                                                            STATE BANK OF INDIA,<br>
                                                                            COMMERCIAL BRANCH<br>
                                                                            #43-29-54/8, Narona Road<br>
                                                                            Balaji Metro Chambers, Dondaparthy<br>
                                                                            VISAKHAPATNAM - 530016<br>
                                                                            CURRENT A/C.No.38039983620<br>
                                                                            SWIFT: SBININBB745<br>
                                                                            OUR BANK CORRESPONDANCE CODE : SBI NEW YORK SBINUS33<br>
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td colspan="7" class="w100 bt p-0 text-left">
                                                                            <table>
                                                                                <tr>
                                                                                    <td class="br" style="width: 50%">
                                                                                        <br>
                                                                                        Declaration : <br>
                                                                                        We declare that this invoice shows the actual price of the<br>
                                                                                        goods described and that all particulars are true and correct.
                                                                                    </td>
                                                                                    <td style="width: 50%">
                                                                                        Signature & Date
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>

                                                </div> 









                                        </div> <!-- end card body-->
                                    </div> <!-- end card -->
                                </div><!-- end col-->
                            </div>
                        </div>
                        <!-- end row-->


                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->
                        
                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

<?php include ($IncludesPath."vendor-scripts.php"); ?>
      
<?php include ($IncludesPath."NewScripts.php"); ?>
<?php // include ($IncludesPath."ShowDataTable.php");?>
  

<script>
/*
    var style = "<?php echo '.page-title,.text-center{text-align:center}table,table td,table th{border:0}@media print{@page{size:A4;margin:10}.generate{display:none}}body{font-family:Helvetica;font-size:14px}.f-16,h1{font-size:16px}.generate{cursor:pointer;border:0;background:#1a996a;color:#fff;padding:10px 15px;font-weight:bolder;font-size:14px}.tblMain{width:980px;margin:0 auto}table{border-collapse:collapse}td{padding:5px;vertical-align:top}.w50{width:50%}.w100{width:100%}.d-block{display:block}.p-0{padding:0}.mb-3{margin-bottom:3px}.bt2{border-top:2px solid #000}.br2{border-right:2px solid #000}.bb2{border-bottom:2px solid #000}.bl2{border-left:2px solid #000}.bt{border-top:1px solid #000}.br{border-right:1px solid #000}.bb{border-bottom:1px solid #000}.bl{border-left:1px solid #000}.f-8{font-size:8px}.f-10{font-size:10px}.f-12{font-size:12px}.f-13{font-size:13px}.f-14{font-size:14px}.f-15{font-size:15px}.f-17{font-size:17px}.f-18{font-size:18px}.bold{font-weight:600}.bolder{font-weight:bolder}.page-title{font-size:20px;margin:10px;font-weight:600}.text-left{text-align:left}.visible-hide{visibility:hidden}.trBreakUpHeader th{padding:7px 5px}' ?>";
    $("#generate").on("click", function() {

        var contentToPrint = document.getElementById("printArea").innerHTML;
        var printWindow = window.open(); // A4 dimensions in pixels
        printWindow.document.write("<html><head><title>Print</title><style>"+style+"</style></head><body>");
        printWindow.document.write(contentToPrint);
        printWindow.document.write('</body></html>');
        printWindow.document.close();

        var fileName = "PROFORMA_INVOICE.pdf";
        printWindow.document.title = fileName;

        printWindow.print();    

        setTimeout(function() {
            printWindow.close();
        }, 500);

        // printWindow.addEventListener('load', function() {
        //     var printDocument = printWindow.document;
            
        //     // Create a Blob containing the HTML content
        //     var blob = new Blob([printDocument.documentElement.outerHTML], { type: 'application/pdf' });
            
        //     // Create a download link
        //     var downloadLink = document.createElement('a');
        //     downloadLink.href = URL.createObjectURL(blob);
        //     downloadLink.download = 'PROFORMA_INVOICE.pdf'; // Specify the desired file name
            
        //     // Trigger a click on the link to start download
        //     downloadLink.click();

        //     // Close the printWindow after a short delay (adjust as needed)
        //     setTimeout(function() {
        //         printWindow.close();
        //     }, 1000); // 1 second delay
        // });

    }); */
</script>
<!--         
<script>
    // show the Print Preview then Download PDF
    function printPage() {

         var selectedHtml = document.querySelector("#printArea").innerHTML; 


        var fileName = "PROFORMA_INVOICE.pdf";
        document.title = fileName;
        window.print(selectedHtml);        
    }

    // function printPage() {
    //     // Create a new Blob object from the HTML of the current page.
    //     var blob = new Blob([document.querySelector("html").innerHTML], { type: "application/pdf" });

    //     // Download the Blob object as a PDF file.
    //     var link = document.createElement("a");
    //     link.href = window.URL.createObjectURL(blob);
    //     link.download = "PROFORMA_INVOICE.pdf";
    //     link.click();
    // }

    const button = document.getElementById("generate");
    button.addEventListener("click", printPage);
</script> -->

</body>

</html>