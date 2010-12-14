<?php

require_once 'OntoWiki/Controller/Component.php';

/**
 * Mass Geocoding of Ressources via attributes (parameter r)
 *
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_geocoder
 * @author     Michael Martin <martin@informatik.uni-leipzig.de>
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id$
 */
class GeocoderController extends OntoWiki_Controller_Component
{
    private $model = null;
    private $translate = null;

    // ------------------------------------------------------------------------
    // --- Component initialization -------------------------------------------
    // ------------------------------------------------------------------------
    public function init()
    {
        parent::init();
        // m is automatically used and selected
        if ((!isset($this->_request->m)) && (!$this->_owApp->selectedModel)) {
            require_once 'OntoWiki/Exception.php';
            throw new OntoWiki_Exception('No model pre-selected and missing parameter m (model)!');
            exit;
        } else {
            $this->model = $this->_owApp->selectedModel;
        }

        // disable tabs
        require_once 'OntoWiki/Navigation.php';
        OntoWiki_Navigation::disableNavigation();

        // get translation object
        $this->translate = $this->_owApp->translate;

        //set title of main window ...
        $this->view->placeholder('main.window.title')->set($this->translate->_('Geo Coder', $this->_config->languages->locale));
    }

    /**
     * initialization of the geocoder Action
     * @access private
     *
     */
    public function initAction() {
        // create a new button on the toolbar
        $toolbar = $this->_owApp->toolbar;
        $toolbar->appendButton(
            OntoWiki_Toolbar::SUBMIT,
            array('name' => $this->translate->_('Start GeoCoder', $this->_config->languages->locale), 'id' => 'geocoder')
        );
        $this->view->placeholder('main.window.toolbar')->set($toolbar);
        $this->view->formActionUrl = $this->_config->urlBase . 'geocoder/google';
        $this->view->formMethod = 'post';
        $this->view->formName = 'geocoder';
        $this->view->query = $this->_request->getParam('query', '');
        $this->view->prefixes = $this->_owApp->selectedModel->getNamespacePrefixes();
        $this->view->example = $this->_privateConfig->exampleQuery;
    }

    /**
     * Specific action which receives geo coordinates from google geocoder by specific addressdata delivered by a SPARQL Query.
     * Received data will be saved as lon/lat as properties to the given Uri (uri also assigned in the SPARQL query)
     * @access private
     * @author     Michael Martin <martin@informatik.uni-leipzig.de>
     * 
     */
    public function googleAction() {

        $query = "";
        // fetch query parameter
        if (isset($this->_request->query)) {
            $query = $this->_request->getParam('query', null, true);
        } else {

            $this->_response->setRawHeader('HTTP/1.0 400 Bad Request');
            echo '400 Bad Request - No Query Parameter in Request found.';
            exit;
        }

        $toolbar = $this->_owApp->toolbar;
        $toolbar->appendButton(
            OntoWiki_Toolbar::SUBMIT,
            array('name' => $this->translate->_('Back', $this->_config->languages->locale), 'id' => 'geocoder')
        );
        $this->view->placeholder('main.window.toolbar')->set($toolbar);
        $this->view->formActionUrl = $this->_config->urlBase . 'geocoder/init';
        $this->view->formMethod = 'post';
        $this->view->formName = 'geocoder';
        $this->view->query = $query;

        $this->minAccuracy = 8;
        if (isset($this->_request->minaccuracy)) {
            $acc = (int) $this->_request->minaccuracy;
            if ($acc >= 0 && $acc <= 8) {
                $this->minAccuracy = $acc;
            }
        }
		$simpleQuery = Erfurt_Sparql_SimpleQuery::initWithString($query);

		$options['result_format'] = "plain";
		$options['use_ac'] = false;
        try {
    		$result = $this->model->sparqlQuery( $simpleQuery,  $options );
        } catch (Exception $e) {
            $this->_owApp->appendMessage(new OntoWiki_Message(
                ($this->translate->_('Query is not valid. Received Message:', $this->_config->languages->locale)).$e->getMessage(), 
                OntoWiki_Message::ERROR));
            return false;
        }

        if (sizeOf($result) == 0)
        {
            $this->_owApp->appendMessage(new OntoWiki_Message(
                ($this->translate->_('No Data found for this query.', $this->_config->languages->locale)), 
                OntoWiki_Message::ERROR));
            return false;
        }





        require_once 'OntoWiki/Model/TitleHelper.php';
        $titleHelper = new OntoWiki_Model_TitleHelper($this->_owApp->selectedModel);
        $titleHelper->addResources($result, 'uri');

        foreach ($result as $entry) {

            $uri = array_shift($entry);

            $resourceLabel = $titleHelper->getTitle($uri);
            $resourceURL = new OntoWiki_Url(array('route' => 'properties'), array('r'));
            $resourceURL->setParam('r', $uri, true);
            $resourceLink = '<a href="'.$resourceURL.'">'.$resourceLabel.'</a>';
            $address = implode (" ", $entry);
            $coordinates = $this->_getCoordinatesFromGoogle($address);
            if ($coordinates['statusCode'] == 620) {
                //geocoding day limit reached
                $this->_owApp->appendMessage(new OntoWiki_Message($this->translate->_('GEOCODER_GOOGLE_STATUS_620', $this->_config->languages->locale), OntoWiki_Message::ERROR));
                break;
            }

                $writeResult = $this->_writeCoordinates($uri, $coordinates);
                if ($writeResult == true) {
                    $message = $this->translate->_('Accurate coordinates received for resource %1$s. 2 Statements were added.', $this->_config->languages->locale);
                    $this->_owApp->appendMessage(
                        new OntoWiki_Message(sprintf($message, $resourceLink), OntoWiki_Message::SUCCESS,array('escape'    => false))
                    );
                } else {
                    $message = $this->translate->_('Received data of resource %1$s from Google Geocoder contains response: %2$s. No Statements were added.', $this->_config->languages->locale);
                    $statuscodeDesc = $this->translate->_('GEOCODER_GOOGLE_STATUS_'.$coordinates['statusCode'],$this->_config->languages->locale);
                    $response = "STATUSCODE: ".$coordinates['statusCode']."( ".$statuscodeDesc." ), ACCURACY:".$coordinates['accuracy'];
                    $this->_owApp->appendMessage(
                        new OntoWiki_Message(sprintf($message, $resourceLink, $response), OntoWiki_Message::ERROR,array('escape'    => false))
                    );
                }
        }
    }

