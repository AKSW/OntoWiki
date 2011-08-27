<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki ExecuteHelperMarkup view helper
 *
 * takes a string and replaces occurrences of helper markup with its results
 * helper markup is defined as:
 * {{helpername p1="v1" p2="v2 ...}} and {{helpername v1}}
 *
 * @category OntoWiki
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class Site_View_Helper_ExecuteHelperMarkup extends Zend_View_Helper_Abstract
{
    /*
     * current view, injected with setView from Zend
     */
    public $view;

    /*
     * the outer helper markup pattern 
     * note: http://stackoverflow.com/questions/1435254/ - now way to identify 
     * key/value pairs at once, so this is done by a second pattern
     */
    public $helperPattern = '/{{(?\'helper\'[a-zA-Z]+)(?\'attributes\'( [a-zA-Z]+\=\"[^"]+\")*)}}/';

    /*
     * identifies key value pairs in the attribute part
     */
    public $keyValuePattern = '/((?\'key\'[a-zA-Z]+)\=\"(?\'value\'[^"]+)\")/';

    /*
     * view setter (dev zone article: http://devzone.zend.com/article/3412)
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    private function executeOnMatches($matches)
    {
        $helper     = $matches['helper'];
        $attributes = trim($matches['attributes']);
        $log        = "Found a $helper pattern: $matches[0]";

        // split the attributes part of the helper markup and fill it to the 
        // options array
        preg_match_all ($this->keyValuePattern, $attributes, $matches, PREG_SET_ORDER);
        $options = array();
        foreach ($matches as $i => $match) {
            $key           = $match['key'];
            $value         = $match['value'];
            $options[$key] = $value;
        }

        // return the output of the helper or its error message
        try {
            return $this->view->$helper($options);
        } catch (Exception $e) {
            $message = htmlspecialchars($e->getMessage(), ENT_NOQUOTES);
            $message = str_replace ('"', '', $message);
            $message = str_replace ('\'', '', $message);
            return $this->returnError($message);
        }
    }

    private function returnError($message = null)
    {
        return "<span title='$message'>{{helper error}}</span>";
    }

    public function executeHelperMarkup($text = null) 
    {
        $this->text = (string) $text;

        // execute full helper tags
        $callback = array( &$this, 'executeOnMatches');
        $this->text = preg_replace_callback ($this->helperPattern, $callback , $this->text);

        return $this->text;
    }
}
