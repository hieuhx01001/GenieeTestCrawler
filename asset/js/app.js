/**
 * Created by hieuhoang on 6/2/16.
 */
"use strict";
var Scraper = function () {
    var handleClickScrapData = function () {

        $("#btn-update").on('click', function (e) {
            // reset fetch data container every time click scrap
            $("#fetch-data").html('');

            // open block UI before ajax call
            $.blockUI({
                message: '<h1  class="text-center ">Just a moment...</h1>',
                css: { backgroundColor: '#5bc0de', borderColor: '#5bc0de', color: '#fff'}
            });

            // ajax fetching data
            $.ajax({
                    url: 'data.php'
                }).done(function (response) {
                    // unblock UI
                    $.unblockUI();
                    $("#fetch-data").html(response);
                }).fail(function() {
                    // unblock UI
                    $.unblockUI();
                    $("#fetch-data").html('');
                    alert("Opps, Some thing went wrong!");
                });
        });
    };

    return {
        init: function () {
            handleClickScrapData();
        }
    }
}();


$(document).ready(function () {
    Scraper.init();
});
