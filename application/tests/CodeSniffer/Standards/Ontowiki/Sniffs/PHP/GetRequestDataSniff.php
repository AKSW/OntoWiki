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
class Ontowiki_Sniffs_PHP_GetRequestDataSniff implements PHP_CodeSniffer_Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_VARIABLE);

    }//end register()


    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in
     *                                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $varName = $tokens[$stackPtr]['content'];
        if ($varName !== '$_REQUEST'
            && $varName !== '$_GET'
            && $varName !== '$_POST'
            && $varName !== '$_FILES'
        ) {
            return;
        }

        $type  = 'SuperglobalAccessed';
        $error = 'The %s super global must not be accessed directly;' .
                 'use Zend_Controller_Front::getInstance()->getRequest() instead';
        $data  = array($varName);

        $phpcsFile->addError($error, $stackPtr, $type, $data);

    }//end process()


}//end class

?>
