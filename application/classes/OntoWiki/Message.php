<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2016, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki message class.
 *
 * Encapsulates a message that needs to be saved for the user.
 *
 * @category  OntoWiki
 * @package   OntoWiki_Classes
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author    Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Message
{
    /**
     * Message of type succes.
     * The last operation was executed successfully.
     */
    const SUCCESS = 'success';

    /**
     * Message of type info.
     * A notice to help the user interpret the application state.
     */
    const INFO = 'info';

    /**
     * Message of type warning.
     * A notice to inform the user the he might not get the results desired
     * due to a non-critical application error or false input.
     */
    const WARNING = 'warning';

    /**
     * Message of type error.
     * A notice to inform the user of a critical application error that does
     * not allow the application to perform under normal circumstances.
     */
    const ERROR = 'error';

    /**
     * Options array
     *
     * @var array
     */
    protected $_options = null;

    /**
     * The message's type
     *
     * @var string
     */
    protected $_type = null;

    /**
     * The message text
     *
     * @var string
     */
    protected $_text = null;

    /**
     * Constructor
     *
     * @param string $text
     * @param string $type
     */
    public function __construct($text, $type = self::INFO, $options = array())
    {
        $this->_options = array_merge(
            array(
                 'escape'    => true,
                 'translate' => true),
            $options
        );

        $this->_type = $type;
        $this->_text = $text;
    }

    /**
     * Returns the type of the message
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Returns the message's text
     *
     * @return string
     */
    public function getText()
    {
        $text = $this->_translate($this->_text);
        if (strlen($text) > 1000) {
            $text = substr($text, 0, 1000) . '...';
        }
        $text = $this->_options['escape'] ? $this->getView()->escape($text) : $text;

        return $text;
    }

    /**
     * Returns the view object.
     *
     * @return OntoWiki_View
     */
    protected function getView()
    {
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        if (null === $viewRenderer->view) {
            $viewRenderer->initView();
        }
        $view = clone $viewRenderer->view;
        $view->clearVars();
        return $view;
    }

    /**
     * Returns the translator.
     *
     * @return Zend_Translate
     */
    protected function getTranslator()
    {
        return OntoWiki::getInstance()->translate;
    }

    private function _translate($text)
    {
        if (($this->_options['translate'] === true) && ($translator = $this->getTranslator()) !== null) {
            return $translator->translate($text);
        }
        return $text;
    }

}


