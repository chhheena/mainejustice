(function ($) {
  "use strict";

  $(function () {
    fixDesignerHeight();
    initDesigner();
  });

  function getColumnWidth(columns) {
    var columnWidth;

    switch (columns) {
      case 1:
        columnWidth = 600;
        break;
      case 2:
        columnWidth = 300;
        break;
      case 3:
        columnWidth = 200;
        break;
      case 4:
        columnWidth = 150;
        break;
      case 5:
        columnWidth = 120;
        break;
      case 6:
        columnWidth = 100;
        break;
      case 7:
        columnWidth = 90;
        break;
      case 8:
      case 9:
      case 10:
      case 11:
      case 12:
        columnWidth = 80;
        break;
      default:
        columnWidth = 150;
        break;
    }

    return columnWidth;
  }

  function getCellHeight(columns) {
    var cellHeight;

    switch (columns) {
      case 1:
      case 2:
      case 3:
      case 4:
        cellHeight = 150;
        break;
      case 5:
        cellHeight = 120;
        break;
      case 6:
        cellHeight = 100;
        break;
      case 7:
        cellHeight = 90;
        break;
      case 8:
      case 9:
      case 10:
      case 11:
      case 12:
        cellHeight = 80;
        break;
      default:
        cellHeight = 150;
        break;
    }

    return cellHeight;
  }

  function initDesigner() {
    var columns = parseInt($(".gridContainer ").data("columns"), 10);
    var columnWidth = getColumnWidth(columns);
    var cellHeight = getCellHeight(columns);

    // create array of elements for database use
    var dbElems = [];

    // generate html from saved dbElems
    var savedElements = window.mwvars.elements;
    var grid_html = createGridHtml(savedElements);

    $(".gridP").append(grid_html);

    // initialize grid
    var $grid = $(".gridP").packery({
      initLayout: false,
      resize: false,
      itemSelector: ".gridP-item",
      gutter: 5,
      columnWidth: columnWidth,
    });

    // prepare grid, iterate over all items
    $grid.find(".gridP-item").each(function (i, gridItem) {
      // make all grid-items draggable
      makeDraggable(i, gridItem);
      // fix items dimensions
      fixItemDimensions(gridItem, columnWidth, cellHeight);
    });

    // css is ready, show grid
    $(".gridP").show();
    $grid.packery();
    orderItems();

    // listen for layout operation
    $grid.on("layoutComplete", function (event) {
      orderItems();
    });

    // remove clicked element
    $grid.on("click", ".gridP-remove", function (event) {
      $grid
        .packery("remove", event.currentTarget.closest(".gridP-item"))
        // layout remaining item elements
        .packery("layout");
      orderItems();
    });

    // update columns
    $(".gridOptions").on("click", "#update-columns", function (event) {
      columns = parseInt($("#jform_columns").val(), 10);
      // add new column class
      $(".gridContainer")
        .removeClass(function (index, className) {
          return (className.match(/(^|\s)gridP-col-\S+/g) || []).join(" ");
        })
        .addClass("gridP-col-" + columns);

      // calculate new columnWidth/cellHeight
      columnWidth = getColumnWidth(columns);
      cellHeight = getCellHeight(columns);

      // iterate over all items
      $grid.find(".gridP-item").each(function (i, gridItem) {
        // check if item fits in grid. If not, decrement data-width until it fits
        var grid_width = $(".gridP").outerWidth();
        var item_width = $(gridItem).outerWidth();
        while (item_width > grid_width) {
          $(gridItem).attr("data-width", $(gridItem).attr("data-width") - 1);
          var new_data_width = parseInt($(gridItem).attr("data-width"), 10);
          item_width =
            new_data_width * columnWidth +
            (new_data_width - 1) * 5; /* n * columnWidth + gutters */
        }
        fixItemDimensions(gridItem, columnWidth, cellHeight);
      });

      // re-initialize grid with new columnWidth
      $grid = $(".gridP").packery({
        resize: false,
        itemSelector: ".gridP-item",
        gutter: 5,
        columnWidth: columnWidth,
      });
    });

    // select item to edit
    $grid.on("click", ".gridP-edit", function (event) {
      var editItem = event.currentTarget.closest(".gridP-item");
      var item_index = $(editItem).attr("data-index");
      var item_size = $(editItem).find(".item-size").text();
      $('a[href="#items"]').tab("show");
      $(".gridEditItem").show();
      $(".edit-index").text(item_index);
      document.getElementById("edit-size").value = item_size;
      document.getElementById("edit-height").value = parseInt(
        $(editItem).attr("data-height"),
        10
      );
      document.getElementById("edit-width").value = parseInt(
        $(editItem).attr("data-width"),
        10
      );
    });

    // edit item
    $("#edit-item").on("click", function () {
      var this_index = $(".gridEditItem").find(".edit-index").text();
      var new_size = document.getElementById("edit-size").value;
      var new_width = document.getElementById("edit-width").value;
      var new_height = document.getElementById("edit-height").value;
      var editItem = $('.gridP-item[data-index="' + this_index + '"]');
      $(editItem).attr("data-width", new_width);
      $(editItem).attr("data-height", new_height);

      // update classes
      $(editItem).removeClass();
      $(editItem).addClass("gridP-item");
      var size_class;
      switch (new_size) {
        case "S":
          size_class = "gridP-small";
          break;
        case "L":
          size_class = "gridP-landscape";
          break;
        case "P":
          size_class = "gridP-portrait";
          break;
        case "B":
          size_class = "gridP-big";
          break;
      }
      $(editItem).addClass(size_class);
      $(editItem).find(".item-size").text(new_size);

      fixItemDimensions(editItem, columnWidth, cellHeight);
      // check if item fits in grid. If not, decrement data-width until it fits
      var grid_width = $(".gridP").outerWidth();
      var item_width = $(editItem).outerWidth();
      while (item_width > grid_width) {
        $(editItem).attr("data-width", $(editItem).attr("data-width") - 1);
        var new_data_width = parseInt($(editItem).attr("data-width"), 10);
        item_width =
          new_data_width * columnWidth +
          (new_data_width - 1) * 5; /* n * columnWidth + gutters */
        fixItemDimensions(editItem, columnWidth, cellHeight);
      }
      $grid.packery("layout");
    });

    // add new item
    $("#add-item").on("click", function () {
      var appended_data_width = parseInt($("#item-width").val(), 10);
      var appended_data_height = parseInt($("#item-height").val(), 10);
      var appended_size = $("#item-size").val();
      var appended_class;
      switch (appended_size) {
        case "S":
          appended_class = "gridP-small";
          break;
        case "L":
          appended_class = "gridP-landscape";
          break;
        case "P":
          appended_class = "gridP-portrait";
          break;
        case "B":
          appended_class = "gridP-big";
          break;
      }

      // create new item elements
      var $items = $(
        "" +
          '<div class="gridP-item ' +
          appended_class +
          '" data-width="' +
          appended_data_width +
          '" data-height="' +
          appended_data_height +
          '">' +
          '<span class="item-number"></span> ' +
          '<span class="item-size">' +
          appended_size +
          "</span>" +
          '<div class="gridP-tools">' +
          '<span class="gridP-edit"><i class="fa fa-pencil"></i></span> ' +
          '<span class="gridP-remove"><i class="fa fa-times"></i></span>' +
          "</div>" +
          "</div>" +
          ""
      );

      fixItemDimensions($items, columnWidth, cellHeight);

      // append items to grid
      $grid
        .append($items)
        // add and lay out newly appended items
        .packery("appended", $items);
      // make all grid-items draggable
      $items.each(makeDraggable);
      $grid.packery("layout");
    });

    // reset input values when changing size
    $("#item-size").on("change", function () {
      var appended_size = $("#item-size").val();
      switch (appended_size) {
        case "S":
          $("#item-height").val(1);
          $("#item-width").val(1);
          break;
        case "L":
          $("#item-height").val(1);
          $("#item-width").val(2);
          break;
        case "P":
          $("#item-height").val(2);
          $("#item-width").val(1);
          break;
        case "B":
          $("#item-height").val(2);
          $("#item-width").val(2);
          break;
      }
    });

    // fix item dimensions
    function fixItemDimensions(gridItem, columnWidth, cellHeight) {
      // set items css width
      var data_width = parseInt($(gridItem).attr("data-width"), 10);
      var itemWidth =
        data_width * columnWidth +
        (data_width - 1) * 5; /* n * columnWidth + gutters */
      $(gridItem).css("width", itemWidth + "px");

      // set items css height
      var data_height = parseInt($(gridItem).attr("data-height"), 10);
      var itemHeight =
        data_height * cellHeight +
        (data_height - 1) * 5; /* n * cellHeight + gutters */
      $(gridItem).css("height", itemHeight + "px");
    }

    // re-order items
    function orderItems() {
      dbElems = [];
      var itemElems = $grid.packery("getItemElements");
      $(itemElems).each(function (i, itemElem) {
        $(itemElem)
          .find(".item-number")
          .text(i + 1);
        $(itemElem).attr("data-index", i + 1);

        var elem = {};
        elem.class = $(itemElem).attr("class");
        elem.width = $(itemElem).attr("data-width");
        elem.height = $(itemElem).attr("data-height");
        elem.index = $(itemElem).attr("data-index");
        elem.size = $(itemElem).find(".item-size").text();

        // add elem to dbElems
        dbElems.push(elem);
      });

      // update form elements textarea
      $("#jform_elements").val(JSON.stringify(dbElems));
    }

    // make item draggable
    function makeDraggable(i, itemElem) {
      // make element draggable with Draggabilly
      var draggie = new Draggabilly(itemElem);
      // bind Draggabilly events to Packery
      $grid.packery("bindDraggabillyEvents", draggie);
      // order and layout after drag
      var $draggable = $(".gridP-item").draggabilly();
      $draggable.on("dragEnd", function () {
        $grid.packery();
        $(".gridEditItem").hide();
      });
    }

    // create grid html
    function createGridHtml(elements) {
      var html = "";
      $(elements).each(function (i, elem) {
        var elem_html = "";
        elem_html +=
          '<div class="' +
          elem.class +
          '" data-width="' +
          elem.width +
          '" data-height="' +
          elem.height +
          '" data-index="' +
          elem.index +
          '">';
        elem_html += '<span class="item-number">' + elem.index + "</span> ";
        elem_html += '<span class="item-size">' + elem.size + "</span>";
        elem_html += '<div class="gridP-tools">';
        elem_html +=
          '<span class="gridP-edit"><i class="fa fa-pencil"></i></span> ';
        elem_html +=
          '<span class="gridP-remove"><i class="fa fa-times"></i></span>';
        elem_html += "</div>";
        elem_html += "</div>";
        html += elem_html;
      });

      return html;
    }
  }

  function fixDesignerHeight() {
    var h = window.innerHeight;
    var nav_h = $("nav.navbar").outerHeight();
    var header_h = $("header.header").outerHeight();
    var subhead_h = $(".subhead-collapse").outerHeight();
    var status_h = $("#status").outerHeight();

    var designer_h = h - nav_h - header_h - subhead_h - status_h + 2;

    $(".gridEditor").css("height", designer_h + "px");
  }

  // Handle resize
  var id;
  $(window).resize(function () {
    clearTimeout(id);
    id = setTimeout(doneBrowserResizing, 200);
  });

  function doneBrowserResizing() {
    fixDesignerHeight();
  }
})(jQuery);
