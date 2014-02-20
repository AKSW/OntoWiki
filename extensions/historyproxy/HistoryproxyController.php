<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Historyproxy component controller. This controler is only used for demonstration purposes of the historyproxy.
 *
 * @category   OntoWiki
 * @package    Extensions_Historyproxy
 * @author     Sebastian Nuck
 * @copyright  Copyright (c) 2014, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class HistoryproxyController extends OntoWiki_Controller_Component
{
    public function viewAction() {

        $model = $this->_owApp->selectedModel;
        $event              = new Erfurt_Event( 'onQueryHistory' );
        // $model->getModelIri()


        /**
         * Get last imports
         */
        $event->function    = 'getLastImports';
        $event->parameters  = array(  );
        $event->trigger();
        $this->view->importsFunction   = $event->function;
        $this->view->importsParameters = $event->parameters;
        $this->view->importsCallback   = $event->callback;

        /**
         * Get last changes
         */
        $event->function    = 'getChangesFromRange';
        $event->parameters  = array( $model->getModelIri(),
            '01-01-2014' , '30-01-2014');
        $event->trigger();
        $this->view->changeFunction   = $event->function;
        $this->view->changeParameters = $event->parameters;
        $this->view->changeCallback   = $event->callback;
    }
}
