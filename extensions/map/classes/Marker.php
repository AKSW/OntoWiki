<?php
// vim: sw=4:sts=4:expandtab
/**
 * Marker-Class of the OW MapPlugin
 *
 * a Marker is an object representing an ontology object. This marker object
 * will later be shown on a map.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_map
 * @author OW MapPlugin-Team <mashup@comiles.eu>
 * @version 1.0.0
 * @package MapPlugin
 */
class Marker {

    /**
     * The geo location (normaly on the earth)
     */
    public $longitude, $latitude;

    /**
     * The resource identifier of the resource represented by this marker
     */
    public $uri;

    /**
     *  status properties indeicating, if a marker is on the Map (visibility) and if a marker is clustered and represented by a cluster on the map
     */
    private $onMap, $inCluster;

    /**
     *  The url of the icon for the marker
     */
    public $icon;

    /**
     * Constructor of a Marker object.
     * @param $uri uri of the resource, which is represented by the marker
     */
    public function __construct( $uri ) {
        $this->uri = $uri;
    }

    /**
     * Gets the uri of the object, which is represented by the marker.
     * @return uri of the object, which is represented by the marker
     */
    public function getUri() {
        return $this->uri;
    }

    /**
     * Sets the onMap-attribute, which indicates whether the marker is already 
     * added to the map.
     * @param $onMapIn indicates whether the marker is already added to the map
     */
    public function setOnMap( $onMapIn ) {
        $this->onMap = $onMapIn;
    }

    /**
     * Gets the onMap-attribute, which indicates whether the marker is already 
     * added to the map.
     * @return marker is already added to the map
     */
    public function getOnMap( ) {
        return $this->onMap;
    }

    /**
     * Sets the inCluster-attribute, which indicates whether the marker is 
     * contained in a cluster.
     * @param $inClusterIn indicates whether the marker is contained in a 
     * cluster
     */
    public function setInCluster( $inClusterIn ) {
        $this->inCluster = $inClusterIn;
    }

    /**
     * Gets the inCluster-attribute, which indicates whether the marker is 
     * contained in a cluster.
     * @return marker is contained in a cluster
     */
    public function getInCluster( ) {
        return $this->inCluster;
    }

    /**
     * Sets the longitude of the marker.
     * @param $lon longitude of the marker
     */
    public function setLon( $lon ) {
        $this->longitude = $lon;
    }

    /**
     * Sets the latitude of the marker.
     * @param $lon latitude of the marker
     */
    public function setLat( $lat ) {
        $this->latitude = $lat;
    }

    /**
     * Gets the longitude of the marker.
     * @return longitude of the marker
     */
    public function getLon( ) {
        return $this->longitude;
    }

    /**
     * Gets the latitude of the marker.
     * @return latitude of the marker
     */
    public function getLat( ) {
        return $this->latitude;
    }

    /**
     * Sets the icon of the marker.
     * @param $icon of the marker
     */
    public function setIcon( $icon ) {
        $this->icon = $icon;
    }

    /**
     * Gets the icon of the marker.
     * @return icon of the marker
     */
    public function getIcon( ) {
        return $this->icon;
    }

}
?>
