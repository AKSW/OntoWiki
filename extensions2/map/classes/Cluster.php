<?php
// vim: sw=4:sts=4:expandtab

require_once $this->_componentRoot.'classes/Marker.php';

/**
 * Cluster-Class of the OW MapPlugin
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_map
 * @author OW MapPlugin-Team <mashup@comiles.eu>
 * @version 1.0.0
 * @package MapPlugin
 */
class Cluster extends Marker {

    /**
     * set isCluster on true, to identify this object as cluster in the json serialization
     * -- not implemented in javascript --
     */
    private $isCluster = true;

    /**
     * The array of the markers in this cluster
     */
    public $containingMarkers = array();

    /**
     * The area of the containing markers
     */
    private $area = array();

    /**
     * A boolean telling, if the cell of this cluster contains the dateline
     */
    private $withDateLine;

    /**
     * Set the inherited inCluster false, because a cluster can't be in a cluster
     */
    private static $inCluster = false;

    /**
     * Constructor of a Cluster object.
     */
    public function __construct($area) {
        parent::__construct( null );
        $this->area = $area;
    }

    /**
     * Destructor of a Cluster object. Sets the inCluster-attribute of the 
     * contained markers to false.
     */
    public function __destruct( ) {
        for($i = 0; $i < count( $this->containingMarkers ); $i++ ) {
            $this->containingMarkers[$i]->setInCluster( false );
        }
    }

    /**
     * Adds a marker to the cluster.
     * @param &$marker a reference of a Marker object
     */
    public function addMarker( &$marker ) {
        $this->containingMarkers[] = &$marker;
    }

    /**
     * Returns the count of the markers in the cluster.
     * @return an integer
     */
    public function countMarkers( ) {
        return count( $this->containingMarkers );
    }

    /**
     * Calculates latitude and longitude for the cluster (arithmetic mean).
     */
    public function createLonLat( ) {

        /**
         * Check if the longitude and latitude is already set.
         */
        if(!isset($this->longitude) OR !isset($this->latitude)){

            /**
             * Adding up the latitude respactively longitude of all markers contained by the cluster
             */
            $lonTmp = 0;
            $latTmp = 0;
            for($i = 0; $i < count($this->containingMarkers); $i++){
                $lonTmp += $this->containingMarkers[$i]->getLon( );
                $latTmp += $this->containingMarkers[$i]->getLat( );
            }

            /**
             * Divide by the amount of the contained markers to calcule the arithmetic mean
             */
            if(count($this->containingMarkers) > 0){
                $lonTmp /= count($this->containingMarkers);
                $latTmp /= count($this->containingMarkers);
            }

            /**
             * Check if the cluster contains markers from the right and from the left side of the dateline
             */
            if($this->withDateLine) {

                /**
                 * If the dateline is in the cluster turn the lon half around the world
                 */
                if($lonTmp > 0) {
                    $lonTmp -= 180;
                }
                else {
                    $lonTmp += 180;
                }
            }

            /**
             * Set the calculated attributes
             */
            $this->setLon($lonTmp);
            $this->setLat($latTmp);
        }
    }

    /**
     * Get the Markers in the Cluster.
     * @return an array of marker objects
     */
    public function getContent( ) {
        return $this->containingMarkers;
    }

    /**
     * Set a value if the dateline is in the cluster or not
     * @param $var boolean
     */
    public function setWithDateLine( $var ) {
        $this->withDateLine = $var;
    }

}
?>
