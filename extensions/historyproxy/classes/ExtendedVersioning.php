<?php
/**
 * This file is part of the {@link http://erfurt-framework.org Erfurt} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */


class Extended_Erfurt_Versioning extends Erfurt_Versioning
{
    public function getRangeOfModifiedResources( $graphUri, $from, $to ) {
        require_once 'Zend/Uri.php';

        // since $to is set to the beginning of the day, we need query
        // everything until the beginning of the day after.
        $nextDay = date( 'd-m-Y', strtotime( ' +1 day', strtotime( $to ) ) );


        $sql = 'SELECT DISTINCT id, resource, useruri, tstamp, action_type ' .
            'FROM ef_versioning_actions WHERE
        model = \'' . $graphUri . '\' AND
        tstamp >= \'' . strtotime( $from ) . '\' AND
        tstamp < \'' . strtotime( $nextDay ) . '\' AND
        parent IS NULL';
        $result = $this->_sqlQuery( $sql );

        return $result;
    }
}
