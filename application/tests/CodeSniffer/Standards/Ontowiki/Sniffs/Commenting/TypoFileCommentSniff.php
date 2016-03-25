<?php
/**
 * Parses and verifies the TYPO3 copyright notice.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   TYPO3SniffPool
 * @author    Stefano Kowalke <blueduck@mailbox.org>
 * @copyright 2015 Stefano Kowalke
 * @license   http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @link      https://github.com/typo3-ci/TYPO3SniffPool
 */

/**
 * Parses and verifies the TYPO3 copyright notice.
 *
 * @category  PHP
 * @package   TYPO3SniffPool
 * @author    Stefano Kowalke <blueduck@mailbox.org>
 * @copyright 2015 Stefano Kowalke
 * @license   http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @link      https://github.com/typo3-ci/TYPO3SniffPool
 */

class Ontowiki_Sniffs_Commenting_TypoFileCommentSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * The file comment in TYPO3 CMS must be the copyright notice.
     *
     * @var array
     */
    protected $copyright = array(
                            1  => "/**\n",
                            2  => " * This file is part of the {@link http://ontowiki.net OntoWiki} project.\n",
                            3  => " *\n",
                            4  => " * @copyright Copyright (c) 2016, {@link http://aksw.org AKSW}\n",
                            5  => " * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)\n",
                            6  => " */",
                           );


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_OPEN_TAG);

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return int
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Find the next non whitespace token.
        $commentStart = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);

        // Allow namespace statements at the top of the file.
        if ($tokens[$commentStart]['code'] === T_NAMESPACE) {
            $semicolon    = $phpcsFile->findNext(T_SEMICOLON, ($commentStart + 1));
            $commentStart = $phpcsFile->findNext(T_WHITESPACE, ($semicolon + 1), null, true);
        }

        if ($tokens[$commentStart]['code'] !== T_DOC_COMMENT_OPEN_TAG) {
            $fix = $phpcsFile->addFixableError(
                'Copyright notice must start with /**; but /* was found!',
                $commentStart,
                'WrongStyle'
            );

            if ($fix === true) {
                $phpcsFile->fixer->replaceToken($commentStart, "/**");
            }

            return;
        }

        $commentEnd = ($phpcsFile->findNext(T_DOC_COMMENT_CLOSE_TAG, ($commentStart + 1)) - 1);
        print($commentStart . ' ' . $commentEnd);
        if ($tokens[$commentStart]['code'] !== T_DOC_COMMENT_OPEN_TAG) {
            $phpcsFile->addError('Copyright notice missing', $commentStart, 'NoCopyrightFound');

            return;
        }

        if ((($commentEnd - $commentStart) + 1) < count($this->copyright)) {
            $phpcsFile->addError(
                'Copyright notice too short',
                $commentStart,
                'CommentTooShort'
            );
            return;
        } else if ((($commentEnd - $commentStart) + 1) > count($this->copyright)) {
            $phpcsFile->addError(
                'Copyright notice too long',
                $commentStart,
                'CommentTooLong'
            );
            return;
        }

        $j = 1;
        for ($i = $commentStart; $i <= $commentEnd; $i++) {
            if ($tokens[$i]['content'] !== $this->copyright[$j]) {
                $error = 'Found wrong part of copyright notice. Expected "%s", but found "%s"';
                $data  = array(
                          trim($this->copyright[$j]),
                          trim($tokens[$i]['content']),
                         );
                $fix   = $phpcsFile->addFixableError($error, $i, 'WrongText', $data);

                if ($fix === true) {
                    $phpcsFile->fixer->replaceToken($i, $this->copyright[$j]);
                }
            }

            $j++;
        }

        return;

    }//end process()


}//end class
