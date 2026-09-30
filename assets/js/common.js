$(document).on("click", "#btnMobileMenu", function () {
  $("#topnav-menu-content").toggle();
  $(".topnav").toggleClass("showMenu");
});

$(".drpSelect").select2({
  width: "100%",
});

// $(document).on("select2:opening", ".select2", function () {
//     $(this).select2("open");
//     $(this).focus();
// });

$("#drpSupplierType").select2({
  dropdownParent: $("#modalCreateSupplier"),
});

$(document).ready(function () {
  $(".select2-selection").on("focus", function () {
    $(this).parent().parent().prev().select2("open");
  });
});

/* Jquery Custom Validator */
jQuery.validator.addMethod(
  "selectcheck",
  function (value) {
    return value != "0" || value != "";
  },
  "year required"
);

jQuery.validator.addMethod(
  "noSpace",
  function (value, element) {
    return value == "" || value.trim().length != 0;
  },
  "No space please and don't leave it empty"
);
/* END of Jquery Custom Validator */

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
  if (open == "T") {
    $("#modalConfirmation").modal("show");
  }

  if (status == "Confirmation") {
    $("#modalConfirmationLabel")
      .text("Confirmation")
      .removeClass("link-success")
      .removeClass("link-danger")
      .addClass("link-warning");
  } else if (status == "Success") {
    $("#modalConfirmationLabel")
      .text("Success")
      .removeClass("link-warning")
      .removeClass("link-danger")
      .addClass("link-success");
  } else if (status == "Error") {
    $("#modalConfirmationLabel")
      .text("Error")
      .removeClass("link-warning")
      .removeClass("link-success")
      .addClass("link-danger");
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

$(document).ready(function () {
  var submenu = $(".dropdown-item.active").first().text();
  // var addpage = $(".dropdown-item.active").attr("data-addpage");
  // var editpage = $(".dropdown-item.active").attr("data-editpage");
  var rootdir = $(".dropdown-item.active").attr("data-menu");

  //console.log(submenu, addpage, editpage, rootdir);

  if (submenu != "") {
    localStorage.setItem("submenu", submenu);
    // localStorage.setItem("addpage", addpage);
    // localStorage.setItem("editpage", editpage);
    localStorage.setItem("rootdir", rootdir);
  }

  var getRootdir = localStorage.getItem("rootdir");
  var getSubmenu = localStorage.getItem("submenu");

  // var getAddpage = localStorage.getItem("addpage");
  // var getEditpage = localStorage.getItem("editpage");

  // console.log(getSubmenu, getAddpage, getEditpage, getRootdir);

  $("#page-header").text(getSubmenu);
  $("#breadcum-root").text(getRootdir);
  $("#breadcum-next").text(getSubmenu);
});

function columnSum(classname, floatval) {
  var sum = 0;

  $(classname).each(function () {
    var str = $.trim($(this).text());
    val = str.replace(/\,|\$/g, "");

    sum += parseFloat(val);
  });
  return sum.toFixed(floatval);
}
