/**
 * Created by hieuhoang on 6/2/16.
 */
"use strict";
var Scraper = function () {
    var initUpdateBtn = function () {
        $("#btn-update").on('click', function (e) {

            // reset fetch data container
            $("#fetch-data").html('');

            // open block UI
            $.blockUI({
                css: {
                    border: 'none',
                    padding: '10px',
                    backgroundColor: '#000',
                    '-webkit-border-radius': '10px',
                    '-moz-border-radius': '10px',
                    opacity: .5,
                    color: '#fff'
                }
            });

            var fetchingData = $.ajax("data.php");
            // handle call back
            fetchingData.done(function (res) {
                $.unblockUI();
                $("#fetch-data").html(res);
            });
            fetchingData.fail(function() {
                $.unblockUI();
                $("#fetch-data").html('');
                alert("Error!!!");
            });
        });
    };

    return {
        init: function () {
            initUpdateBtn();
        }
    }
}();


$(document).ready(function () {
    Scraper.init();
});