    /**
     * Google Geocoder Accessor 
     * @access private
     * @author     Michael Martin <martin@informatik.uni-leipzig.de>
     * @param string    $address    Address line which will be send to geocoder
     * @return array   $latLng     Array of geocoder results with statuscode, longitude, latitude, accuracy
     * 
     */
    private function _getCoordinatesFromGoogle( $address = "") {
        $googleMapKey = $this->_privateConfig->googleMapKey;
		$addressData	= urlencode( $address);
		$geocoder		= "http://maps.google.nl/maps/geo?q=".$addressData."&output=xml&key=".$googleMapKey;
		$kml			= file_get_contents( $geocoder );
		$xml			= new SimpleXMLElement( utf8_encode( $kml ) );

		$latLng			= array(
			'statusCode'	=> 0,
			'longitude'		=> 0,
			'latitude'		=> 0,
			'accuracy'		=> 0,
		);

        if (sizeof($xml->Response->Placemark) > 1) {
            $latLng['statusCode'] = 100;
            return $latLng;
        }

		$statusCode		= (string) $xml->Response->Status->code;
        $latLng['statusCode'] = $statusCode;


		if( $statusCode == 200) {

			list( $longitude, $latitude, $altitude ) = explode( ",", $xml->Response->Placemark->Point->coordinates );
			$latLng['longitude']	= $longitude;
			$latLng['latitude']		= $latitude;
			$attributes				= $xml->Response->Placemark->AddressDetails->attributes();
			foreach( $attributes as $key => $value ) {
				if( $key == "Accuracy" ) {
					$latLng['accuracy']	= (string) $value;
                }
            }
		}
		return $latLng;
    }

    /**
     * Function for writing newly received GeoCoordinates to the model
     * @access private
     * @author     Michael Martin <martin@informatik.uni-leipzig.de>
     * @param string    $uri    ResourceUri
     * @param array     $coordinates 
     * @return boolean  
     * 
     */
    private function _writeCoordinates($uri, $coordinates) {
        if ($coordinates["statusCode"] == 200 && ($coordinates["accuracy"] >= $this->minAccuracy) ) {

            $predicates = array();
			$longitude = array(
				'value'	=> $coordinates['longitude'],
				'type'	=> 'literal',
                'datatype' => "xsd:float"
			);
			
			$latitude = array(
				'value'	=> $coordinates['latitude'],
				'type'	=> 'literal',
                'datatype' => "xsd:float"
			);

            $predicates[$this->_privateConfig->longitude][] = $longitude;
            $predicates[$this->_privateConfig->latitude][] = $latitude;
		    $statements = array( $uri => $predicates );

            $versioning                 = $this->_erfurt->getVersioning();
            $actionSpec                 = array();
            $actionSpec['type']         = 666;
            $actionSpec['modeluri']     = (string) $this->_owApp->selectedModel;
            $actionSpec['resourceuri']  = $uri;

            $versioning->startAction($actionSpec);
            $result = $this->model->addMultipleStatements( $statements );
            $versioning->endAction($actionSpec);
            return true;
		}
        return false;
    }
}

