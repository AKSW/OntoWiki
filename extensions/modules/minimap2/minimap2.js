$(document).ready(function() {
	
	var resources = $('.separated-vertical > tbody > tr > td > a.Resource');
	var extentParam = '';
	resources.each(function(i, r) {
		extentParam += $(r).attr('about') + ',';
	});
	extentParam = extentParam.substr(0, extentParam.length-1);
	var url = urlBase + 'map/marker/';
	
	var map = new GMap2(document.getElementById("miniMap2"));
	map.setCenter(new GLatLng(51.2, 12.23), 13);
	map.setUIToDefault();
	
	$.getJSON(url, {extent: extentParam, datatype: 'json'}, function(data) {
		for (var i=0; i<data.length; ++i) {
			map.addOverlay(new GMarker(new GLatLng(data[i].latitude, data[i].longitude), {icon: new GIcon(G_DEFAULT_ICON)}));
		}
    });
});
