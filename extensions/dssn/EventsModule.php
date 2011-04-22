<?php
/**
 * DSSN module – Next Birthdays
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_events
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class EventsModule extends OntoWiki_Module
{
    /*
     * array of next birthdays or null if not fetched yet
     */
    protected $birthdays = null;

    public function getTitle()
    {
        return 'Upcoming ...';
    }

    public function init()
    {
        /* nothing to do right now */
    }

    /*
     * This fetches and sets the next
     * TODO: real data please, and with object-cache :-)
     */
    public function setBirthdays() {

        $singleBirthday = array(
            // resource will used to link to the friends page
            'resource' => 'http://sebastian.tramp.name',
            // titleHelper please
            'name' => 'Sebastian Tramp',
            // do we need a pic gatewy to enforce small images?
            'depiction' => 'http://www.gravatar.com/avatar/65628ed5c340e69a9ebdea271f21a4fe.png',
            // YYYY-MM-DD
            'date' => '2010-09-29',
            // something similar as OntoWiki_Utils::dateDifference but for days in the future
            'label' => 'in 5 days'
        );

        $this->birthdays = array();
        $this->birthdays[] = $singleBirthday;
        $this->birthdays[] = $singleBirthday;
        $this->birthdays[] = $singleBirthday;
        $this->birthdays[] = $singleBirthday;

        //$this->birthdays = array();
    }

    /*
     * returns the array of next birthdays
     */
    public function getBirthdays() {
        if ($this->birthdays == null) {
            $this->setBirthdays();
        }
        return $this->birthdays;
    }

    public function shouldShow() {
        // module can be turned off in extension config
        if ($this->_privateConfig->modules->events != true) {
            return false;
        }
        // module is not shown if there are no birthdays
        if (count($this->getBirthdays()) > 0) {
            return true;
        } else {
            return false;
        }
    }

    function getContents()
    {
        $content = array (
            'Birthdays' => $this->render('modules/events-bdays', $this->getBirthdays(), 'birthdays'),
            'Events' => $this->render('modules/events-other', false, 'others')
        );
        return $content;
    }
}


