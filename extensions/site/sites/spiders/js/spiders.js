$(document).ready(function() {
    $('table.checklist').dataTable( {
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
        var name = $(this).find('span.name').text();
        var html = '<p><strong about"'+ uri +'">' + name + '</strong></p>'
            +'<ul>'
            +'<li><a href="'+uri+'">go to place</a></li>'
            +'<li><a href="javascript:filterList(\''+name+'\')">filter list</a></li>'
            +'</ul>';

        var iconL1 = {
                    shadow:             false,
                    iconanchor:         [4, 19],
                    infowindowanchor:   [8, 2],
                    image:              "https://chart.googleapis.com/chart?chst=d_map_spin&chld=0.2|330|FF0000|1"
                };
        var iconL2 = {
                    shadow:             false,
                    iconanchor:         [4, 19],
                    infowindowanchor:   [8, 2],
                    image:              "https://chart.googleapis.com/chart?chst=d_map_spin&chld=0.2|330|FF00FF|1"
                };
        

        if ((lat.length > 0) && (lon.length > 0)) {
            m = {
                count: 1,
                html: html,
                latitude: lat,
                longitude: lon,
                icon: iconL1
            };
            mapLocations.push(m);
        }
    });

    $('div.speciesmap').height(300);
    $('div.speciesmap').gMap({
        markers: mapLocations,
        maptype: G_PHYSICAL_MAP,
        scrollwheel: false,
        latitude: 41.989722,
        longitude: 43.59,
        zoom: 6
    });
    
});

function filterList (name) {
    $('div.dataTables_filter').find('input').attr('value', name).trigger('keyup');
}