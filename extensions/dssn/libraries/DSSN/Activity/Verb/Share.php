<?php
/**
 * An activity verb share
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_dssn
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class DSSN_Activity_Verb_Share extends DSSN_Activity_Verb
{
    public function __construct() {
        $this->setIri(DSSN_AAIR_NS . 'Share');
    }
    
    /**
     * Get label.
     * a label in past form (user LABEL the following)
     */
    function getLabel()
    {
        return 'shared';
    }
}

