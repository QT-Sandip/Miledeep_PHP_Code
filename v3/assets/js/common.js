$(document).on("click", "#btnMobileMenu", function() {
    debugger
    $("#topnav-menu-content").toggle();
    $(".topnav").toggleClass("showMenu");
});

$('.drpSelect').select2({
    width: '100%'
});


/* Jquery Custom Validator */
jQuery.validator.addMethod('selectcheck', function (value) {
    return (value != '0' || value != '');
}, "year required");

jQuery.validator.addMethod("noSpace", function(value, element) { 
    return value == '' || value.trim().length != 0;  
}, "No space please and don't leave it empty");
/* END of Jquery Custom Validator*/


/* When Ajax called and Ajax Completed the operation */
$(document).ajaxStart(function () {
    document.getElementById("preloader").style.display = "block";
    document.getElementById("status").style.display = "block";
});
$(document).ajaxComplete(function () {
    document.getElementById("preloader").style.display = "none";
    document.getElementById("status").style.display = "none";
});
/* END */


/* Modal Popup for Confimation */
function fnShowModalConfirmation(
    status,
    desc,
    btnClose,
    btnContinue,
    selectedID, 
    open
) {
    // var myModal = new bootstrap.Modal(
    //     document.getElementById("modalConfirmation"),
    //     {
    //         keyboard: false,
    //     }
    // );

    // var modalShow = document.getElementById("toggleMyModal"); // relatedTarget
    // myModal.show(modalShow);
    if(open == "T"){
        $("#modalConfirmation").modal("show");
    }

    if (status == "Confirmation") { 
        $("#modalConfirmationLabel").text("Confirmation").removeClass("link-success").removeClass("link-danger").addClass("link-warning");
    } else if (status == "Success") {
        $("#modalConfirmationLabel").text("Success").removeClass("link-warning").removeClass("link-danger").addClass("link-success");        
    }else if (status == "Error") {
        $("#modalConfirmationLabel").text("Error").removeClass("link-warning").removeClass("link-success").addClass("link-danger");
    }

    $("#hdnSelectedData").val(selectedID);
    $("#modalDesc").text(desc);
    $("#btnModalCancel").text(btnClose);

    if (btnContinue.length > 0) {
        $("#btnModalContinue").show();
        $("#btnModalContinue").text(btnContinue);
    } else {
        $("#btnModalContinue").hide();
    }

}
/* END */