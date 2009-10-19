// vim: sw=4:sts=4:expandtab

function MapManager ( map ) {
    this.map        = map;
    this.init       = initMap;
    this.setHeight  = setMapHeight;
}	

// set the height for the map container and the detailmap
function setMapHeight() {
    
    var windowHeight = $(window).height();
    var windowTop  = $('.section-mainwindows').eq(0).offset().top;
    var contentTop = $('.section-mainwindows .window .content').eq(0).offset().top;
    var mapTop     = $('.section-mainwindows .window .content #mainMap').eq(0).offset().top;
    $("#mainMap").height(
        (windowHeight -         // all we can use
        mapTop -                // window title, tabs, etc.
        windowTop -             // assume same margin as top for the bottom
        (mapTop - contentTop)   // assume same content padding for the bottom
        ) * 0.99);              // we're not greedy ;)

	// make it sqare
	$("#detailMap").height( $("#detailMap").width() );
	// if issue #533 on safari happans use this dirty hack
	if ($("#detailMap").height() >= $("#mainMap").height()) {
		$("#detailMap").height($("#mainMap").height()/2);
	}
}

// initiate the map
function initMap ( ) {

	//alert("extent: l:"+extent.left+" b:"+extent.bottom+" r:"+extent.right+" t:"+extent.top );

	// initiate the maps
	if(this.map) {
		mainMap = this.map;
	} else {
		mainMap		= new OpenLayers.Map( $('#mainMap').get( 0 ) );
	}
	
	if(this.detailMap) {
		detailMap = this.detailMap;
	} else {
		detailMap	= new OpenLayers.Map( $('#detailMap').get( 0 ) );
	}

	// add controls to the main map and the detail map
	mainMap.addControl( new OpenLayers.Control.PanZoom( ) );
	mainMap.addControl( new OpenLayers.Control.LayerSwitcher( ) );
	detailMap.addControl( new OpenLayers.Control.LayerSwitcher( ) );

	// Create a set of OpenStreetMap (OSM) layers
	// but the OSM layers have a projection problem, will hopefully come back later
	/*
	var osmmnk	= new OpenLayers.Layer.OSM.Mapnik("OSM Mapnik");
	var osmmnk	= new OpenLayers.Layer.OSM.Mapnik("OSM Mapnik", {
			projection: new OpenLayers.Projection("EPSG:41001"),
			maxExtent:	new OpenLayers.Bounds(-20037508.34,-20037508.34, 20037508.34, 20037508.34),
			numZoomLevels: 18,
			maxResolution: 156543,
			units: 'degrees'
		});
    var osmtah	= new OpenLayers.Layer.OSM.Osmarender("OSM Osmarender"); // This Osmarender layer does not work as it is using EPSG:900913 projection
    var osmcyc	= new OpenLayers.Layer.OSM.CycleMap("OSM CycleMap"); // This Osmarender layer does not work as it is using EPSG:900913 projection
	*/

    // Create a Yahoo map layer
	// but has also projection problems
	/*
	var yahooLayer = new OpenLayers.Layer.Yahoo( "Yahoo" );
	*/

    // Create a set of Google Map layers for physical, street, hybrid and satellite view
	// has no projection problems, because i use the google projection as default :-(
    var gmap = new OpenLayers.Layer.Google( "Google Streets"); // type ist default
    var ghyb = new OpenLayers.Layer.Google( "Google Hybrid", {type: G_HYBRID_MAP});
    var gsat = new OpenLayers.Layer.Google( "Google Satellite", {type: G_SATELLITE_MAP});
    var gphy = new OpenLayers.Layer.Google( "Google Physical", {type: G_PHYSICAL_MAP});

	// the layers for the detail map
    var dgmap = new OpenLayers.Layer.Google( "Google Streets"); // type ist default
    var dghyb = new OpenLayers.Layer.Google( "Google Hybrid", {type: G_HYBRID_MAP});
    var dgsat = new OpenLayers.Layer.Google( "Google Satellite", {type: G_SATELLITE_MAP});
    var dgphy = new OpenLayers.Layer.Google( "Google Physical", {type: G_PHYSICAL_MAP});

    // Adds the layers to the mainMap and detailMap, because i couldn't clone them i only add the googlestreets layer to the detailmap
	// mainMap.addLayers( [gmap, ghyb, gsat, gphy, osmmnk, osmtah, osmcyc, yahooLayer]);
    mainMap.addLayers( [ gmap, ghyb, gsat, gphy ] );
	detailMap.addLayers( [ dgmap, dghyb, dgsat, dgphy ] );

	// zoom the mainMap to the minimal extend containing all markers, hopefully
	mainMap.zoomToExtent( new OpenLayers.Bounds( extent.left, extent.bottom, extent.right, extent.top ), false );
	
	// and zoom out once, because mormaly not all markers are in the above defined extend
	if( mainMap.getZoom( ) > 0 ) mainMap.zoomOut( );

	// zoome the detailMap as near to the earth surface as possible
	detailMap.setCenter( new OpenLayers.LonLat( defaultLong, defaultLat ), ( detailMap.getNumZoomLevels( ) - 1 ) );

	// read the default layer from configuration
	mainMap.setBaseLayer( mainMap.getLayersByName( defaultLayer )[0] );

	// for the detailMap this is unneccesary, because ther is only one layer
	// detailMap.setBaseLayer(detailMap.getLayersByName(defaultlayer)[0]);

	// load the Menu for layerswitching, not implemented
	// loadLayerMenu();

	// load the markers for the Map
    loadMarkers();

	// register events to reload the markers when the mainMap has been moved
	// and to move the detailMap on click on the mainMap
	mainMap.events.register('moveend', '', loadMarkers);
	mainMap.events.register('click', 'mapClick', detailView);

	$('#selectedIndirect').change(loadMarkers);
}

