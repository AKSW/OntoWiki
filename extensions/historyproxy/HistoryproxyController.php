<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Historyproxy component controller.
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
        $event->function    = 'getChangesAtDate';
        $event->parameters  = array( $model->getModelIri(),
            "29-01-2014",
            'http://xmlns.com/foaf/0.1/Person' );
        $event->trigger();

        $this->view->function   = $event->function;
        $this->view->parameters = $event->parameters;
        $this->view->callback   = $event->callback;
    }
}
