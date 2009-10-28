// vim: sw=4:sts=4:expandtab

function NewMapManager ( mapContainer, extent, jsonUrl ) {

    /* properties */
    this.mapContainer            = mapContainer;
    this.map                     = {};
    this.markers                 = false;
    this.extent                  = extent;
    this.jsonRequestUrl          = jsonUrl;
    this.imgPath                 = '';
    this.iconDefaultCenter       = '';
    this.iconDefaultSize         = '';
    this.icon                    = {};
    this.clusterIcon             = {};
    this.themePath               = '';
    this.defaultLayer            = '';
    this.defaultSelectedIndirect = 0;

    /* functions */
    this.init                   = initMap;
    this.loadMarkers            = loadMarkers;
    this.markerCallbackHandler  = markerCallbackHandler;
    this.prepare                = prepareMap;
}

function prepareMap() {
    OpenLayers.ImgPath = this.imgPath;
}

// initiate the map
function initMap ( ) {
    this.map	    = new OpenLayers.Map( this.mapContainer, { theme: this.themePath });
//    this.map.theme  = this.themePath;

    //alert("extent: l:"+extent.left+" b:"+extent.bottom+" r:"+extent.right+" t:"+extent.top );

    // add controls to the main map and the detail map
    this.map.addControl( new OpenLayers.Control.PanZoom( ) );
    this.map.addControl( new OpenLayers.Control.LayerSwitcher( ) );

    // Create a set of OpenStreetMap (OSM) layers
    // but the OSM layers have a projection problem, will hopefully come back later

    // Create a set of Google Map layers for physical, street, hybrid and satellite view
    // has no projection problems, because i use the google projection as default :-(
    var gmap = new OpenLayers.Layer.Google( "Google Streets"); // type ist default
    var ghyb = new OpenLayers.Layer.Google( "Google Hybrid", {type: G_HYBRID_MAP});
    var gsat = new OpenLayers.Layer.Google( "Google Satellite", {type: G_SATELLITE_MAP});
    var gphy = new OpenLayers.Layer.Google( "Google Physical", {type: G_PHYSICAL_MAP});

    // Adds the layers to the mainMap and detailMap, because i couldn't clone them i only add the googlestreets layer to the detailmap
    // mainMap.addLayers( [gmap, ghyb, gsat, gphy, osmmnk, osmtah, osmcyc, yahooLayer]);
    this.map.addLayers( [ gmap, ghyb, gsat, gphy ] );

    // zoom the mainMap to the minimal extend containing all markers, hopefully
    this.map.zoomToExtent( new OpenLayers.Bounds( this.extent.left, this.extent.bottom, this.extent.right, this.extent.top ), false );

    // and zoom out once, because mormaly not all markers are in the above defined extend
    if( this.map.getZoom( ) > 0 ) this.map.zoomOut( );

    // read the default layer from configuration
    this.map.setBaseLayer( this.map.getLayersByName( this.defaultLayer )[0] );

    // load the markers for the Map
    this.loadMarkers(false);

    // register events to reload the markers when the mainMap has been moved
    // and to move the detailMap on click on the mainMap
    var that = this;
    this.map.events.register('moveend', '', function(data) {that.loadMarkers.call(that)});
    //    this.map.events.register('click', 'mapClick', detailView);

    $('#selectedIndirect').change(function(data) {that.loadMarkers.call(that)});
}

// load resources to display from MapController and build and display markers on the maps
function loadMarkers(that) {
    if(!that) {
        that = this;
    }
    // destroy old Markers
    //console.log("going to destroy all markers");
    if( that.markers ) { 
        that.markers.destroy();
    //    console.log("markers destoyes");
    } else {
    //    console.log("there where no markers to destoy");
    }

    // create and add new markerlayer to the maps
    //console.log("create new markers layer");
    that.markers = new OpenLayers.Layer.Markers( "Markers" );
    //console.log("markers layer created");
    that.map.addLayer( that.markers );
    //console.log("added markers layer to map");

    // get marker from MapController with JSON
    // replace __extent__ by the actual viewable extend
    bounds = that.map.getExtent( );
    url = that.jsonRequestUrl.replace( /__extent__/ , bounds.top+','+bounds.right+','+bounds.bottom+','+bounds.left);

    if( typeof($('#selectedIndirect')) != 'undefined' && escape($('#selectedIndirect').val()) != 'undefined') {
        url = url.replace( /__indirect__/ ,  escape($('#selectedIndirect').val()));
    } else {
        url = url.replace( /__indirect__/ ,  that.defaultSelectedIndirect);
    }

    $.getJSON( url, '', function(data) {that.markerCallbackHandler.call(that,data)});
}

function markerCallbackHandler (data,that) {
    if(!that) {
        that = this;
    }
    if( data ) {
        // read data from result
        for ( var i in data ) {
            // single Marker
            if ( data[i].containingMarkers == null ) {
                // at the moment the marker doesn't bring a own icon with it
                var feature_data = { icon: that.icon.clone( ), label: data[i].label, uri: data[i].uri, url: data[i].url};
            }
            // Cluster
            else {
                // at the moment the marker doesn't bring a own icon with it
                var feature_data = { icon: that.clusterIcon.clone( ), content: data[i].containingMarkers };
            }

            // create new feature with the special properties
            // feature is a very abstract thing
           // console.log("create new feature on the markers layer");
            var feature = new OpenLayers.Feature( that.markers, new OpenLayers.LonLat( data[i].longitude, data[i].latitude ), feature_data );
           // console.log("created new feature on the markers layer ... done");

            // create a marker from the feature
            var marker = feature.createMarker( );

            // register events for the marker to open popup and to move the detailmap
            // the second parameter gives the content in which the function will be called (accessible as this in the function)
            // === here will come a great new thing, I don't know what, but it will ===
            //marker.events.register( 'click', feature, selected );
            //marker.events.register( 'mouseover', feature, detailView );

            // add the marker to the markerlayer of the mainMap
            //console.log("putting new marker on the markers layer");
            that.markers.addMarker( marker );
            //console.log("put new marker on the markers layer ... done");
        }
    }
}

