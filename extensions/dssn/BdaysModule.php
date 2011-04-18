<?php

/**
 * DSSN module – Next Birthdays
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_bdays
 * @author     Atanas Alexandrov <sirakov@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: exploretags.php 4276 2009-10-11 11:38:55Z jonas.brekle@gmail.com $
 */
class BdaysModule extends OntoWiki_Module
{
    public function getTitle()
    {
        return 'Next Birthdays';
    }

    public function init()
    {
    }

    /**
     * Returns the content for the model list.
     */
    function getContents()
    {
        $singleBirthday = array(
            'resource' => 'http://sebastian.tramp.name',
            'name' => 'Sebastian Tramp',
            'depiction' => 'http://www.gravatar.com/avatar/65628ed5c340e69a9ebdea271f21a4fe.png',
            'date' => '2010-09-29',
            'label' => 'in 5 days'
           );
        $birthdays = array();
        $birthdays[] = $singleBirthday;
        $birthdays[] = $singleBirthday;
        $birthdays[] = $singleBirthday;
        $birthdays[] = $singleBirthday;

        $content = $this->render('dssn/bdays', $birthdays, 'birthdays');
        return $content;
    }
}


