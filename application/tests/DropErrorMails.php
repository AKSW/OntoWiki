<?php

/**
 *  Sends Error mails to developer, if automated Tests fail 
 *
 * @author     Julian Jöris <julianjoeris@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

?>
<?php
define("SEND_ERROR_TO","ontowiki-dev@googlegroups.com");
//define("SEND_ERROR_TO","ontowiki-dev@joese.de");

$files = explode("\n",`ls /var/www/tests.ontowiki.net`);
array_pop($files);

foreach ($files as $file) {
	$failure_code = `grep \"status_failed /var/www/tests.ontowiki.net/$file | wc -l`; 
	if($failure_code > 0) {
		mail(	SEND_ERROR_TO,
			"Error in TestSuite ".$file,
			"An error occured in Testsuite ".$file."!\n See http://tests.ontowiki.net/$file for details.",
			"From: ontowikiautotest@googlemail.com");
	}

}

?>
