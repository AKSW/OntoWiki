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
        return 'Upcoming Events';
    }

    public function init()
    {
        /* nothing to do right now */
    }

    /*
     * This fetches and sets the next n birthdays of people $me knows
     * TODO: real data please, and with object-cache :-)
     */
    public function setBirthdays() {

        $helper = $this->_owApp->extensionManager->getComponentHelper('dssn');

        $me = $helper->getMe();
        $this->birthdays = array();
        
        foreach ($me->getFriends(DSSN_Foaf_Person::BASIC) as $uri => $friend){
            $birthdayTime = strtotime(str_replace("-", "/", $friend['birthday']));
            $now = time();
            if($birthdayTime < $now){
                $birthdayTime += 60*60*24*365; //TODO use +1year
            }
            if(($birthdayTime - $now) < 60*60*24*7){
                $this->birthdays[] = array(
                    // resource will used to link to the friends page
                    'resource' => $uri,
                    // titleHelper please
                    'name' => $friend['name'],
                    // do we need a pic gateway to enforce small images?
                    'depiction' => $friend['depiction'],
                    // MM-DD
                    'date' => $friend['birthday'],
                    // formats the difference nicly
                    'label' => OntoWiki_Utils::dateDifference($now, $birthdayTime).' ('.date('l',$birthdayTime).')'
                );
            }
        }
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
            'Parties' => $this->render('modules/events-other', false, 'parties'),
            'Other' => $this->render('modules/events-other', false, 'others')
        );
        return $content;
    }
}


