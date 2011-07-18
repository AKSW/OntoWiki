<?php
require_once 'lib/IteratorReader.php';

/**
 *  @category		component
 *  @package        csvimport
 *  @author			Michael Martin martin@informatik.uni-leipzig.de
 */
class CsvParser
{
    //a constant
    const error_character = '\\uFFFD';

	/**
	 *	readed csvFile represented ar associated array
	 *	@var array
	 *  @access private
     *  @author			Michael Martin martin@informatik.uni-leipzig.de
	 */
    private $csvMap;


	/**
	 * This is the constructor.It try to open the csv file.The method throws an exception
	 * on failure.
	 *
	 * @access public
	 * @param str $fileName The csv file.
     * @author			Michael Martin martin@informatik.uni-leipzig.de
	 *
	 * @throws Exception
	 */
    public function __construct($fileName = "", $separator = ',', $useHeaders = false ) {

        //preventing some limitations
        ini_set("max_execution_time","600");
        ini_set("memory_limit","1536M");
        ini_set("auto_detect_line_endings",TRUE);

        //initialising some class attributes
        $this->csvMap = array();

        //parse Map and check status
        $this->csvMap = $this->readCSV($fileName, $separator, $useHeaders);
		if( empty ($this->csvMap) )
			throw new Exception( 'The file "'.$fileName.'" cannot be readed or is empty.' );
    }



	/**
	 * Getter of the CSV Map
	 *
	 * @access public
	 * @return array $csvMap.
	 */
    public function getParsedFile () {
        return $this->csvMap;
    }





#########################################################
# Private Functions
#########################################################

	/**
	 * It try to open the csv file.The method throws an exception
	 *
	 * @access private
	 * @param str $fileName The csv file.
	 */
    private function readCSV($fileName, $separator = ",", $useHeaders = false) {

        $csvReader = new File_CSV_IteratorReader($fileName, $separator) ;
        return $csvReader->toArray($useHeaders);
    }

#########################################################
# TODO: Maybe these following function could be used in further workflows
#########################################################

    // Replaces all byte sequences that need escaping. Characters that can
    // remain unencoded in N-Triples are not touched by the regex. The
    // replaced sequences are:
    //
    // 0x00-0x1F   non-printable characters
    // 0x22        double quote (")
    // 0x5C        backslash (\)
    // 0x7F        non-printable character (Control)
    // 0x80-0xBF   unexpected continuation byte,
    // 0xC0-0xFF   first byte of multi-byte character,
    //             followed by one or more continuation byte (0x80-0xBF)
    //
    // The regex accepts multi-byte sequences that don't have the correct
    // number of continuation bytes (0x80-0xBF). This is handled by the
    // callback.
    private function escape( $str ) {
        return preg_replace_callback(
            "/[\\x00-\\x1F\\x22\\x5C\\x7F]|[\\x80-\\xBF]|[\\xC0-\\xFF][\\x80-\\xBF]*/",
            array('Transformer','escape_callback'),
            $str);
    }

    private static function escape_callback($matches) {
        $encoded_character = $matches[0];
        $byte = ord($encoded_character[0]);
        // Single-byte characters (0xxxxxxx, hex 00-7E)
        if ($byte == 0x09) return "\\t";
        if ($byte == 0x0A) return "\\n";
        if ($byte == 0x0D) return "\\r";
        if ($byte == 0x22) return "\\\"";
        if ($byte == 0x5C) return "\\\\";
        if ($byte < 0x20 || $byte == 0x7F) {
            // encode as \u00XX
            return "\\u00" . sprintf("%02X", $byte);
        }

        // Multi-byte characters
        if ($byte < 0xC0) {
            // Continuation bytes (0x80-0xBF) are not allowed to appear as first byte
            return Transformer::error_character;
        }
        if ($byte < 0xE0) { // 110xxxxx, hex C0-DF
            $bytes = 2;
            $codepoint = $byte & 0x1F;
        } else if ($byte < 0xF0) {
            // 1110xxxx, hex E0-EF
            $bytes = 3;
            $codepoint = $byte & 0x0F;
        } else if ($byte < 0xF8) {
            // 11110xxx, hex F0-F7
            $bytes = 4;
            $codepoint = $byte & 0x07;
        } else if ($byte < 0xFC) {
            // 111110xx, hex F8-FB
            $bytes = 5;
            $codepoint = $byte & 0x03;
        } else if ($byte < 0xFE) {
            // 1111110x, hex FC-FD
            $bytes = 6;
            $codepoint = $byte & 0x01;
        } else {
            // 11111110 and 11111111, hex FE-FF, are not allowed
            return Transformer::error_character;
        }

        // Verify correct number of continuation bytes (0x80 to 0xBF)
        $length = strlen($encoded_character);
        if ($length < $bytes) {
            // not enough continuation bytes
            return Transformer::error_character;
        }

        if ($length > $bytes) {
            // Too many continuation bytes -- show each as one error
            $rest = str_repeat(Transformer::error_character, $length - $bytes);
        } else {
            $rest = '';
        }

        // Calculate Unicode codepoints from the bytes
        for ($i = 1; $i < $bytes; $i++) {
            // Loop over the additional bytes (0x80-0xBF, 10xxxxxx)
            // Add their lowest six bits to the end of the codepoint
            $byte = ord($encoded_character[$i]);
            $codepoint = ($codepoint << 6) | ($byte & 0x3F);
        }

        // Check for overlong encoding (character is encoded as more bytes than
        // necessary, this must be rejected by a safe UTF-8 decoder)
        if (($bytes == 2 && $codepoint <= 0x7F) ||
            ($bytes == 3 && $codepoint <= 0x7FF) ||
            ($bytes == 4 && $codepoint <= 0xFFFF) ||
            ($bytes == 5 && $codepoint <= 0x1FFFFF) ||
            ($bytes == 6 && $codepoint <= 0x3FFFFF)) {
            return Transformer::error_character . $rest;
        }

        // Check for UTF-16 surrogates, which must not be used in UTF-8
        if ($codepoint >= 0xD800 && $codepoint <= 0xDFFF) {
            return Transformer::error_character . $rest;
        }

        // Misc. illegal code positions
        if ($codepoint == 0xFFFE || $codepoint == 0xFFFF) {
            return Transformer::error_character . $rest;
        }

        if ($codepoint <= 0xFFFF) {
            // 0x0100-0xFFFF, encode as \uXXXX
            return "\\u" . sprintf("%04X", $codepoint) . $rest;
        }

        if ($codepoint <= 0x10FFFF) {
            // 0x10000-0x10FFFF, encode as \UXXXXXXXX
            return "\\U" . sprintf("%08X", $codepoint) . $rest;
        }
        // Unicode codepoint above 0x10FFFF, no characters have been assigned
        // to those codepoints
        return Transformer::error_character . $rest;
    }
}
?>
