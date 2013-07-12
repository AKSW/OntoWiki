<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Helper for rendering HTML forms
 *
 * Replacement for the corresponding Zend view helper.
 *
 * @category  OntoWiki
 * @package   OntoWiki_Classes_View_Helper
 */
class OntoWiki_View_Helper_Form extends Zend_View_Helper_HtmlElement // Zend_View_Helper_Form
{
    public function form($info) // , $attribs = null, $content = false)
    {
        // do not disregard "name" attribute as Zend_View_Helper_Form::form does
        $info = array_merge(array('content' => false), $info);
        extract($info);

        $xhtml = '<form'
               . $this->_htmlAttribs($attribs)
               . '>';

        if (strtoupper($attribs['method']) === 'POST') {
            $event = new Erfurt_Event('onDisplayPostForm');
            $event->setDefault(array());
            $xhtml .= implode($event->trigger());
        }

        if (false !== $content) {
            $xhtml .= $content
                   .  '</form>';
        }

        return $xhtml;
    }
}
