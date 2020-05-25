(function ($) {

  // load state data from json
  $.getJSON("/map/map.json", function (data) {
    $.each(data.states, function (i, state) {
      $("#" + state.id).click(function (e) {
        displayInfo(state);
        $('#display-map').addClass('showing');
      }).addClass('active');
      displayTable(state);
      displayMainStates(state);
    });
    $("body").append(
      $("<div>").attr("id", "map-hover")
    );
  });

  // update map on resize
  $(window).resize(function () {
    positionLabels();
    positionMainStateLabels();
  });

  // close open state
  $("#map-info .close-button").click(function (e) {
    $('#map-labels').empty();
    $("#map-info ul").empty();
    $("#map-image .state").removeAttr("style");
    $('#map-info').hide();
    $("#map-image").removeClass("locked");
    $('.main-state-label').css('opacity', '1');
    $('#display-map').removeClass('showing');
  });

  // show hover display
  $(window).on('load', function () {
    $("#map-image .state.active").hover(function (e) {
      $('.main-state-label').addClass('hide');
      if ($(this).data('state-info') != "") {
        $("#map-hover")
          .show()
          .html($(this).data('state-info'));
      }
    }).mouseleave(function (e) {
      $("#map-hover").hide();
      $('.main-state-label').removeClass('hide');
    });

  });


  // hover info follow map
  $(document).mousemove(function (e) {
    $("#map-hover").css({
      'top': e.pageY + 130,
      'left': e.pageX + 150,
    });
  }).mouseover();

  // convert percent to color
  function percentageToColor(percent, max) {
    var dec = (percent / max);
    if (dec < 0.4) dec += 0.1;
    if(percent != 0){
      return "rgba(0, 103, 172, " + dec + ")";
    }
  }

  // adjust label positions
  function positionLabels() {
    var $parent, id, p1, p2;
    $parent = $('#map-labels');
    p2 = $parent.get(0).getBoundingClientRect();
    $parent.find('div').each(function (i, label) {
      id = "#" + $(label).data("state");
      p1 = $(id).get(0).getBoundingClientRect();
      $(label).css({
        "top": (p1.top - p2.top) + "px",
        "left": (p1.left - p2.left) + "px",
        "width": p1.width + "px",
        "height": p1.height + "px"
      });

      // position state outliers
      if (id == '#CA') {
        $(label).css({
          "left": (p1.left - p2.left - 10) + "px"
        });
      } else if (id == '#FL') {
        $(label).css({
          "left": (p1.left - p2.left + 50) + "px"
        });
      } else if (id == '#MI') {
        $(label).css({
          "top": (p1.top - p2.top + 20) + "px",
          "left": (p1.left - p2.left + 20) + "px"
        });
      } else if (id == '#OK') {
        $(label).css({
          "left": (p1.left - p2.left + 20) + "px"
        });
      } else if (id == '#TX') {
        $(label).css({
          "left": (p1.left - p2.left + 30) + "px"
        });
      }

    });
  }

  function positionMainStateLabels() {
    var $parent, id, p1, p2;
    $parent = $('#main-state-labels');
    p2 = $parent.get(0).getBoundingClientRect();
    $parent.find('div').each(function (i, label) {
      id = "#" + $(label).data("main-state");
      p1 = $(id).get(0).getBoundingClientRect();
      $(label).css({
        "top": (p1.top - p2.top) + "px",
        "left": (p1.left - p2.left) + "px",
        "width": p1.width + "px",
        "height": p1.height + "px"
      });
    });
  }

  // display state labels on main states
  function displayMainStates(state) {
    $("#main-state-labels").append(
      $("<div>")
      .attr({
        "id": "main-state-label-" + state.id,
        "data-main-state": state.id,
      })
      .append("<span class='main-state-label'>" + state.label + "</span>")
    );

    $("#" + state.id).attr("data-state-info", state.hover);

    positionMainStateLabels();

  }

  // display state data
  function displayInfo(state) {
    $('.main-state-label').css('opacity', '0');
    $("#map-image").addClass("locked");

    $("#map-info").show();
    $("#map-info h3").text(state.label);

    $.each(state.description, function (d, description) {
      $("#map-info ul").append("<li>" + description + "</li>");
    });

    // getting json data from the results.php page
    $.getJSON("/results.php", function (data) {
      var max;
      
      // sort data
      data.sort(stateSort)

      // push sorted percentages to get max
      var percs = [];
      $.each(data, function (i, item) { 
        if (state.id == item.map_destination) {
          percs.push(item.map_percentage);
        }
      });
      max = percs[0];

      // show data info
      var n = 0;
      $.each(data, function (i, item) {
        if (state.id == item.map_destination && item.map_percentage > 0 && item.map_source != "PR" && item.map_source != "VI") {
          n++;
          $("#map-labels").append(
            $("<div>")
            .addClass("label")
            .css("opacity", "0")
            .append("<span>" + item.map_percentage + "%</span>")
            .attr({
              "id": "info-" + item.map_source,
              "data-state": item.map_source
            })
          );
          positionLabels();

          setTimeout(function () {
            const color = percentageToColor(item.map_percentage, max);
            $("#" + item.map_source).css("fill", color);
            $("#info-" + item.map_source).css("opacity", "1");
          }, 20 * n);

        }
      });


    });


    $("#map-labels > #info-" + state.id + " > span").addClass(state.id);

  }

  // sort objects
  function stateSort(a, b) {
    if (a.map_percentage < b.map_percentage) {
      return 1;
    } else {
      return -1;
    }
  }

  // add table version
  function displayTable(state) {

    var $list = $("<ul>");
    var $table = $("<table>")
      .append(
        $("<tr>").append(
          $("<th>").text("State"),
          $("<th>").text("% of Providers")
        )
      );

    // getting json data from the results.php page
    $.getJSON("/results.php", function (data) {
      // sort data
      data.sort(stateSort);

      $.each(data, function (i, item) {
        if (state.id == item.map_destination && item.map_source != "PR" && item.map_source != "VI") {
          $table.append(
            $("<tr>").append(
              $("<td>").text(item.map_source),
              $("<td>").text(item.map_percentage + "%")
            )
          );
        }
      });
      
    });

    // add description text
    $.each(state.description, function (d, description) {
      $list.append("<li>" + description + "</li>");
    });

    var $header = $("<h3>")
      .text(state.label)
      .append('<span></span><span></span>')
      .click(function (e) {
        $(this).toggleClass("active");
        $(this).next().slideToggle("slow");
      });

    var $content = $("<div>")
      .addClass("state-data")
      .append(
        $list,
        $table
      );

    // add table and header
    $("#map-table").append(
      $header,
      $content
    );

  }

})(jQuery);