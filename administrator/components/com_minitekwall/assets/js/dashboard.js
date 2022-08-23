(function ($) {
  "use strict";

  $(function () {
    checkForUpdate("auto");

    $("#check-version").on("click", function (event) {
      $(".update-box").remove();
      $("#check-version").find(".fa").addClass("fa-spin");
      checkForUpdate("check");
    });
  });

  function checkForUpdate(type) {
    $.ajax({
      type: "GET",
      url: "index.php?option=com_minitekwall&task=checkForUpdate&type=" + type,
      success: function (response) {
        $(".update-box").remove();
        $(".minitek-dashboard").prepend(response);
        $("#check-version").find(".fa").removeClass("fa-spin");
      },
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        console.log(errorThrown);
      },
    });
  }
})(jQuery);
