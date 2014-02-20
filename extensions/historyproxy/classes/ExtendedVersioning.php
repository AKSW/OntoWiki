<?php
/**
 * This file is part of the {@link http://erfurt-framework.org Erfurt} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This class extends the Erfurt_Versioning.
 *
 * @category   OntoWiki
 * @package    Extensions_Historyproxy
 * @author     Sebastian Nuck
 * @copyright  Copyright (c) 2014, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class Extended_Erfurt_Versioning extends Erfurt_Versioning
{
    // this action type is used for import actions (e.g. via the import mechanism)
    const import_action_type = 11;

    /**
     * Returns modified resources in a given range.
     * @param  [type]  $graphUri
     * @param  [type]  $from
     * @param  [type]  $to
     * @param  integer $page
     * @return [type]
     */
    public function getRangeOfModifiedResources( $graphUri, $from, $to, $page = 1 ) {
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

        $result = $this->_sqlQuery(
            $sql,
            $this->getLimit() + 1,
            $page * $this->getLimit() - $this->getLimit()
            );
        return $result;
    }

    /**
     * Returns the imports associated with a given model and/or useruri.
     * @param  [type]  $model (optional)
     * @param  [type]  $useruri (optional)
     * @param  integer $page (optional)
     * @return [type]
     */
    public function getImports( $model = null, $useruri = null, $page = 1 ) {
        $sql = 'SELECT id, model, useruri, resource, tstamp FROM ef_versioning_actions
        WHERE action_type = ' . self::import_action_type . ' ';

        if ($model != null) {
            $sql .= 'AND model = \'' . $model . '\' ';
        }

        if ($useruri != null) {
            $sql .= 'AND useruri = \'' . $useruri . '\' ';
        }
        $sql.= 'ORDER BY id DESC';

        $logger = Erfurt_App::getInstance()->getLog();
        $result = $this->_sqlQuery(
            $sql,
            $this->getLimit() + 1,
            $page * $this->getLimit() - $this->getLimit()
            );
        return $result;
    }

}
