// (c)2007 Mapstraction Team- http://mapstraction.com

function $m() { var elements = new Array(); for (var i = 0; i < arguments.length; i++) { var element = arguments[i]; if (typeof element == 'string')
element = document.getElementById(element); if (arguments.length == 1)
return element; elements.push(element);}
return elements;}
function loadScript(src,callback) { var script = document.createElement('script'); script.type = 'text/javascript'; script.src = src; if (callback) { var evl=new Object(); evl.handleEvent=function (e){callback();}; script.addEventListener('load',evl,true);}
document.getElementsByTagName("head")[0].appendChild(script); return;}
function convertLatLonXY_Yahoo(point,level){ var size = 1 << (26 - level); var pixel_per_degree = size / 360.0; var pixel_per_radian = size / (2 * Math.PI); var origin = new YCoordPoint(size / 2 , size / 2); var answer = new YCoordPoint(); answer.x = Math.floor(origin.x + point.lon * pixel_per_degree); var sin = Math.sin(point.lat * Math.PI / 180.0); answer.y = Math.floor(origin.y + 0.5 * Math.log((1 + sin) / (1 - sin)) * -pixel_per_radian); return answer;}
function loadStyle(href) { var link = document.createElement('link'); link.type = 'text/css'; link.rel = 'stylesheet'; link.href = href; document.getElementsByTagName("head")[0].appendChild(link); return;}
function getStyle(el, prop) { var y; if (el.currentStyle)
y = el.currentStyle[prop]; else if (window.getComputedStyle)
y = window.getComputedStyle( el, '').getPropertyValue(prop); return y;}
function lonToMetres (lon,lat) { return lon * 111200 * Math.cos(lat * (Math.PI/180));}
function metresToLon(m,lat) { return m / (111200*Math.cos(lat * (Math.PI/180)));}
function getDegreesFromGoogleZoomLevel (pixels,zoom)
{ return (360*pixels) / (Math.pow(2,zoom+8));}
function getGoogleZoomLevelFromDegrees (pixels,degrees)
{ return logN ((360*pixels)/degrees, 2) - 8;}
function logN (number,base)
{ return Math.log(number) / Math.log(base);}
function Mapstraction(element,api,debug) { this.api = api; this.maps = new Object(); this.currentElement = $m(element); this.eventListeners = new Array(); this.markers = new Array(); this.layers = new Array(); this.polylines = new Array(); this.images = new Array(); this.loaded = new Object(); this.onload = new Object(); if(debug == true)
{ this.debug = true
}
else
{ this.debug = false
}
this.svn_revision_string = '$Revision: 173 $'; this.addControlsArgs = new Object(); if (this.currentElement)
{ this.addAPI($m(element),api);}
}
Mapstraction.prototype.swap = function(element,api) { if (this.api == api) { return;}
var center = this.getCenter(); var zoom = this.getZoom(); this.currentElement.style.visibility = 'hidden'; this.currentElement.style.display = 'none'; this.currentElement = $m(element); this.currentElement.style.visibility = 'visible'; this.currentElement.style.display = 'block'; this.api = api; if (this.maps[this.api] == undefined) { this.addAPI($m(element),api); this.setCenterAndZoom(center,zoom); for (var i=0; i<this.markers.length; i++) { this.addMarker( this.markers[i], true);}
for (var i=0; i<this.polylines.length; i++) { this.addPolyline( this.polylines[i], true);}
}else{ this.setCenterAndZoom(center,zoom);}
this.addControls(this.addControlsArgs);}
Mapstraction.prototype.addAPI = function(element,api) { me = this; this.loaded[api] = false; this.onload[api] = new Array(); switch (api) { case 'yahoo':
if (YMap) { this.maps[api] = new YMap(element); YEvent.Capture(this.maps[api],EventsList.MouseClick,function(event,location) { me.clickHandler(location.Lat,location.Lon,location,me) }); YEvent.Capture(this.maps[api],EventsList.changeZoom,function() { me.moveendHandler(me) }); YEvent.Capture(this.maps[api],EventsList.endPan,function() { me.moveendHandler(me) }); this.loaded[api] = true;}
else { alert('Yahoo map script not imported');}
break; case 'google':
if (GMap2) { if (GBrowserIsCompatible()) { this.maps[api] = new GMap2(element); GEvent.addListener(this.maps[api], 'click', function(marker,location) { if ( location ) { me.clickHandler(location.y,location.x,location,me);}
}); GEvent.addListener(this.maps[api], 'moveend', function() {me.moveendHandler(me)}); this.loaded[api] = true;}
else { alert('browser not compatible with Google Maps');}
}
else { alert('Google map script not imported');}
break; case 'microsoft':
if (VEMap) { element.style.position='relative'; var msft_width = parseFloat(getStyle($m(element),'width')); var msft_height = parseFloat(getStyle($m(element),'height')); var ffv = 0; var ffn = "Firefox/"; var ffp = navigator.userAgent.indexOf(ffn); if (ffp != -1) ffv = parseFloat(navigator.userAgent.substring(ffp+ffn.length)); if (ffv >= 1.5) { Msn.Drawing.Graphic.CreateGraphic=function(f,b) { return new Msn.Drawing.SVGGraphic(f,b) }
}
this.maps[api] = new VEMap(element.id); this.maps[api].LoadMap(); this.maps[api].AttachEvent("onclick", function(e) { me.clickHandler(e.view.LatLong.Latitude, e.view.LatLong.Longitude, me);}); this.maps[api].AttachEvent("onchangeview", function(e) {me.moveendHandler(me)}); this.resizeTo(msft_width, msft_height); this.loaded[api] = true;}
else { alert('Virtual Earth script not imported');}
break; case 'openlayers':
this.maps[api] = new OpenLayers.Map( element.id, { maxExtent: new OpenLayers.Bounds(-20037508.34,-20037508.34,20037508.34,20037508.34), maxResolution:156543, numZoomLevels:18, units:'meters', projection: "EPSG:41001"
} ); this.layers['osmmapnik'] = new OpenLayers.Layer.TMS( 'OSM Mapnik', [ "http://a.tile.openstreetmap.org/", "http://b.tile.openstreetmap.org/", "http://c.tile.openstreetmap.org/" ], { type:'png', getURL: function (bounds) { var res = this.map.getResolution(); var x = Math.round ((bounds.left - this.maxExtent.left) / (res * this.tileSize.w)); var y = Math.round ((this.maxExtent.top - bounds.top) / (res * this.tileSize.h)); var z = this.map.getZoom(); var limit = Math.pow(2, z); if (y < 0 || y >= limit) { return null;} else { x = ((x % limit) + limit) % limit; var path = z + "/" + x + "/" + y + "." + this.type; var url = this.url; if (url instanceof Array) { url = this.selectUrl(path, url);}
return url + path;}
}, displayOutsideMaxExtent: true
} ); this.layers['osm'] = new OpenLayers.Layer.TMS( 'OSM', [ "http://a.tah.openstreetmap.org/Tiles/tile.php/", "http://b.tah.openstreetmap.org/Tiles/tile.php/", "http://c.tah.openstreetmap.org/Tiles/tile.php/" ], { type:'png', getURL: function (bounds) { var res = this.map.getResolution(); var x = Math.round ((bounds.left - this.maxExtent.left) / (res * this.tileSize.w)); var y = Math.round ((this.maxExtent.top - bounds.top) / (res * this.tileSize.h)); var z = this.map.getZoom(); var limit = Math.pow(2, z); if (y < 0 || y >= limit) { return null;} else { x = ((x % limit) + limit) % limit; var path = z + "/" + x + "/" + y + "." + this.type; var url = this.url; if (url instanceof Array) { url = this.selectUrl(path, url);}
return url + path;}
}, displayOutsideMaxExtent: true
} ); this.maps[api].addLayer(this.layers['osmmapnik']); this.maps[api].addLayer(this.layers['osm']); this.loaded[api] = true; break; case 'openstreetmap':
if (GMap2) { if (GBrowserIsCompatible()) { this.maps[api] = new GMap2(element); GEvent.addListener(this.maps[api], 'click', function(marker,location) { if ( location ) { me.clickHandler(location.y,location.x,location,me);}
}); GEvent.addListener(this.maps[api], 'moveend', function() {me.moveendHandler(me)}); var copyright = new GCopyright(1, new GLatLngBounds(new GLatLng(-90,-180), new GLatLng(90,180)), 0, "copyleft"); var copyrightCollection = new GCopyrightCollection('OSM'); copyrightCollection.addCopyright(copyright); var tilelayers = new Array(); tilelayers[0] = new GTileLayer(copyrightCollection, 1, 18); tilelayers[0].getTileUrl = function (a, b) { return "http://tile.openstreetmap.org/"+b+"/"+a.x+"/"+a.y+".png";}; tilelayers[0].isPng = function() { return true;}; tilelayers[0].getOpacity = function() { return 1.0;}
var custommap = new GMapType(tilelayers, new GMercatorProjection(19), "OSM", {errorMessage:"More OSM coming soon"}); this.maps[api].addMapType(custommap); this.loaded[api] = true; var myPoint = new LatLonPoint(50.6805,-1.4062505); this.setCenterAndZoom(myPoint, 11); this.maps[api].setMapType(custommap);}
else { alert('browser not compatible with Google Maps');}
}
else { alert('Google map script not imported');}
break; case 'multimap':
this.maps[api] = new MultimapViewer( element ); this.maps[api].addEventHandler( 'click', function(eventType, eventTarget, arg1, arg2, arg3) { if (arg1) { me.clickHandler(arg1.lat, arg1.lon, me);}
}); this.maps[api].addEventHandler( 'changeZoom', function(eventType, eventTarget, arg1, arg2, arg3) { me.moveendHandler(me);}); this.maps[api].addEventHandler( 'endPan', function(eventType, eventTarget, arg1, arg2, arg3) { me.moveendHandler(me);}); this.loaded[api] = true; break; case 'map24':
if (Map24) { Map24.loadApi(["core_api","wrapper_api"] , function() { Map24.MapApplication.init
( { NodeName: element.id, MapType: "Static" } ); me.maps[api] = Map24.MapApplication.Map; Map24.MapApplication.Map.addListener('Map24.Event.MapClick', function(e) { me.clickHandler(e.Coordinate.Latitude/60, e.Coordinate.Longitude/60, me); e.stop();} ); Map24.MapApplication.Map.addListener("MapPanStop", function(e) { me.moveendHandler(me);} ); var client=Map24.MapApplication.Map.MapClient['Static']; me.loaded[api] = true; for (var i = 0; i < me.onload[api].length; i++) { me.onload[api][i]();}
}, "2.0.1247" );} else { alert('map24 api not loaded');}
break; case 'mapquest':
myself = this; MQInitOverlays( function() { myself.loaded[api] = true; myself.maps[api] = new MQTileMap(element); for (var i = 0; i < myself.onload[api].length; i++) { myself.onload[api][i]();}
}); break; case 'freeearth':
this.maps[api] = new FE.Map($m(element)); myself = this; this.maps[api].onLoad = function() { myself.freeEarthLoaded = true; myself.loaded[api] = true; for (var i = 0; i < myself.onload[api].length; i++) { myself.onload[api][i]();}
}
this.maps[api].load(); break; default:
if(this.debug)
alert(api + ' not supported by mapstraction');}
}
Mapstraction._getScriptLocation=function(){ var scriptLocation=""; var SCRIPT_NAME = "mapstraction.js"; var scripts=document.getElementsByTagName('script'); for(var i=0; i<scripts.length; i++) { var src=scripts[i].getAttribute('src'); if(src) { var index=src.lastIndexOf(SCRIPT_NAME); if((index>-1)&&(index+SCRIPT_NAME.length==src.length)){scriptLocation=src.slice(0,-SCRIPT_NAME.length); break;}
}
}
return scriptLocation;}
Mapstraction.writeInclude = function(api, key, version) { var jsfiles=new Array(); var allScriptTags=""; var host=Mapstraction._getScriptLocation()+"lib/"; switch(api) { case 'google':
if(version == null) { version = "2";}
jsfiles.push('http://maps.google.com/maps?file=api&v=' + version + '&key=' + key); break; case "microsoft":
if(version == null) { version = "v3";}
jsfiles.push('http://dev.virtualearth.net/mapcontrol/' + version + '/mapcontrol.js'); break; case "yahoo":
if(version == null) { version = "3.0";}
jsfiles.push('http://api.maps.yahoo.com/ajaxymap?v='+ version + '&appid=' + key); break; case "openlayers":
jsfiles.push('http://openlayers.org/api/OpenLayers.js'); break; case "multimap":
if(version == null) { version = "1.2";}
jsfiles.push('http://developer.multimap.com/API/maps/' + version + '/' + key); break; case "map24":
jsfiles.push('http://api.maptp.map24.com/ajax?appkey=' + key); break; case "mapquest":
if(version == null) { version = "5.1";}
jsfiles.push('http://btilelog.access.mapquest.com/tilelog/transaction?transaction=script&key=' + key + '&ipr=true&itk=true&v=' + version); jsfiles.push('mapquest-js/mqcommon.js'); jsfiles.push('mapquest-js/mqutils.js'); jsfiles.push('mapquest-js/mqobjects.js'); jsfiles.push('mapquest-js/mqexec.js'); break; case "freeearth":
jsfiles.push('http://freeearth.poly9.com/api.js');}
for(var i=0; i<jsfiles.length; i++) { if(/MSIE/.test(navigator.userAgent)||/Safari/.test(navigator.userAgent)) { var currentScriptTag = jsfiles[i]; allScriptTags += currentScriptTag;} else { var s=document.createElement("script"); s.src=jsfiles[i]; s.type="text/javascript"; var h=document.getElementsByTagName("head").length ? document.getElementsByTagName("head")[0] : document.body; h.appendChild(s);}
}
if(allScriptTags) document.write(allScriptTags);}
Mapstraction.prototype.isLoaded = function(api){ if(api == null)
api = this.api; return this.loaded[api];}
Mapstraction.prototype.setDebug = function(debug){ if(debug != null)
return this.debug = debug; else
return this.debug;}
Mapstraction.prototype.resizeTo = function(width,height){ if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.resizeTo(width,height);} ); return;}
switch (this.api) { case 'yahoo':
this.maps[this.api].resizeTo(new YSize(width,height)); break; case 'google':
case 'openstreetmap':
this.currentElement.style.width = width; this.currentElement.style.height = height; this.maps[this.api].checkResize(); break; case 'openlayers':
this.currentElement.style.width = width; this.currentElement.style.height = height; this.maps[this.api].updateSize(); break; case 'microsoft':
this.maps[this.api].Resize(width, height); break; case 'multimap':
this.currentElement.style.width = width; this.currentElement.style.height = height; this.maps[this.api].resize(); break; case 'mapquest':
this.currentElement.style.width = width; this.currentElement.style.height = height; this.maps[this.api].setSize(new MQSize(width, height)); break; case 'map24':
Map24.MapApplication.Map.Canvas['c'].resizeTo(width,height); break;}
}
Mapstraction.prototype.clickHandler = function(lat,lon, me) { for(var i = 0; i < this.eventListeners.length; i++) { if(this.eventListeners[i][1] == 'click') { this.eventListeners[i][0](new LatLonPoint(lat,lon));}
}
}
Mapstraction.prototype.moveendHandler = function(me) { for(var i = 0; i < this.eventListeners.length; i++) { if(this.eventListeners[i][1] == 'moveend') { this.eventListeners[i][0]();}
}
}
Mapstraction.prototype.addEventListener = function(type, func) { var listener = new Array(); listener.push(func); listener.push(type); this.eventListeners.push(listener); switch (this.api) { case 'openlayers':
this.maps[this.api].events.register(type, this, func); break;}
}
Mapstraction.prototype.addControls = function( args ) { if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.addControls(args);} ); return;}
var map = this.maps[this.api]; this.addControlsArgs = args; switch (this.api) { case 'google':
case 'openstreetmap':
if (this.controls) { while (ctl = this.controls.pop()) { map.removeControl(ctl);}
} else { this.controls = new Array();}
c = this.controls; if ( args.zoom || args.pan ) { if ( args.zoom == 'large' ) { c.unshift(new GLargeMapControl()); map.addControl(c[0]);} else { c.unshift(new GSmallMapControl()); map.addControl(c[0]);}
}
if ( args.scale ) { c.unshift(new GScaleControl()); map.addControl(c[0]);}
if (this.api != "openstreetmap") { if ( args.overview ) { c.unshift(new GOverviewMapControl()); map.addControl(c[0]);}
if ( args.map_type ) { c.unshift(new GMapTypeControl()); map.addControl(c[0]);}
}
break; case 'yahoo':
if ( args.pan ) map.addPanControl(); else map.removePanControl(); if ( args.zoom == 'large' ) map.addZoomLong(); else if ( args.zoom == 'small' ) map.addZoomShort(); else map.removeZoomScale(); break; case 'openlayers':
for (var i = map.controls.length; i>1; i--) { map.controls[i-1].deactivate(); map.removeControl(map.controls[i-1]);}
if ( args.pan ) { map.addControl(new OpenLayers.Control.PanZoomBar());}
else { }
if ( args.zoom == 'large' ) { map.addControl(new OpenLayers.Control.PanZoomBar());}
else if ( args.zoom == 'small' ) { map.addControl(new OpenLayers.Control.ZoomBox());}
else map.addControl(new OpenLayers.Control.ZoomBox()); if ( args.overview ) { map.addControl(new OpenLayers.Control.OverviewMap());}
if ( args.map_type ) { map.addControl(new OpenLayers.Control.LayerSwitcher());}
break; case 'multimap':
pan_zoom_widget = "MM"; if (args.zoom && args.zoom == "small") { pan_zoom_widget = pan_zoom_widget + "Small";}
if (args.pan) { pan_zoom_widget = pan_zoom_widget + "Pan";}
if (args.zoom) { pan_zoom_widget = pan_zoom_widget + "Zoom";}
pan_zoom_widget = pan_zoom_widget + "Widget"; if (pan_zoom_widget != "MMWidget") { eval(" map.addWidget( new " + pan_zoom_widget + "() );");}
if ( args.map_type ) { map.addWidget( new MMMapTypeWidget() );}
if ( args.overview ) { map.addWidget( new MMOverviewWidget() );}
break; case 'mapquest':
if (this.controls) { while (ctl = this.controls.pop()) { map.removeControl(ctl);}
} else { this.controls = new Array();}
c = this.controls; if ( args.pan ) { c.unshift(new MQPanControl()); map.addControl(c[0], new MQMapCornerPlacement(MQMapCorner.TOP_LEFT, new MQSize(0,0)));}
if ( args.zoom == 'large' ) { c.unshift(new MQLargeZoomControl()); map.addControl(c[0], new MQMapCornerPlacement(MQMapCorner.TOP_LEFT, new MQSize(0,0)));}
else if ( args.zoom == 'small' ) { c.unshift(new MQZoomControl()); map.addControl(c[0], new MQMapCornerPlacement(MQMapCorner.BOTTOM_LEFT, new MQSize(0,0)));}
if ( args.map_type ) { c.unshift(new MQViewControl()); map.addControl(c[0], new MQMapCornerPlacement(MQMapCorner.TOP_RIGHT, new MQSize(0,0)));}
break;}
}
Mapstraction.prototype.addSmallControls = function() { if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.addSmallControls();} ); return;}
var map = this.maps[this.api]; switch (this.api) { case 'yahoo':
map.addPanControl(); map.addZoomShort(); this.addControlsArgs.pan = true; this.addControlsArgs.zoom = 'small'; break; case 'google':
case 'openstreetmap':
map.addControl(new GSmallMapControl()); this.addControlsArgs.zoom = 'small'; break; case 'openlayers':
map.addControl(new OpenLayers.Control.ZoomBox()); map.addControl(new OpenLayers.Control.LayerSwitcher({'ascending':false})); break; case 'multimap':
smallPanzoomWidget = new MMSmallPanZoomWidget(); map.addWidget( smallPanzoomWidget ); this.addControlsArgs.pan = true; this.addControlsArgs.zoom = 'small'; break; case 'mapquest':
map.addControl(new MQZoomControl(map)); map.addControl(new PanControl(map)); this.addControlsArgs.pan = true; this.addControlsArgs.zoom = 'small'; break;}
}
Mapstraction.prototype.addLargeControls = function() { if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.addLargeControls();} ); return;}
var map = this.maps[this.api]; switch (this.api) { case 'yahoo':
map.addPanControl(); map.addZoomLong(); this.addControlsArgs.pan = true; this.addControlsArgs.zoom = 'large'; break; case 'openlayers':
map.addControl(new OpenLayers.Control.PanZoomBar()); break; case 'google':
map.addControl(new GMapTypeControl()); map.addControl(new GOverviewMapControl()) ; this.addControlsArgs.overview = true; this.addControlsArgs.map_type = true; case 'openstreetmap':
map.addControl(new GLargeMapControl()); map.addControl(new GScaleControl()) ; this.addControlsArgs.pan = true; this.addControlsArgs.zoom = 'large'; this.addControlsArgs.scale = true; break; case 'multimap':
panzoomWidget = new MMPanZoomWidget(); map.addWidget( panzoomWidget ); this.addControlsArgs.pan = true; this.addControlsArgs.zoom = 'large'; break; case 'mapquest':
map.addControl(new MQLargeZoomControl(map)); map.addControl(new PanControl(map)); map.addControl(new MQViewControl(map)); this.addControlsArgs.pan = true; this.addControlsArgs.zoom = 'large'; this.addControlsArgs.map_type = true; break;}
}
Mapstraction.prototype.addMapTypeControls = function() { if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.addMapTypeControls();} ); return;}
var map = this.maps[this.api]; switch (this.api) { case 'yahoo':
map.addTypeControl(); break; case 'google':
case 'openstreetmap':
map.addControl(new GMapTypeControl()); break; case 'multimap':
map.addWidget( new MMMapTypeWidget() ); break; case 'mapquest':
map.addControl(new MQViewControl(map)); break; case 'openlayers':
map.addControl( new OpenLayers.Control.LayerSwitcher({'ascending':false}) ); break;}
}
Mapstraction.prototype.dragging = function(on) { if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.dragging(on);} ); return;}
var map = this.maps[this.api]; switch (this.api) { case 'google':
case 'openstreetmap':
if (on) { map.enableDragging();} else { map.disableDragging();}
break; case 'yahoo':
if (on) { map.enableDragMap();} else { map.disableDragMap();}
break; case 'multimap':
if (on) { map.setOption("drag","dragmap");} else { map.setOption("drag","");}
break; case 'mapquest':
map.enableDragging(on); break;}
}
Mapstraction.prototype.setCenterAndZoom = function(point, zoom) { if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.setCenterAndZoom(point, zoom);} ); return;}
var map = this.maps[this.api]; switch (this.api) { case 'yahoo':
var yzoom = 18 - zoom; map.drawZoomAndCenter(point.toYahoo(),yzoom); break; case 'google':
case 'openstreetmap':
map.setCenter(point.toGoogle(), zoom); break; case 'microsoft':
map.SetCenterAndZoom(point.toMicrosoft(),zoom); break; case 'openlayers':
map.setCenter(point.toOpenLayers(), zoom); break; case 'multimap':
map.goToPosition( new MMLatLon( point.lat, point.lng ) ); map.setZoomFactor( zoom ); break; case 'map24':
var newSettings = new Object(); newSettings.Latitude = point.lat*60; newSettings.Longitude = point.lon*60; var client = map.MapClient['Static']; var dLon = getDegreesFromGoogleZoomLevel
(client.getCanvasSize().Width,zoom); newSettings.MinimumWidth = lonToMetres (dLon, point.lat); Map24.MapApplication.center ( newSettings ); break; case 'mapquest':
map.setCenter(new MQLatLng( point.lat, point.lng ), zoom - 3 ); break; case 'freeearth':
if (this.freeEarthLoaded) { map.setTargetLatLng( point.toFreeEarth() );} else { myself = this; this.freeEarthOnLoad.push( function() { myself.setCenterAndZoom(point);} );}
break; default:
if(this.debug)
alert(this.api + ' not supported by Mapstraction.setCenterAndZoom');}
}
Mapstraction.prototype.addMarker = function(marker,old) { var map = this.maps[this.api]; marker.mapstraction = this; marker.api = this.api; marker.map = this.maps[this.api]; if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.addMarker(marker, old);} ); return;}
switch (this.api) { case 'yahoo':
var ypin = marker.toYahoo(); marker.setChild(ypin); map.addOverlay(ypin); if (! old) { this.markers.push(marker);}
break; case 'google':
case 'openstreetmap':
var gpin = marker.toGoogle(); marker.setChild(gpin); map.addOverlay(gpin); if (! old) { this.markers.push(marker);}
break; case 'microsoft':
var mpin = marker.toMicrosoft(); marker.setChild(mpin); map.AddPushpin(mpin); if (! old) { this.markers.push(marker);}
break; case 'openlayers':
if (!this.layers['markers'])
{ this.layers['markers'] = new OpenLayers.Layer.Markers("markers"); map.addLayer(this.layers['markers']);}
var olmarker = marker.toOpenLayers(); marker.setChild(olmarker); this.layers['markers'].addMarker(olmarker); if (! old) { this.markers.push(marker);}
break; case 'multimap':
var mmpin = marker.toMultiMap(); marker.setChild(mmpin); map.addOverlay(mmpin); if (! old) { this.markers.push(marker);}
break; case 'map24':
var m24pin = marker.toMap24(); marker.setChild(m24pin); m24pin.commit(); if (! old) { this.markers.push(marker);}
break; case 'mapquest':
var mqpin = marker.toMapQuest(); marker.setChild(mqpin); map.addPoi(mqpin); if (! old) { this.markers.push(marker);}
break; case 'freeearth':
var fepin = marker.toFreeEarth(); marker.setChild(fepin); map.addOverlay(fepin); if (! old) { this.markers.push(marker);}
break; default:
if(this.debug)
alert(this.api + ' not supported by Mapstraction.addMarker');}
}
Mapstraction.prototype.addMarkerWithData = function(marker,data) { marker.addData(data); this.addMarker(marker);}
Mapstraction.prototype.addPolylineWithData = function(polyline,data) { polyline.addData(data); this.addPolyline(polyline);}
Mapstraction.prototype.removeMarker = function(marker) { if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.removeMarker(marker);} ); return;}
var map = this.maps[this.api]; var tmparray = new Array(); while(this.markers.length > 0){ current_marker = this.markers.pop(); if(marker == current_marker) { switch (this.api) { case 'google':
case 'openstreetmap':
map.removeOverlay(marker.proprietary_marker); break; case 'yahoo':
map.removeOverlay(marker.proprietary_marker); break; case 'microsoft':
map.DeletePushpin(marker.pinID); break; case 'multimap':
map.removeOverlay(marker.proprietary_marker); break; case 'mapquest':
map.removePoi(marker.proprietary_marker); break; case 'map24':
marker.proprietary_marker.remove(); break; case 'openlayers':
this.layers['markers'].removeMarker(marker.proprietary_marker); marker.proprietary_marker.destroy(); break;}
marker.onmap = false; break;} else { tmparray.push(current_marker);}
}
this.markers = this.markers.concat(tmparray);}
Mapstraction.prototype.removeAllMarkers = function() { if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.removeAllMarkers();} ); return;}
var map = this.maps[this.api]; switch (this.api) { case 'yahoo':
map.removeMarkersAll(); break; case 'google':
case 'openstreetmap':
map.clearOverlays(); break; case 'microsoft':
map.DeleteAllPushpins(); break; case 'multimap':
map.removeAllOverlays(); break; case 'mapquest':
map.removeAllPois(); break; case 'map24':
var current_marker; while(this.markers.length > 0) { current_marker = this.markers.pop(); current_marker.proprietary_marker.remove();}
break; case 'openlayers':
this.layers['markers'].clearMarkers(); break; default:
if(this.debug)
alert(this.api + ' not supported by Mapstraction.removeAllMarkers');}
this.markers = new Array();}
Mapstraction.prototype.addPolyline = function(polyline,old) { if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.addPolyline(polyline,old);} ); return;}
var map = this.maps[this.api]; switch (this.api) { case 'yahoo':
ypolyline = polyline.toYahoo(); polyline.setChild(ypolyline); map.addOverlay(ypolyline); if(!old) {this.polylines.push(polyline);}
break; case 'google':
case 'openstreetmap':
gpolyline = polyline.toGoogle(); polyline.setChild(gpolyline); map.addOverlay(gpolyline); if(!old) {this.polylines.push(polyline);}
break; case 'microsoft':
mpolyline = polyline.toMicrosoft(); polyline.setChild(mpolyline); map.AddPolyline(mpolyline); if(!old) {this.polylines.push(polyline);}
break; case 'openlayers':
if(this.debug)
alert(this.api + ' not supported by Mapstraction.addPolyline'); break; case 'multimap':
mmpolyline = polyline.toMultiMap(); polyline.setChild(mmpolyline); map.addOverlay( mmpolyline ); if(!old) {this.polylines.push(polyline);}
break; case 'mapquest':
mqpolyline = polyline.toMapQuest(); polyline.setChild(mqpolyline); map.addOverlay( mqpolyline ); if(!old) {this.polylines.push(polyline);}
break; case 'map24':
var m24polyline = polyline.toMap24(); polyline.setChild(m24polyline); m24polyline.commit(); if(!old) {this.polylines.push(polyline);}
break; default:
if(this.debug)
alert(this.api + ' not supported by Mapstraction.addPolyline');}
}
Mapstraction.prototype.removePolyline = function(polyline) { if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.removePolyline(polyline);} ); return;}
var map = this.maps[this.api]; var tmparray = new Array(); while(this.polylines.length > 0){ current_polyline = this.polylines.pop(); if(polyline == current_polyline) { switch (this.api) { case 'google':
case 'openstreetmap':
map.removeOverlay(polyline.proprietary_polyline); break; case 'yahoo':
map.removeOverlay(polyline.proprietary_polyline); break; case 'microsoft':
map.DeletePolyline(polyline.pllID); break; case 'multimap':
polyline.proprietary_polyline.remove(); break; case 'mapquest':
map.removeOverlay(polyline.proprietary_polyline); break; case 'map24':
polyline.proprietary_polyline.remove(); break;}
polyline.onmap = false; break;} else { tmparray.push(current_polyline);}
}
this.polylines = this.polylines.concat(tmparray);}
Mapstraction.prototype.removeAllPolylines = function() { if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.removeAllPolylines();} ); return;}
var map = this.maps[this.api]; switch (this.api) { case 'yahoo':
for(var i = 0, length = this.polylines.length;i < length;i++){ map.removeOverlay(this.polylines[i].proprietary_polyline);}
break; case 'google':
case 'openstreetmap':
for(var i = 0, length = this.polylines.length;i < length;i++){ map.removeOverlay(this.polylines[i].proprietary_polyline);}
break; case 'microsoft':
map.DeleteAllPolylines(); break; case 'multimap':
for(var i = 0, length = this.polylines.length;i < length;i++){ this.polylines[i].proprietary_polyline.remove();}
break; case 'mapquest':
map.removeAllOverlays(); break; case 'map24':
var current_polyline; while(this.polylines.length > 0) { current_polyline = this.polylines.pop(); current_polyline.proprietary_polyline.remove();}
break; default:
if(this.debug)
alert(this.api + ' not supported by Mapstraction.removeAllPolylines');}
this.polylines = new Array();}
Mapstraction.prototype.getCenter = function() { if(this.loaded[this.api] == false) { return null;}
var map = this.maps[this.api]; var point = undefined; switch (this.api) { case 'yahoo':
var pt = map.getCenterLatLon(); point = new LatLonPoint(pt.Lat,pt.Lon); break; case 'google':
case 'openstreetmap':
var pt = map.getCenter(); point = new LatLonPoint(pt.lat(),pt.lng()); break; case 'openlayers':
var pt = map.getCenter(); point = new LatLonPoint(pt.lat, pt.lon); break; case 'microsoft':
var pt = map.GetCenter(); point = new LatLonPoint(pt.Latitude,pt.Longitude); break; case 'multimap':
var pt = map.getCurrentPosition(); point = new LatLonPoint(pt.y, pt.x); break; case 'mapquest':
var pt = map.getCenter(); point = new LatLonPoint(pt.getLatitude(), pt.getLongitude()); break; case 'map24':
var pt = map.MapClient['Static'].getCurrentMapView().getCenter(); point = new LatLonPoint(pt.Y/60,pt.X/60); break; default:
if(this.debug)
alert(this.api + ' not supported by Mapstraction.getCenter');}
return point;}
Mapstraction.prototype.setCenter = function(point) { if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.setCenter(point);} ); return;}
var map = this.maps[this.api]; switch (this.api) { case 'yahoo':
map.panToLatLon(point.toYahoo()); break; case 'google':
case 'openstreetmap':
map.setCenter(point.toGoogle()); break; case 'openlayers':
map.setCenter(point.toOpenLayers()); break; case 'microsoft':
map.SetCenter(point.toMicrosoft()); break; case 'multimap':
map.goToPosition(point.toMultiMap()); break; case 'mapquest':
map.setCenter(point.toMapQuest()); break; case 'freeearth':
if (this.freeEarthLoaded) { map.setTargetLatLng( point.toFreeEarth() );} else { myself = this; this.freeEarthOnLoad.push( function() { myself.setCenterAndZoom(point);} );}
break; case 'map24':
var mv = map.MapClient['Static'].getCurrentMapView(); var newSettings = new Object(); newSettings.MinimumWidth = lonToMetres
(mv.LowerRight.Longitude - mv.TopLeft.Longitude, (mv.LowerRight.Latitude+mv.TopLeft.Latitude)/2); newSettings.Latitude = point.lat*60; newSettings.Longitude = point.lon*60; Map24.MapApplication.center(newSettings); break; default:
if(this.debug)
alert(this.api + ' not supported by Mapstraction.setCenter');}
}
Mapstraction.prototype.setZoom = function(zoom) { if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.setZoom(zoom);} ); return;}
var map = this.maps[this.api]; switch (this.api) { case 'yahoo':
var yzoom = 18 - zoom; map.setZoomLevel(yzoom); break; case 'google':
case 'openstreetmap':
map.setZoom(zoom); break; case 'openlayers':
map.zoomTo(zoom); break; case 'microsoft':
map.SetZoomLevel(zoom); break; case 'multimap':
map.setZoomFactor(zoom); break; case 'mapquest':
map.setZoomLevel(zoom - 3); break; case 'map24':
var point = this.getCenter(); var newSettings = new Object(); newSettings.Latitude = point.lat*60; newSettings.Longitude = point.lon*60; var client = map.MapClient['Static']; var dLon = getDegreesFromGoogleZoomLevel
(client.getCanvasSize().Width,zoom); newSettings.MinimumWidth = lonToMetres (dLon, point.lat); Map24.MapApplication.center ( newSettings ); break; default:
if(this.debug)
alert(this.api + ' not supported by Mapstraction.setZoom');}
}
Mapstraction.prototype.autoCenterAndZoom = function() { if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.autoCenterAndZoom();} ); return;}
var lat_max = -90; var lat_min = 90; var lon_max = -180; var lon_min = 180; for (var i=0; i<this.markers.length; i++) {; lat = this.markers[i].location.lat; lon = this.markers[i].location.lon; if (lat > lat_max) lat_max = lat; if (lat < lat_min) lat_min = lat; if (lon > lon_max) lon_max = lon; if (lon < lon_min) lon_min = lon;}
for (i=0; i<this.polylines.length; i++) { for (j=0; j<this.polylines[i].points.length; j++) { lat = this.polylines[i].points[j].lat; lon = this.polylines[i].points[j].lon; if (lat > lat_max) lat_max = lat; if (lat < lat_min) lat_min = lat; if (lon > lon_max) lon_max = lon; if (lon < lon_min) lon_min = lon;}
}
this.setBounds( new BoundingBox(lat_min, lon_min, lat_max, lon_max) );}
Mapstraction.prototype.centerAndZoomOnPoints = function(points) { var bounds = new BoundingBox(points[0].lat,points[0].lon,points[0].lat,points[0].lon); for (var i=1, len = points.length ; i<len; i++) { bounds.extend(points[i]);}
this.setBounds(bounds);}
Mapstraction.prototype.getZoom = function() { if(this.loaded[this.api] == false) { myself = this; return -1;}
var map = this.maps[this.api]; switch (this.api) { case 'yahoo':
return 18 - map.getZoomLevel(); case 'google':
case 'openstreetmap':
return map.getZoom(); case 'openlayers':
return map.zoom; case 'microsoft':
return map.GetZoomLevel(); case 'multimap':
return map.getZoomFactor(); case 'mapquest':
return map.getZoomLevel() + 3; case 'map24':
var mv = map.MapClient['Static'].getCurrentMapView(); var dLon = (mv.LowerRight.Longitude - mv.TopLeft.Longitude) / 60; var width = map.MapClient['Static'].getCanvasSize().Width; var zoom = getGoogleZoomLevelFromDegrees (width,dLon); return Math.round(zoom); default:
if(this.debug)
alert(this.api + ' not supported by Mapstraction.getZoom');}
}
Mapstraction.prototype.getZoomLevelForBoundingBox = function( bbox ) { if(this.loaded[this.api] == false) { myself = this; return -1;}
var map = this.maps[this.api]; var ne = bbox.getNorthEast(); var sw = bbox.getSouthWest(); switch (this.api) { case 'google':
case 'openstreetmap':
var gbox = new GLatLngBounds( sw.toGoogle(), ne.toGoogle() ); var zoom = map.getBoundsZoomLevel( gbox ); return zoom; break; case 'openlayers':
var olbox = bbox.toOpenLayers(); var zoom = map.getZoomForExtent(olbox); break; case 'multimap':
var mmlocation = map.getBoundsZoomFactor( sw.toMultiMap(), ne.toMultiMap() ); var zoom = mmlocation.zoom_factor(); return zoom; break; case 'map24':
var dLon = ne.lon - sw.lon; var width = map.MapClient['Static'].getCanvasSize().Width; var zoom = getGoogleZoomLevelFromDegrees (width,dLon); return Math.round(zoom); break; default:
if(this.debug)
alert( this.api + ' not supported by Mapstraction.getZoomLevelForBoundingBox' );}
}
Mapstraction.ROAD = 1; Mapstraction.SATELLITE = 2; Mapstraction.HYBRID = 3; Mapstraction.prototype.setMapType = function(type) { if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.setMapType(type);} ); return;}
var map = this.maps[this.api]; switch (this.api) { case 'yahoo':
switch(type) { case Mapstraction.ROAD:
map.setMapType(YAHOO_MAP_REG); break; case Mapstraction.SATELLITE:
map.setMapType(YAHOO_MAP_SAT); break; case Mapstraction.HYBRID:
map.setMapType(YAHOO_MAP_HYB); break; default:
map.setMapType(YAHOO_MAP_REG);}
break; case 'google':
case 'openstreetmap':
switch(type) { case Mapstraction.ROAD:
map.setMapType(G_NORMAL_MAP); break; case Mapstraction.SATELLITE:
map.setMapType(G_SATELLITE_MAP); break; case Mapstraction.HYBRID:
map.setMapType(G_HYBRID_MAP); break; default:
map.setMapType(G_NORMAL_MAP);}
break; case 'microsoft':
switch(type) { case Mapstraction.ROAD:
map.SetMapStyle(Msn.VE.MapStyle.Road); break; case Mapstraction.SATELLITE:
map.SetMapStyle(Msn.VE.MapStyle.Aerial); break; case Mapstraction.HYBRID:
map.SetMapStyle(Msn.VE.MapStyle.Hybrid); break; default:
map.SetMapStyle(Msn.VE.MapStyle.Road);}
break; case 'multimap':
maptypes = map.getAvailableMapTypes(); maptype = -1; for (var i = 0; i < maptypes.length; i++) { switch (maptypes[i]) { case MM_WORLD_MAP:
if (type == Mapstraction.ROAD) { maptype = maptypes[i];}
default_type = maptypes[i]; break; case MM_WORLD_AERIAL:
if (type == Mapstraction.SATELLITE) { maptype = maptypes[i];}
break; case MM_WORLD_HYBRID:
if (type == Mapstraction.HYBRID) { maptype = maptypes[i];}
break;}
}
if (maptype == -1) { maptype = default_type;}
map.setMapType(maptype); break; case 'mapquest':
switch (type) { case Mapstraction.ROAD:
map.setMapType("map"); break; case Mapstraction.SATELLITE:
map.setMapType("sat"); break; case Mapstraction.HYBRID:
map.setMapType("hyb"); break;}
break; default:
if(this.debug)
alert(this.api + ' not supported by Mapstraction.setMapType');}
}
Mapstraction.prototype.getMapType = function() { if(this.loaded[this.api] == false) { myself = this; return -1;}
var map = this.maps[this.api]; var type; switch (this.api) { case 'yahoo':
type = map.getCurrentMapType(); switch(type) { case YAHOO_MAP_REG:
return Mapstraction.ROAD; break; case YAHOO_MAP_SAT:
return Mapstraction.SATELLITE; break; case YAHOO_MAP_HYB:
return Mapstraction.HYBRID; break; default:
return null;}
break; case 'google':
case 'openstreetmap':
type = map.getCurrentMapType(); switch(type) { case G_NORMAL_MAP:
return Mapstraction.ROAD; break; case G_SATELLITE_MAP:
return Mapstraction.SATELLITE; break; case G_HYBRID_MAP:
return Mapstraction.HYBRID; break; default:
return null;}
break; case 'microsoft':
type = map.GetMapStyle(); switch(type) { case Msn.VE.MapStyle.Road:
return Mapstraction.ROAD; break; case Msn.VE.MapStyle.Aerial:
return Mapstraction.SATELLITE; break; case Msn.VE.MapStyle.Hybrid:
return Mapstraction.HYBRID; break; default:
return null;}
break; case 'multimap':
maptypes = map.getAvailableMapTypes(); type = map.getMapType(); switch(type) { case MM_WORLD_MAP:
return Mapstraction.ROAD; break; case MM_WORLD_AERIAL:
return Mapstraction.SATELLITE; break; case MM_WORLD_HYBRID:
return Mapstraction.HYBRID; break; default:
return null;}
break; case 'mapquest':
type = map.getMapType(); switch(type) { case "map":
return Mapstraction.ROAD; break; case "sat":
return Mapstraction.SATELLITE; break; case "hyb":
return Mapstraction.HYBRID; break; default:
return null;}
break; default:
if(this.debug)
alert(this.api + ' not supported by Mapstraction.getMapType');}
}
Mapstraction.prototype.getBounds = function () { if(this.loaded[this.api] == false) { return null;}
var map = this.maps[this.api]; switch (this.api) { case 'google':
case 'openstreetmap':
var gbox = map.getBounds(); var sw = gbox.getSouthWest(); var ne = gbox.getNorthEast(); return new BoundingBox(sw.lat(), sw.lng(), ne.lat(), ne.lng()); break; case 'openlayers':
var olbox = map.calculateBounds(); break; case 'yahoo':
var ybox = map.getBoundsLatLon(); return new BoundingBox(ybox.LatMin, ybox.LonMin, ybox.LatMax, ybox.LonMax); break; case 'microsoft':
var mbox = map.GetMapView(); var nw = mbox.TopLeftLatLong; var se = mbox.BottomRightLatLong; return new BoundingBox(se.Latitude,nw.Longitude,nw.Latitude,se.Longitude); break; case 'multimap':
var mmbox = map.getMapBounds(); var sw = mmbox.getSouthWest(); var ne = mmbox.getNorthEast(); return new BoundingBox(sw.lat, sw.lon, ne.lat, ne.lon); break; case 'mapquest':
var mqbox = map.getMapBounds(); var se = mqbox.getLowerRightLatLng(); var nw = mqbox.getUpperLeftLatLng(); return new BoundingBox(se.lat, nw.lon, nw.lat, se.lon); break; case 'map24':
var mv = map.MapClient['Static'].getCurrentMapView(); var se = mv.LowerRight; var nw = mv.TopLeft; return new BoundingBox (se.Latitude/60, nw.Longitude/60, nw.Latitude/60, se.Longitude/60 ); break; default:
if(this.debug)
alert(this.api + ' not supported by Mapstraction.getBounds');}
}
Mapstraction.prototype.setBounds = function(bounds){ if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.setBounds(bounds);} ); return;}
var map = this.maps[this.api]; var sw = bounds.getSouthWest(); var ne = bounds.getNorthEast(); switch (this.api) { case 'google':
case 'openstreetmap':
var gbounds = new GLatLngBounds(new GLatLng(sw.lat,sw.lon),new GLatLng(ne.lat,ne.lon)); map.setCenter(gbounds.getCenter(), map.getBoundsZoomLevel(gbounds)); break; case 'openlayers':
var bounds = new OpenLayers.Bounds(); bounds.extend(new LatLonPoint(sw.lat,sw.lon).toOpenLayers()); bounds.extend(new LatLonPoint(ne.lat,ne.lon).toOpenLayers()); map.zoomToExtent(bounds); break; case 'yahoo':
if(sw.lon > ne.lon)
sw.lon -= 360; var center = new YGeoPoint((sw.lat + ne.lat)/2, (ne.lon + sw.lon)/2); var container = map.getContainerSize(); for(var zoom = 1 ; zoom <= 17 ; zoom++){ var sw_pix = convertLatLonXY_Yahoo(sw,zoom); var ne_pix = convertLatLonXY_Yahoo(ne,zoom); if(sw_pix.x > ne_pix.x)
sw_pix.x -= (1 << (26 - zoom)); if(Math.abs(ne_pix.x - sw_pix.x)<=container.width
&& Math.abs(ne_pix.y - sw_pix.y) <= container.height){ map.drawZoomAndCenter(center,zoom); break;}
}
break; case 'microsoft':
map.SetMapView([new VELatLong(sw.lat,sw.lon),new VELatLong(ne.lat,ne.lon)]); break; case 'multimap':
var mmlocation = map.getBoundsZoomFactor( sw.toMultiMap(), ne.toMultiMap() ); var center = new LatLonPoint(mmlocation.coords.lat, mmlocation.coords.lon); this.setCenterAndZoom(center, mmlocation.zoom_factor); break; case 'mapquest':
if(this.debug)
alert(this.api + ' not supported by Mapstraction.setBounds'); break; case 'freeearth':
var center = new LatLonPoint((sw.lat + ne.lat)/2, (ne.lon + sw.lon)/2); this.setCenter(center); break; case 'map24':
var settings = new Object(); settings.Latitude = ((sw.lat+ne.lat) / 2) * 60; settings.Longitude = ((sw.lon+ne.lon) / 2) * 60; settings.MinimumWidth = lonToMetres
(ne.lon-sw.lon, (ne.lat+sw.lat)/2); Map24.MapApplication.center ( settings ); break; default:
if(this.debug)
alert(this.api + ' not supported by Mapstraction.setBounds');}
}
Mapstraction.prototype.addImageOverlay = function(id, src, opacity, west, south, east, north) { if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.addImageOverlay(id, src, opacity, west, south, east, north);} ); return;}
var map = this.maps[this.api]; var b = document.createElement("img"); b.style.display = 'block'; b.setAttribute('id',id); b.setAttribute('src',src); b.style.position = 'absolute'; b.style.zIndex = 1; b.setAttribute('west',west); b.setAttribute('south',south); b.setAttribute('east',east); b.setAttribute('north',north); switch (this.api) { case 'google':
case 'openstreetmap':
map.getPane(G_MAP_MAP_PANE).appendChild(b); this.setImageOpacity(id, opacity); this.setImagePosition(id); GEvent.bind(map, "zoomend", this, function(){this.setImagePosition(id)}); GEvent.bind(map, "moveend", this, function(){this.setImagePosition(id)}); break; case 'multimap':
map.getContainer().appendChild(b); this.setImageOpacity(id, opacity); this.setImagePosition(id); me = this; map.addEventHandler( 'changeZoom', function(eventType, eventTarget, arg1, arg2, arg3) { me.setImagePosition(id);}); map.addEventHandler( 'drag', function(eventType, eventTarget, arg1, arg2, arg3) { me.setImagePosition(id);}); map.addEventHandler( 'endPan', function(eventType, eventTarget, arg1, arg2, arg3) { me.setImagePosition(id);}); break; default:
b.style.display = 'none'; if(this.debug)
alert(this.api + "not supported by Mapstraction.addImageOverlay not supported"); break;}
}
Mapstraction.prototype.setImageOpacity = function(id, opacity) { if(opacity<0){opacity=0;} if(opacity>=100){opacity=100;}
var c=opacity/100; var d=document.getElementById(id); if(typeof(d.style.filter)=='string'){d.style.filter='alpha(opacity:'+opacity+')';}
if(typeof(d.style.KHTMLOpacity)=='string'){d.style.KHTMLOpacity=c;}
if(typeof(d.style.MozOpacity)=='string'){d.style.MozOpacity=c;}
if(typeof(d.style.opacity)=='string'){d.style.opacity=c;}
}
Mapstraction.prototype.setImagePosition = function(id) { if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.setImagePosition(id);} ); return;}
var map = this.maps[this.api]; var x = document.getElementById(id); var d; var e; switch (this.api) { case 'google':
case 'openstreetmap':
d = map.fromLatLngToDivPixel(new GLatLng(x.getAttribute('north'), x.getAttribute('west'))); e = map.fromLatLngToDivPixel(new GLatLng(x.getAttribute('south'), x.getAttribute('east'))); break; case 'multimap':
d = map.geoPosToContainerPixels(new MMLatLon(x.getAttribute('north'), x.getAttribute('west'))); e = map.geoPosToContainerPixels(new MMLatLon(x.getAttribute('south'), x.getAttribute('east'))); break;}
x.style.top=d.y+'px'; x.style.left=d.x+'px'; x.style.width=e.x-d.x+'px'; x.style.height=e.y-d.y+'px';}
Mapstraction.prototype.addOverlay = function(url, autoCenterAndZoom) { if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.addOverlay(url);} ); return;}
var map = this.maps[this.api]; switch (this.api) { case 'yahoo':
map.addOverlay(new YGeoRSS(url)); break; case 'openstreetmap':
case 'google':
var geoXML = new GGeoXml(url); map.addOverlay(geoXML, function() { if(autoCenterAndZoom) { geoXML.gotoDefaultViewport(map);}
}); break; case 'microsoft':
var veLayerSpec = new VELayerSpecification(); veLayerSpec.Type = VELayerType.GeoRSS; veLayerSpec.ID = 1; veLayerSpec.LayerSource = url; veLayerSpec.Method = 'get'; map.AddLayer(veLayerSpec); break; case 'multimap':
break; case 'freeearth':
if (this.freeEarthLoaded) { var ferss = new FE.GeoRSS(url); map.addOverlay(ferss);} else { myself = this; this.freeEarthOnLoad.push( function() { myself.addOverlay(url);} );}
break; default:
if(this.debug)
alert(this.api + ' not supported by Mapstraction.addGeoRSSOverlay');}
}
Mapstraction.prototype.addTileLayer = function(tile_url, opacity, copyright_text, min_zoom, max_zoom) { if(!tile_url)
return; if (! this.tileLayers) { this.tileLayers = [];}
if(!opacity)
opacity = 0.6; if(!copyright_text)
copyright_text = "Mapstraction"; if(!min_zoom)
min_zoom = 1; if(!max_zoom)
max_zoom = 18; console.log(this.api); switch (this.api) { case 'google':
case 'openstreetmap':
var copyright = new GCopyright(1, new GLatLngBounds(new GLatLng(-90,-180), new GLatLng(90,180)), 0, "copyleft"); var copyrightCollection = new GCopyrightCollection(copyright_text); copyrightCollection.addCopyright(copyright); var tilelayers = new Array(); tilelayers[0] = new GTileLayer(copyrightCollection, min_zoom, max_zoom); tilelayers[0].isPng = function() { return true;}; tilelayers[0].getOpacity = function() { return opacity;}
tilelayers[0].getTileUrl = function (a, b) { url = tile_url; url = url.replace(/\{Z\}/,b); url = url.replace(/\{X\}/,a.x); url = url.replace(/\{Y\}/,a.y); return url
}; tileLayerOverlay = new GTileLayerOverlay(tilelayers[0]); this.tileLayers.push( [tile_url, tileLayerOverlay, true] ); this.maps[this.api].addOverlay(tileLayerOverlay); break;}
return tileLayerOverlay;}
Mapstraction.prototype.toggleTileLayer = function(tile_url) { switch (this.api) { case 'google':
case 'openstreetmap':
for (var f=0; f<this.tileLayers.length; f++) { if(this.tileLayers[f][0] == tile_url) { if(this.tileLayers[f][2]) { this.maps[this.api].removeOverlay(this.tileLayers[f][1]); this.tileLayers[f][2] = false;} else { this.maps[this.api].addOverlay(this.tileLayers[f][1]); this.tileLayers[f][2] = true;}
}
}
break;}
}
Mapstraction.prototype.addFilter = function(field, operator, value) { if (! this.filters) { this.filters = [];}
this.filters.push( [field, operator, value] );}
Mapstraction.prototype.removeFilter = function(field, operator, value) { if (! this.filters) { return;}
var del; for (var f=0; f<this.filters.length; f++) { if (this.filters[f][0] == field &&
(! operator || (this.filters[f][1] == operator && this.filters[f][2] == value))) { this.filters.splice(f,1); f--;}
}
}
Mapstraction.prototype.toggleFilter = function(field, operator, value) { if (! this.filters) { this.filters = [];}
var found = false; for (var f=0; f<this.filters.length; f++) { if (this.filters[f][0] == field && this.filters[f][1] == operator && this.filters[f][2] == value) { this.filters.splice(f,1); f--; found = true;}
}
if (! found) { this.addFilter(field, operator, value);}
}
Mapstraction.prototype.removeAllFilters = function() { this.filters = [];}
Mapstraction.prototype.doFilter = function() { if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.doFilter();} ); return;}
var map = this.maps[this.api]; if (this.filters) { switch (this.api) { case 'multimap':
var mmfilters = []; for (var f=0; f<this.filters.length; f++) { mmfilters.push( new MMSearchFilter( this.filters[f][0], this.filters[f][1], this.filters[f][2] ));}
map.setMarkerFilters( mmfilters ); map.redrawMap(); break; default:
var vis; for (var m=0; m<this.markers.length; m++) { vis = true; for (var f=0; f<this.filters.length; f++) { if (! this.applyFilter(this.markers[m], this.filters[f])) { vis = false;}
}
if (vis) { this.markers[m].show();} else { this.markers[m].hide();}
}
break;}
}
}
Mapstraction.prototype.applyFilter = function(o, f) { var vis = true; switch (f[1]) { case 'ge':
if (o.getAttribute( f[0] ) < f[2]) { vis = false;}
break; case 'le':
if (o.getAttribute( f[0] ) > f[2]) { vis = false;}
break; case 'eq':
if (o.getAttribute( f[0] ) != f[2]) { vis = false;}
break;}
return vis;}
Mapstraction.prototype.getAttributeExtremes = function(field) { var min; var max; for (var m=0; m<this.markers.length; m++) { if (! min || min > this.markers[m].getAttribute(field)) { min = this.markers[m].getAttribute(field);}
if (! max || max < this.markers[m].getAttribute(field)) { max = this.markers[m].getAttribute(field);}
}
for (var p=0; m<this.polylines.length; m++) { if (! min || min > this.polylines[p].getAttribute(field)) { min = this.polylines[p].getAttribute(field);}
if (! max || max < this.polylines[p].getAttribute(field)) { max = this.polylines[p].getAttribute(field);}
}
return [min, max];}
Mapstraction.prototype.getMap = function() { return this.maps[this.api];}
function LatLonPoint(lat,lon) { this.lat = lat; this.lon = lon; this.lng = lon;}
LatLonPoint.prototype.toYahoo = function() { return new YGeoPoint(this.lat,this.lon);}
LatLonPoint.prototype.toGoogle = function() { return new GLatLng(this.lat,this.lon);}
LatLonPoint.prototype.toOpenLayers = function() { var ollon = this.lon * 20037508.34 / 180; var ollat = Math.log(Math.tan((90 + this.lat) * Math.PI / 360)) / (Math.PI / 180); ollat = ollat * 20037508.34 / 180; return new OpenLayers.LonLat(ollon, ollat);}
LatLonPoint.prototype.fromOpenLayers = function() { var lon = (this.lon / 20037508.34) * 180; var lat = (this.lat / 20037508.34) * 180; lat = 180/Math.PI * (2 * Math.atan(Math.exp(lat * Math.PI / 180)) - Math.PI / 2); this.lon = lon; this.lat = lat;}
LatLonPoint.prototype.toMicrosoft = function() { return new VELatLong(this.lat,this.lon);}
LatLonPoint.prototype.toMultiMap = function() { return new MMLatLon(this.lat, this.lon);}
LatLonPoint.prototype.toMapQuest = function() { return new MQLatLng(this.lat, this.lon);}
LatLonPoint.prototype.toFreeEarth = function() { return new FE.LatLng(this.lat,this.lon);}
LatLonPoint.prototype.toMap24 = function() { return new Map24.Point (this.lon,this.lat);}
LatLonPoint.prototype.toString = function() { return this.lat + ', ' + this.lon;}
LatLonPoint.prototype.distance = function(otherPoint) { var d,dr; with (Math) { dr = 0.017453292519943295; d = cos(otherPoint.lon*dr - this.lon*dr) * cos(otherPoint.lat*dr - this.lat*dr); return acos(d)*6378.137;}
return -1;}
LatLonPoint.prototype.equals = function(otherPoint) { return this.lat == otherPoint.lat && this.lon == otherPoint.lon;}
function BoundingBox(swlat, swlon, nelat, nelon) { this.sw = new LatLonPoint(swlat, swlon); this.ne = new LatLonPoint(nelat, nelon);}
BoundingBox.prototype.getSouthWest = function() { return this.sw;}
BoundingBox.prototype.getNorthEast = function() { return this.ne;}
BoundingBox.prototype.isEmpty = function() { return this.ne == this.sw;}
BoundingBox.prototype.contains = function(point){ return point.lat >= this.sw.lat && point.lat <= this.ne.lat && point.lon>= this.sw.lon && point.lon <= this.ne.lon;}
BoundingBox.prototype.toSpan = function() { return new LatLonPoint( Math.abs(this.sw.lat - this.ne.lat), Math.abs(this.sw.lon - this.ne.lon) );}
BoundingBox.prototype.extend = function(point) { if(this.sw.lat > point.lat)
this.sw.lat = point.lat; if(this.sw.lon > point.lon)
this.sw.lon = point.lon; if(this.ne.lat < point.lat)
this.ne.lat = point.lat; if(this.ne.lon < point.lon)
this.ne.lon = point.lon; return;}
function Marker(point) { this.location = point; this.onmap = false; this.proprietary_marker = false; this.attributes = new Array(); this.pinID = "mspin-"+new Date().getTime()+'-'+(Math.floor(Math.random()*Math.pow(2,16)));}
Marker.prototype.setChild = function(some_proprietary_marker) { this.proprietary_marker = some_proprietary_marker; this.onmap = true
}
Marker.prototype.setLabel = function(labelText) { this.labelText = labelText;}
Marker.prototype.addData = function(options){ if(options.label)
this.setLabel(options.label); if(options.infoBubble)
this.setInfoBubble(options.infoBubble); if(options.icon) { if(options.iconSize)
this.setIcon(options.icon, new Array(options.iconSize[0], options.iconSize[1])); else
this.setIcon(options.icon); if(options.iconAnchor)
this.setIconAnchor(new Array(options.iconAnchor[0], options.iconAnchor[1]));}
if(options.iconShadow) { if(options.iconShadowSize)
this.setShadowIcon(options.iconShadow, new Array(options.iconShadowSize[0], options.iconShadowSize[1])); else
this.setIcon(options.iconShadow);}
if(options.infoDiv)
this.setInfoDiv(options.infoDiv[0],options.infoDiv[1]); if(options.draggable)
this.setDraggable(options.draggable); if(options.hover)
this.setHover(options.hover); if(options.hoverIcon)
this.setHoverIcon(options.hoverIcon); if(options.openBubble)
this.openBubble(); if(options.date)
this.setAttribute( 'date', eval(options.date) ); if(options.category)
this.setAttribute( 'category', options.category );}
Marker.prototype.setInfoBubble = function(infoBubble) { this.infoBubble = infoBubble;}
Marker.prototype.setInfoDiv = function(infoDiv,div){ this.infoDiv = infoDiv; this.div = div;}
Marker.prototype.setIcon = function(iconUrl, iconSize, iconAnchor){ this.iconUrl = iconUrl; if(iconSize)
this.iconSize = iconSize; if(iconAnchor)
this.iconAnchor = iconAnchor;}
Marker.prototype.setIconSize = function(iconSize){ if(iconSize)
this.iconSize = iconSize;}
Marker.prototype.setIconAnchor = function(iconAnchor){ if(iconAnchor)
this.iconAnchor = iconAnchor;}
Marker.prototype.setShadowIcon = function(iconShadowUrl, iconShadowSize){ this.iconShadowUrl = iconShadowUrl; if(iconShadowSize)
this.iconShadowSize = iconShadowSize;}
Marker.prototype.setHoverIcon = function(hoverIconUrl){ this.hoverIconUrl = hoverIconUrl;}
Marker.prototype.setDraggable = function(draggable) { this.draggable = draggable;}
Marker.prototype.setHover = function(hover) { this.hover = hover;}
Marker.prototype.changeIcon = function(iconUrl) { if (this.proprietary_marker) { this.proprietary_marker.setImage(iconUrl);}
}
Marker.prototype.revertIcon = function() { this.changeIcon(this.iconUrl);}
Marker.prototype.toYahoo = function() { var ymarker; if(this.iconUrl) { ymarker = new YMarker(this.location.toYahoo (),new YImage(this.iconUrl));} else { ymarker = new YMarker(this.location.toYahoo());}
if(this.iconSize) { ymarker.size = new YSize(this.iconSize[0], this.iconSize[1]);}
if(this.labelText) { ymarker.addLabel(this.labelText);}
if(this.infoBubble) { var theInfo = this.infoBubble; var event_action; if(this.hover) { event_action = EventsList.MouseOver;}
else { event_action = EventsList.MouseClick;}
YEvent.Capture(ymarker, event_action, function() { ymarker.openSmartWindow(theInfo);});}
if(this.infoDiv) { var theInfo = this.infoDiv; var div = this.div; var event_div; if(this.hover) { event_action = EventsList.MouseOver;}
else { event_action = EventsList.MouseClick;}
YEvent.Capture(ymarker, event_action, function() { document.getElementById(div).innerHTML = theInfo;});}
return ymarker;}
Marker.prototype.toGoogle = function() { var options = new Object(); if(this.labelText) { options.title = this.labelText;}
if(this.iconUrl){ var icon = new GIcon(G_DEFAULT_ICON,this.iconUrl); if(this.iconSize) { icon.iconSize = new GSize(this.iconSize[0], this.iconSize[1]); var anchor; if(this.iconAnchor) { anchor = new GPoint(this.iconAnchor[0], this.iconAnchor[1]);}
else { anchor = new GPoint(this.iconSize[0]/2, this.iconSize[1]/2);}
icon.iconAnchor = anchor;}
if(this.iconShadowUrl) { icon.shadow = this.iconShadowUrl; if(this.iconShadowSize) { icon.shadowSize = new GSize(this.iconShadowSize[0], this.iconShadowSize[1]);}
}
options.icon = icon;}
if(this.draggable){ options.draggable = this.draggable;}
var gmarker = new GMarker( this.location.toGoogle(),options); if(this.infoBubble) { var theInfo = this.infoBubble; var event_action; if(this.hover) { event_action = "mouseover";}
else { event_action = "click";}
GEvent.addListener(gmarker, event_action, function() { gmarker.openInfoWindowHtml(theInfo, {maxWidth: 100});});}
if(this.hoverIconUrl) { GEvent.addListener(gmarker, "mouseover", function() { gmarker.setImage(this.hoverIconUrl);}); GEvent.addListener(gmarker, "mouseout", function() { gmarker.setImage(this.iconUrl);});}
if(this.infoDiv){ var theInfo = this.infoDiv; var div = this.div; var event_action; if(this.hover) { event_action = "mouseover";}
else { event_action = "click";}
GEvent.addListener(gmarker, event_action, function() { document.getElementById(div).innerHTML = theInfo;});}
return gmarker;}
Marker.prototype.toOpenLayers = function() { if(this.iconSize) { var size = new OpenLayers.Size(this.iconSize[0], this.iconSize[1]);}
else
{ var size = new OpenLayers.Size(15,20);}
if(this.iconAnchor)
{ var anchor = new OpenLayers.Pixel(this.iconAnchor[0], this.iconAnchor[1]);}
else
{ anchor = new OpenLayers.Pixel(-(size.w/2), -size.h);}
if(this.iconUrl) { var icon = new OpenLayers.Icon(this.iconUrl, size, anchor);}
else
{ var icon = new OpenLayers.Icon('http://boston.openguides.org/markers/AQUA.png', size, anchor);}
var marker = new OpenLayers.Marker(this.location.toOpenLayers(), icon); return marker;}
Marker.prototype.toMicrosoft = function() { var pin = new VEPushpin(this.pinID,this.location.toMicrosoft(), this.iconUrl,this.labelText,this.infoBubble); return pin;}
Marker.prototype.toMap24 = function() { var ops = new Object(); ops.Longitude = this.location.lon*60; ops.Latitude = this.location.lat*60; if(this.infoBubble) { ops.TooltipContent = this.infoBubble;}
if(this.labelText) { }
ops.LogoURL = this.iconUrl ? this.iconUrl :
"http://www.free-map.org.uk/images/marker.png"; ops.TooltipLayout = Map24.MapObject.LAYOUT_BUBBLE; if(this.hover) { ops.TooltipOpen = "OnMouseOver";} else { ops.TooltipOpen = "OnClick";}
var m24Location = new Map24.Location ( ops ); return m24Location;}
Marker.prototype.toMultiMap = function() { if (this.iconUrl) { var icon = new MMIcon(this.iconUrl); icon.iconSize = new MMDimensions(32, 32); var mmmarker = new MMMarkerOverlay( this.location.toMultiMap(), {'icon' : icon} );} else { var mmmarker = new MMMarkerOverlay( this.location.toMultiMap());}
if(this.labelText){ }
if(this.infoBubble) { mmmarker.setInfoBoxContent(this.infoBubble);}
if(this.infoDiv) { }
for (var key in this.attributes) { mmmarker.setAttribute(key, this.attributes[ key ]);}
return mmmarker;}
Marker.prototype.toMapQuest = function() { var mqmarker = new MQPoi( this.location.toMapQuest() ); if(this.iconUrl){ var mqicon = new MQMapIcon(); mqicon.setImage(this.iconUrl,32,32,true,false); mqmarker.setIcon(mqicon);}
if(this.labelText) { mqmarker.setInfoTitleHTML( this.labelText );}
if(this.infoBubble) { mqmarker.setInfoContentHTML( this.infoBubble );}
if(this.infoDiv){ var theInfo = this.infoDiv; var div = this.div; MQEventManager.addListener(mqmarker, "click", function() { document.getElementById(div).innerHTML = theInfo;});}
return mqmarker;}
Marker.prototype.toFreeEarth = function() { var feicon; if (this.iconUrl) { feicon = new FE.Icon(this.iconUrl);} else { feicon = new FE.Icon("http://freeearth.poly9.com/images/bullmarker.png");}
var femarker = new FE.Pushpin( this.location.toFreeEarth(), feicon); if(this.infoBubble) { var theBubble = this.infoBubble; FE.Event.addListener(femarker, 'click', function() { femarker.openInfoWindowHtml( theBubble, 200, 100 );} );}
if(this.infoDiv) { var theInfo = this.infoDiv; var div = this.div; FE.Event.addListener(femarker, 'click', function() { document.getElementById(div).innerHTML = theInfo;});}
return femarker;}
Marker.prototype.setAttribute = function(key,value) { this.attributes[key] = value;}
Marker.prototype.getAttribute = function(key) { return this.attributes[key];}
Marker.prototype.openBubble = function() { if(this.mapstraction.loaded[this.api] == false) { var my_marker = this; this.mapstraction.onload[this.api].push( function() { my_marker.openBubble();} ); return;}
if( this.api) { switch (this.api) { case 'yahoo':
var ypin = this.proprietary_marker; ypin.openSmartWindow(this.infoBubble); break; case 'google':
case 'openstreetmap':
var gpin = this.proprietary_marker; gpin.openInfoWindowHtml(this.infoBubble); break; case 'microsoft':
var pin = this.proprietary_marker; var el = $m(this.pinID + "_" + this.maps[this.api].GUID).onmouseover; setTimeout(el, 1000); break; case 'multimap':
this.proprietary_marker.openInfoBox(); break; case 'mapquest':
this.proprietary_marker.setRolloverEnabled(false); this.proprietary_marker.showInfoWindow(); this.proprietary_marker.setRolloverEnabled(true); break;}
} else { alert('You need to add the marker before opening it');}
}
Marker.prototype.hide = function() { if (this.api) { switch (this.api) { case 'google':
case 'openstreetmap':
this.proprietary_marker.hide(); break; case 'openlayers':
this.proprietary_marker.display(false); break; case 'yahoo':
this.proprietary_marker.hide(); break; case 'map24':
this.proprietary_marker.hide(); break; case 'multimap':
this.proprietary_marker.setVisibility(false); break; case 'mapquest':
this.proprietary_marker.setVisible(false); break; default:
if(this.debug)
alert(this.api + "not supported by Marker.hide");}
}
}
Marker.prototype.show = function() { if (this.api) { switch (this.api) { case 'google':
case 'openstreetmap':
this.proprietary_marker.show(); break; case 'openlayers':
this.proprietary_marker.display(true); break; case 'map24':
this.proprietary_marker.show(); break; case 'yahoo':
this.proprietary_marker.unhide(); break; case 'multimap':
this.proprietary_marker.setVisibility(true); break; case 'mapquest':
this.proprietary_marker.setVisible(true); break; default:
if(this.debug)
alert(this.api + "not supported by Marker.show");}
}
}
function Polyline(points) { this.points = points; this.attributes = new Array(); this.onmap = false; this.proprietary_polyline = false; this.pllID = "mspll-"+new Date().getTime()+'-'+(Math.floor(Math.random()*Math.pow(2,16)));}
Polyline.prototype.addData = function(options){ if(options.color)
this.setColor(options.color); if(options.width)
this.setWidth(options.width); if(options.opacity)
this.setIcon(options.opacity); if(options.date)
this.setAttribute( 'date', eval(options.date) ); if(options.category)
this.setAttribute( 'category', options.category );}
Polyline.prototype.setChild = function(some_proprietary_polyline) { this.proprietary_polyline = some_proprietary_polyline; this.onmap = true;}
Polyline.prototype.setColor = function(color){ this.color = (color.length==7 && color[0]=="#") ? color.toUpperCase() : color;}
Polyline.prototype.setWidth = function(width){ this.width = width;}
Polyline.prototype.setOpacity = function(opacity){ this.opacity = opacity;}
Polyline.prototype.toYahoo = function() { var ypolyline; var ypoints = []; for (var i = 0, length = this.points.length ; i< length; i++){ ypoints.push(this.points[i].toYahoo());}
ypolyline = new YPolyline(ypoints,this.color,this.width,this.opacity); return ypolyline;}
Polyline.prototype.toGoogle = function() { var gpolyline; var gpoints = []; for (var i = 0, length = this.points.length ; i< length; i++){ gpoints.push(this.points[i].toGoogle());}
gpolyline = new GPolyline(gpoints,this.color,this.width,this.opacity); return gpolyline;}
Polyline.prototype.toMap24 = function() { var m24polyline; var m24longs = ""; var m24lats = ""; for (var i=0; i<this.points.length; i++) { if(i) { m24longs += "|"; m24lats += "|";}
m24longs += (this.points[i].lon*60); m24lats += (this.points[i].lat*60);}
m24polyline = new Map24.Polyline({ Longitudes: m24longs, Latitudes: m24lats, Color: this.color || "black", Width: this.width || 3
}); return m24polyline;}
Polyline.prototype.toMicrosoft = function() { var mpolyline; var mpoints = []; for (var i = 0, length = this.points.length ; i< length; i++){ mpoints.push(this.points[i].toMicrosoft());}
var color; var opacity = this.opacity ||1.0; if(this.color){ color = new VEColor(parseInt(this.color.substr(1,2),16),parseInt(this.color.substr(3,2),16),parseInt(this.color.substr(5,2),16), opacity);}else{ color = new VEColor(0,255,0, opacity);}
mpolyline = new VEPolyline(this.pllID,mpoints,color,this.width); return mpolyline;}
Polyline.prototype.toMultiMap = function() { var mmpolyline; var mmpoints = []; for (var i = 0, length = this.points.length ; i< length; i++){ mmpoints.push(this.points[i].toMultiMap());}
mmpolyline = new MMPolyLineOverlay(mmpoints, this.color, this.opacity, this.width, false, undefined); return mmpolyline;}
Polyline.prototype.toMapQuest = function() { var mqpolyline = new MQLineOverlay(); mqpolyline.setColor(this.color||"red"); mqpolyline.setBorderWidth(this.width || 3); mqpolyline.setKey("Line"); mqpolyline.setColorAlpha(this.opacity); var mqpoints = new MQLatLngCollection(); for (var i = 0, length = this.points.length ; i< length; i++){ mqpoints.add(this.points[i].toMapQuest());}
mqpolyline.setShapePoints(mqpoints); return mqpolyline;}
Polyline.prototype.toFreeEarth = function() { var fepoints = new Array(); for (var i = 0, length = this.points.length ; i< length; i++){ fepoints.push(this.points[i].toFreeEarth());}
var fepolyline = new FE.Polyline(fepoints, this.color || '0xff0000', this.width || 1, this.opacity || 1); return fepolyline;}
Polyline.prototype.setAttribute = function(key,value) { this.attributes[key] = value;}
Polyline.prototype.getAttribute = function(key) { return this.attributes[key];}
Polyline.prototype.show = function() { if (this.api) { }
}
Polyline.prototype.hide = function() { if (this.api) { }
}
Mapstraction.prototype.showRoute = function(route) { if(this.loaded[this.api] == false) { myself = this; this.onload[this.api].push( function() { myself.showRoute(route);} ); return;}
var map = this.maps[this.api]; switch (this.api) { case 'mapquest':
map.addRouteHighlight(route['bounding_box'],"http://map.access.mapquest.com",route['session_id'],true); break; default:
if(this.debug)
alert(api + ' not supported by Mapstration.showRoute'); break;}
}