// load resources to display from MapController and build and display markers on the maps
function loadMarkers() {
	// destroy old Markers
	if( markers ) markers.destroy();
	if( detailMarkers ) detailMarkers.destroy();

	// add spinner for the user to know that I'm working

	// create and add new markerlayer to the maps
	markers			= new OpenLayers.Layer.Markers( "Markers" );
	detailMarkers	= new OpenLayers.Layer.Markers( "Markers" );
	mainMap.addLayer( markers );
	detailMap.addLayer( detailMarkers );

	// display a spinner over the map
//	var spinner = new OpenLayers.Marker( mainMap.getCenter(), new OpenLayers.Icon(urlBase + '../extensions/themes/silverblue/images/spinner.gif', new OpenLayers.Size(16,16), new OpenLayers.Pixel(0,0)) );
//	markers.addMarker( spinner );

	// get marker from MapController with JSON
	// replace __extent__ by the actual viewable extend
	bounds = mainMap.getExtent( );
	url = jsonRequestUrl.replace( /__extent__/ , bounds.top+','+bounds.right+','+bounds.bottom+','+bounds.left);
    if( typeof($('#selectedIndirect')) != 'undefined' ) {
        url = url.replace( /__indirect__/ ,  escape($('#selectedIndirect').val()));
    }
	$.getJSON( url, '', function( data ) {
		if( data ) {
			// read data from result
			for ( var i in data ) {
				// single Marker
				if ( data[i].containingMarkers == null ) {
					// at the moment the marker doesn't bring a own icon with it
					var feature_data = { icon: icon.clone( ), label: data[i].label, uri: data[i].uri, url: data[i].url};
				}
				// Cluster
				else {
					// at the moment the marker doesn't bring a own icon with it
					var feature_data = { icon: cluster.clone( ), content: data[i].containingMarkers };
				}

				// create new feature with the special properties
				// feature is a very abstract thing
				var feature = new OpenLayers.Feature( markers, new OpenLayers.LonLat( data[i].longitude, data[i].latitude ), feature_data );

				// create a marker from the feature
				var marker = feature.createMarker( );

				// register events for the marker to open popup and to move the detailmap
				// the second parameter gives the content in which the function will be called (accessible as this in the function)
				marker.events.register( 'click', feature, selected );
				marker.events.register( 'mouseover', feature, detailView );

				// add the marker to the markerlayer of the mainMap
				markers.addMarker( marker );

				// add a marker at the same position to the detailMap
				detailMarkers.addMarker( new OpenLayers.Marker( marker.lonlat, feature.data.icon.clone( ) ) );
			}
		}
	});
//	spinner.destroy();
}

// this function is called, when a marker is clicked/selected
function selected( evt ) {

	// get the markercontainer [depricated], seebi will remove this
	var marker_container = $( "#marker_container" );

	// get the data from the feature
	if( this.data.content == null ) {
		// single marker
		showPopup(this.data, false);
		addItem( this.data, marker_container );
	}
	else {
		// cluster
		showPopup(this.data, true);
		for( i in this.data.content ){
			addItem( this.data.content[i], marker_container );
		}
	}	

	OpenLayers.Event.stop(evt);

	// adds an item to the list [depricated], seebi will remove this
	function addItem( itemData, marker_container ) {
		if( document.getElementById( 'marker_' + itemData.uri ) == null ){
			var item = $('<li>').attr('id', 'marker_' + itemData.uri);
			var link = $('<a>').attr('class', 'expandable Resource hasMenu').attr('about', itemData.uri).attr('href', itemData.url);
			link.append( document.createTextNode( itemData.label ) );
			item.append( link );
			marker_container.append( item ).slideDown('slow'); // slideDown doesn't seem to work

			updateSelectedMarkersCounter( );
		}
	}

	// update the counter of listelements [depricated], seebi will remove this
	function updateSelectedMarkersCounter( ) {
		var message		= "No marker selected.";
		var container	= document.getElementById('marker_container');
		var messagebox	= document.getElementById('marker_messagebox');
		if(container.childNodes.length > 0) {
			message		= "One marker selected.";
			if(container.childNodes.length > 1) {
				message	= container.childNodes.length + " markers selected."
			}
		}
		messagebox.innerHTML = message;
	}

	// seebi will create popups here
	function showPopup(data, isCluster) {
		text = "";
		if(isCluster){
			for( i in data.content ){
				text += "label: " + data.content[i].label + "\n";
				text += "uri: " + data.content[i].uri + "\n";
				text += "url: " + data.content[i].url + "\n";
				text += "\n";
			}
		}
		else {
				text += "label: " + data.label + "\n";
				text += "uri: " + data.uri + "\n";
				text += "url: " + data.url + "\n";
		}
		//alert(text);
	}
}

// move the detailMap to the marker place
function detailView(evt) {
	if( this == 'mapClick' ) {
		ll = mainMap.getLonLatFromViewPortPx(evt.xy)
	}
	else if ( this.data.content != null ){
		// sould first calculate the extend of the cluster
		// alert("cluster");
		ll = this.lonlat;
	}
	else {
		ll = this.lonlat;
	}
	detailMap.panTo( ll );
	OpenLayers.Event.stop(evt);
}

/* There is no possibility to add and populate menus in ontowiki at the moment */
/*function loadLayerMenu() {
	var menu = $(layerMenuId);
	for(var i in mainMap.layers) {
		var item = $('<li><a>' + mainMap.layers[i].name + '</a></li>');
		menu.append(item);
	}
}*/

