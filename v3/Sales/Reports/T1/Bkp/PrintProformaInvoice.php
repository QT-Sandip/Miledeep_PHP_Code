<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
 
include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';


?>
<!DOCTYPE html>
<html lang="en" id="content">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <link href="https://serp.silverlights.in/Clients/DFL/Sales/styles/InvoiceHtmlPdf.css" rel="stylesheet" />
    

</head>
<body>
        
    <div  class="pdf-content">
        <!-- <div style="text-align: center">
            <button class="generate" id="generate">Get PDF / PRINT </button>
        </div> -->
        <?php include_once 'ViewProformaInvoice.php' ?>   

    </div> 

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>

     <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.3.2/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script> -->

    <script>
        // show the Print Preview then Download PDF
        function printPage() {
            var fileName = "PROFORMA_INVOICE.pdf";
            document.title = fileName;
                window.print(); 
        }

        $(document).ready(function(){
            printPage();
            setTimeout(function() {
                window.close();
            }, 2000); 
        }); 

        // function printPage() {
        //     // Create a new Blob object from the HTML of the current page.
        //     var blob = new Blob([window.print()], { type: "application/pdf" });

        //     // Download the Blob object as a PDF file.
        //     var link = document.createElement("a");
        //     link.href = window.URL.createObjectURL(blob);
        //     link.download = "PROFORMA_INVOICE.pdf";
        //     link.click();
        // }

        // const button = document.getElementById("generate");
        // button.addEventListener("click", printPage);

        // $(document).on("click", "#generate", function(){
        //     printPage();
        //     setTimeout(function() {
        //         window.close();
        //     }, 1000); // 1 second delay
        // });

        //   $(document).ready(function() {
        //     // Capture the content as an image
        //     html2canvas($("#content")[0]).then(function(canvas) {
        //         // Convert the canvas to an image data URL
        //         var imgData = canvas.toDataURL("image/jpeg");

        //         // Create a PDF instance
        //         var pdf = new jsPDF();

        //         // Add the image to the PDF
        //         pdf.addImage(imgData, "JPEG", 10, 10, 190, 280); // Adjust the dimensions as needed

        //         // Download the PDF
        //         pdf.save("content.pdf");
        //     });
        // });



    </script>
    
</body>
</html>