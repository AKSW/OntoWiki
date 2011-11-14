<?php
/**
 * Ontowiki_Sniffs_PHP_GetRequestDataSniff.
 * 
 * Ensures that no super globals are used.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer_MySource
 * @author    Lars Eidam <lars.eidam@googlemail.com>
 * @link      http://code.google.com/p/ontowiki/
 */

/**
 * Ontowiki_Sniffs_PHP_GetRequestDataSniff.
 * Ensures that getRequestData() is used to access super globals.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Lars Eidam <lars.eidam@googlemail.com>
 * @link      http://code.google.com/p/ontowiki/
 */
class Ontowiki_Sniffs_Functions_ForbiddenFunctionsSniff extends Generic_Sniffs_PHP_ForbiddenFunctionsSniff
{
    /**
     * A list of forbidden functions with their alternatives.
     *
     * @var array(string => string|null)
     */
    protected $forbiddenFunctions = array(
         'print_r'          => null,
         'var_dump'         => null,
         'error_log'        => null
    );

}//end class

?>
