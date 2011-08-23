<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki DisplayLiteralPropertyValue view helper
 *
 * filters / modifies a given literal value by using the extensions which 
 * listen to the onDisplayLiteralPropertyValue event
 *
 * @category OntoWiki
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class Site_View_Helper_DisplayLiteralPropertyValue extends Zend_View_Helper_Abstract
{
    public function displayLiteralPropertyValue($value = null, $datatype = '', $property = '')
    {
        if (!$value) {
            $newValue = '';
        } else {
            $event           = new Erfurt_Event('onDisplayLiteralPropertyValue');
            $event->value    = $value;
            $event->datatype = $datatype;
            $event->property = $property;
            $newValue        = $event->trigger();
        }
        return $newValue;
    }
}
