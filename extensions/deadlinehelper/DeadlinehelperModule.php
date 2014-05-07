<?php

/**
 * OntoWiki deadline helper module
 *
 * A simple helper that looks for dates to come and displays them in a module
 *
 * @category OntoWiki
 * @package Extensions_Map
 * @author Norman Radkte <radtke@informatik.uni-leipzig.de>
 * @copyright Copyright (c) 2014, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class DeadlinehelperModule extends OntoWiki_Module
{
    public function getTitle()
    {
        return $this->_owApp->translate->_('Important dates');
    }

    public function shouldShow()
    {
        return true;
    }

    /**
     * Get the map content
     */
    public function getContents()
    {
        $properties = array();
        if (isset($this->_privateConfig->properties)) {
            $properties = $this->_privateConfig->properties;
        }
        if (count($properties) > 0) {
            $n = 1;

            // Write query
            $sparql = 'SELECT ?subject ?property ?value WHERE {' . PHP_EOL;
            $sparql.= '?subject ?property ?value .' . PHP_EOL;
            $sparql.= 'FILTER (' . PHP_EOL;
            foreach($properties as $setup) {
                if ($n >1) {
                    $sparql .= '|| ' . PHP_EOL;
                }
                $sparql.= '?property = <' . $setup->property . '> ' . PHP_EOL;
                $n++;
            }
            $sparql.= ')' . PHP_EOL;
            $sparql.= '}' . PHP_EOL;
            $sparql.= 'ORDER BY ASC (?value)' . PHP_EOL;

            $results  = $this->_owApp->selectedModel->sparqlQuery($sparql);

            if (empty($results)) {
                $this->view->noResult = true;
                return $this->render('deadlinehelper');
            }

            $data = array();
            $url = new OntoWiki_Url(array('controller'=>'resource', 'action'=>'properties'));

            $titleHelper = new OntoWiki_Model_TitleHelper($this->_owApp->selectedModel);
            $found = false;

            // keep future dates and drop past dates
            foreach($results as &$property) {
                $date = strtotime($property['value']);
                if ($date === false) {
                    continue;
                }

                if ($date > strtotime("now")) {
                    $data[] = array ('subjectTitle'   => $titleHelper->getTitle($property['subject']),
                                     'subjectUrl'     => $url->setParam('r', $property['subject']),
                                     'predicateTitle' => $titleHelper->getTitle($property['property']),
                                     'predicateUrl'   => $url->setParam('r', $property['property']),
                                     'value'          => $property['value']);
                    $found = true;
                }
            }

            if ($found === true) {
                $this->view->properties = $data;
            } else {
                $this->view->noResult = true;
            }
        return $this->render('deadlinehelper');
        }
    }

}
