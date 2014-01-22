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
 * This is a poormans implementation of a cron job. It is based on a chain of
 * recursivly restarting jobs which trigger onEveryMinute, onEveryHour and
 * onEveryDay events. The starting time of a job chain is saved to avoid more
 * than on job chain.
 *
 *
 * @category  OntoWiki
 * @package   OntoWiki_Classes_Jobs
 * @author    Sebastian Tramp <mail@sebastian.tramp.name>
 */
class OntoWiki_Jobs_Cron extends Erfurt_Worker_Job_Abstract
{

    /**
     * on which integer the delay countdown starts
     */
    const DELAY_COUNTDOWN    = 10;

    /**
     * the maximum time in seconds where a cached chain startTime is not taken
     * as too old and a new chain is fired
     */
    const DELAY_MERCYSECONDS = 10;


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
     * sleeps an amount of time and calls the next job
     *
     * @param mixed $load       the load of the next job
     * @param int   $inXSeconds time in seconds when the next job is called
     *
     * @return void
     */
    private function _next($load = null, $inXSeconds = 2)
    {
        if ((int)$inXSeconds > 0) {
            sleep((int)$inXSeconds);
        }

        if ($load == null) {
            OntoWiki::getInstance()->callJob('cron');
        } else {
            OntoWiki::getInstance()->callJob('cron', $load);
        }
    }

    /**
     * initializes a new load
     *
     * @return void
     */
    private function _getNewLoad()
    {
        $this->setValue($this->timeStart, 'timeStart');
        $this->setValue($this->timeStart, 'timeLast');
        $this->load = array(
            'lastMinutly' => $this->nowString,
            'lastHourly' => $this->nowString,
            'lastDaily' => $this->nowString,
            'timeStart' => $this->timeStart,
        );
    }

    /**
     * trigger events, based on nowstring and load
     *
     * @return void
     */
    private function _triggerEvents()
    {
        $now             = new DateTime($this->nowString);

        $lastMinutly     = new DateTime($this->load->lastMinutly);
        $lastMinutlyDiff = $this->_toSeconds($now->diff($lastMinutly));
        if ($lastMinutlyDiff >= 60) {
            $this->load->lastMinutly = $this->nowString;
            /**
             * @trigger onEveryMinute
             */
            $event = new Erfurt_Event('onEveryMinute');
            $event->trigger();
            if ($event->handled) {
                $this->logSuccess('triggered onEveryMinute (handled)');
            } else {
                $this->logSuccess('triggered onEveryMinute (but not handled)');
            }
        };

        $lastHourly     = new DateTime($this->load->lastHourly);
        $lastHourlyDiff = $this->_toSeconds($now->diff($lastHourly));
        if ($lastHourlyDiff >= 60 * 60) {
            $this->load->lastHourly = $this->nowString;
            /**
             * @trigger onEveryHour
             */
            $event = new Erfurt_Event('onEveryHour');
            $event->trigger();
            if ($event->handled) {
                $this->logSuccess('triggered onEveryHour (handled)');
            } else {
                $this->logSuccess('triggered onEveryHour (but not handled)');
            }
        };

        $lastDaily     = new DateTime($this->load->lastDaily);
        $lastDailyDiff = $this->_toSeconds($now->diff($lastDaily));
        if ($lastDailyDiff >= 60 * 60 * 24) {
            $this->load->lastDaily = $this->nowString;
            /**
             * @trigger onEveryDay
             */
            $event = new Erfurt_Event('onEveryDay');
            $event->trigger();
            if ($event->handled) {
                $this->logSuccess('triggered onEveryDay (handled)');
            } else {
                $this->logSuccess('triggered onEveryDay (but not handled)');
            }
        };
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
        // the micro-timestamp to identify the start of the cron chain
        $this->timeStart = microtime(true);
        // the timestring to calculate the events
        $this->nowString = date("Y-m-d H:i:s");
        // the timestamp when a continous chain of cron jobs was started
        $timeStartValue = $this->getValue('timeStart');
        // the timestamp when the last link in the chain was started
        $timeLastValue  = $this->getValue('timeLast');

        if (empty($load)) {
            // situation 1: no previous timestamps cached -> init
            if ($timeStartValue === false || $timeLastValue === false) {
                // first start without payload and cached values,
                // so we create a fresh chain
                $this->_getNewLoad();
                $this->_next($this->load);
            } else {
                // situation 2: previous timestamps cached -> handle
                $timeLastDiff = $this->timeStart - $timeLastValue;
                if ($timeLastDiff > self::DELAY_MERCYSECONDS) {
                    // first start without payload but WITH invalid old cache,
                    // we can create a fresh chain
                    $this->logSuccess(
                        'started without payload,'.
                        ' and OLD cached timestamps exists -- '. $timeLastDiff .
                        ' (init on ' . (string)$this->timeStart . ')'
                    );
                    $this->_getNewLoad();
                    $this->_next($this->load);
                } else {
                    // first start without payload but WITH opposing cache,
                    // we need to look forward
                    $this->logFailure(
                        'started without payload, but cached timestamps exists ' .
                        ' (do nothing for now and try again '.self::DELAY_COUNTDOWN.' times).'
                    );
                    $this->_next(array('delayed' => self::DELAY_COUNTDOWN), 1);
                }
            }
        } else { // load exists
            if (!empty($load->delayed) && (int)$load->delayed > 0) {
                $this->logFailure(
                    'started delayed: ' . $load->delayed
                );
                $timeLastDiff = $this->timeStart - $timeLastValue;
                if ($timeLastDiff > self::DELAY_MERCYSECONDS) {
                    // this is a fresh restart since we know, that the chain can
                    // be created now
                    $this->_next(null, 0);
                } else {
                    // this is a delayed job which is agains started to re-run the tests
                    $this->_next(array('delayed' => $load->delayed - 1), 1);
                }
            } else if (empty($load->timeStart) || (string)$load->timeStart !== (string)$timeStartValue) {
                // this is a start with load but it does not belong to the
                // cron chain of the cached timeStart
                $this->logFailure(
                    'started with payload, but cached timestamp differs' .
                    ' (do nothing, chain dies).'
                );
            } else {
                // finally, this is a "normal" job which can trigger events and
                // which setup a new timeLast
                $this->load = $load;
                $this->_triggerEvents();
                $this->setValue($this->timeStart, 'timeLast');
                $this->_next($this->load);
            }
        }
    }
}
