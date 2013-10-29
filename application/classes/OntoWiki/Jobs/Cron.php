<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki Cron Job
 *
 * @category  OntoWiki
 * @package   OntoWiki_Classes_Jobs
 * @author    Sebastian Tramp <mail@sebastian.tramp.name>
 */
class OntoWiki_Jobs_Cron extends Erfurt_Worker_Job_Abstract
{
    /**
     * gives an DateInterval in seconds (for better comparison)
     * taken from http://www.php.net/manual/en/dateinterval.format.php#102271
     *
     * @param DateInterval $interval object to recalculate
     *
     * @return integer
     */
    private function _toSeconds(DateInterval $interval)
    {
        return
            ($interval->y * 365 * 24 * 60 * 60) +
            ($interval->m * 30 * 24 * 60 * 60) +
            ($interval->d * 24 * 60 * 60) +
            ($interval->h * 60 * 60) +
            ($interval->i * 60) +
            $interval->s;
    }


    /**
     * run the job
     *
     * @param mixed $load payload object
     *
     * @return null
     */
    public function run($load)
    {
        $nowString = date("Y-m-d H:i:s");
        $now         = new DateTime($nowString);
        //$this->logSuccess('OntoWiki_Jobs_Cron started');

        if (empty($load)) {
            // first start is without payload, so we create a fresh one
            $newLoad = array(
                'lastMinutly' => $nowString,
                'lastHourly' => $nowString,
                'lastDaily' => $nowString,
            );
        } else {
            $lastMinutly     = new DateTime($load->lastMinutly);
            $lastMinutlyDiff = $this->_toSeconds($now->diff($lastMinutly));
            if ($lastMinutlyDiff >= 60) {
                $this->logSuccess('OntoWiki_Jobs_Cron -> onEveryMinute');
                $load->lastMinutly = $nowString;
                /**
                 * @trigger onEveryMinute
                 */
                $event = new Erfurt_Event('onEveryMinute');
                $event->trigger();
            };

            $lastHourly     = new DateTime($load->lastHourly);
            $lastHourlyDiff = $this->_toSeconds($now->diff($lastHourly));
            if ($lastHourlyDiff >= 60 * 60) {
                $this->logSuccess('OntoWiki_Jobs_Cron -> onEveryHour');
                $load->lastHourly = $nowString;
                /**
                 * @trigger onEveryHour
                 */
                $event = new Erfurt_Event('onEveryHour');
                $event->trigger();
            };

            $lastDaily     = new DateTime($load->lastDaily);
            $lastDailyDiff = $this->_toSeconds($now->diff($lastDaily));
            if ($lastDailyDiff >= 60 * 60 * 24) {
                $this->logSuccess('OntoWiki_Jobs_Cron -> onEveryDay');
                $load->lastDaily = $nowString;
                /**
                 * @trigger onEveryDay
                 */
                $event = new Erfurt_Event('onEveryDay');
                $event->trigger();
            };
        }

        // send events

        // wait, and start the next cron instance
        sleep(10);
        OntoWiki::getInstance()->callJob('cron', $load);

    }
}
