$(document).ready(function() {
    $('.checklist').dataTable( {
    "sDom": '<"top"if>rt<"bottom"lp<"clear">',
            "bPaginate": false,
            "bLengthChange": true,
            "bFilter": true,
            "bSort": true,
            "bInfo": true,
            "bAutoWidth": true} );

    $('span.CitedRecordSum').text($("td.CitedRecordCount").sum());
    $('span.OriginalRecordSum').text($("td.OriginalRecordCount").sum());

    var mapLocations = new Array();
    $("a.location").each(function (i) {
        var lat = $(this).find('span.lat').text();
        var lon = $(this).find('span.long').text();
        var uri = $(this).attr('about');

        if ((lat.length > 0) && (lon.length > 0)) {
            marker = {
                latitude: lat,
                longitude: lon
            }
            mapLocations.push(marker);
        }
    });

    $('div.speciesmap').height(300);
    $('div.speciesmap').gMap({
        markers: mapLocations,
        maptype: G_PHYSICAL_MAP,
        latitude: 41.989722,
        longitude: 43.59,
        zoom: 6
    });
});

