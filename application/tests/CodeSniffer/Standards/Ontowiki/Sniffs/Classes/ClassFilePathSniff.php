<?php
/**
 * Ontowiki_Sniffs_Classes_ClassFilePathSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Lars Eidam <lars.eidam@googlemail.com>
 * @link      http://code.google.com/p/ontowiki/
 */

/**
 * Ontowiki_Sniffs_Classes_ClassFilePathSniff.
 *
 * Tests that the filepath correspond to the classname for php Files in
 * the application/classes Folder
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Lars Eidam <lars.eidam@googlemail.com>
 * @link      http://code.google.com/p/ontowiki/
 */
class Ontowiki_Sniffs_Classes_ClassFilePathSniff implements PHP_CodeSniffer_Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
                T_CLASS,
               );

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens   = $phpcsFile->getTokens();
        $decName  = $phpcsFile->findNext(T_STRING, $stackPtr);
        $fullPath = $phpcsFile->getFilename();
        $longFileName = basename($fullPath);
        $fileName = substr($longFileName, 0, strrpos($longFileName, '.'));

        // if the file is under the application/classes folder the class has the path in the name
        // application/classes/Ontowiki/Utils/TestClass.php -> Classname=Ontowiki_Utils_TestClass
        if (stristr($fullPath, 'application/classes') !== FALSE) {
            $partedPath = substr($fullPath, strrpos($fullPath, 'classes'), strlen($fullPath));
            $partedPath = substr($partedPath, 0, strrpos($partedPath, '.'));

            $classNameArray = explode("_", $tokens[$decName]['content']);
            $filepathArray = explode("/", $partedPath);
            if (1 == count($filepathArray)) {
                $filepathArray = explode("\\", $partedPath);
            }
            
            $notFound = TRUE;
            foreach ($classNameArray as $index => $classNamePart) {
                if ($classNamePart != $filepathArray[$index + 1]) {
                    $notFound = FALSE;
                    break;
                }
            }
            if (FALSE === $notFound) {
                $error = '%s name doesn\'t match filepath; expected "%s %s"';
                $data  = array(
                          ucfirst($tokens[$stackPtr]['content']),
                          $tokens[$stackPtr]['content'],
                          $tokens[$decName]['content'],
                         );
                $phpcsFile->addError($error, $stackPtr, 'NoMatch', $data);
            }
        }
    //    else
    //    {
    //        if ($tokens[$decName]['content'] !== $fileName)
    //        {
    //            $error = '%s name doesn\'t match filename; expected "%s %s"';
    //            $data  = array(
    //                ucfirst($tokens[$stackPtr]['content']),
    //                $tokens[$stackPtr]['content'],
    //                $fileName,
    //                );
    //            $phpcsFile->addError($error, $stackPtr, 'NoMatch', $data);
    //        }
    //    }
    }//end process()


}//end class

?>
