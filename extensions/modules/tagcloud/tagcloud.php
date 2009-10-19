<?php

require_once 'OntoWiki/Module.php';

/**
 * OntoWiki module – tagcloud
 *
 * Shows a list of tags associated with the current resource
 * and sizes them according to their frequency.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_tagcloud
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: tagcloud.php 4092 2009-08-19 22:20:53Z christian.wuerker $
 */
class TagcloudModule extends OntoWiki_Module
{
    public function getTitle()
    {
        return 'Tags';
    }
    
	/**
	 * Returns the content for the model list.
	 */
	public function getContents()
	{
		$nonLinScale = 0.5;
		$min         = 0.75;
		$max         = 1.25;
		
		// dummy data
		$data = array(
			'action' => array(
				'url'      => '#', 
				'count'    => 12, 
				'selected' => ''
			), 
			'semanticweb' => array(
				'url'      => '#',
				'count'    => 80, 
				'selected' => ''
			),
			'ontowiki' => array(
				'url'      => '#',
				'count'    => 56, 
				'selected' => ''
			),  
			'rdf' => array(
				'url'      => '#',
				'count'    => 40, 
				'selected' => 'selected'
			), 
			'tag' => array(
				'url'      => '#',
				'count'    => 12, 
				'selected' => ''
			), 
			'importanttag' => array(
				'url'      => '#',
				'count'    => 72, 
				'selected' => 'selected'
			), 
			'report' => array(
				'url'      => '#',
				'count'    => 19, 
				'selected' => ''
			),
			'anothertag' => array(
				'url'      => '#',
				'count'    => 2, 
				'selected' => ''
			),
			'spaced tag' => array(
				'url'      => '#',
				'count'    => 56, 
				'selected' => ''
			),
			'javascript' => array(
				'url'      => '#',
				'count'    => 14, 
				'selected' => ''
			)
		);
		
		$tags     = array();
		$maxCount = 0;
		
		// get maximum count
		foreach ($data as $tag) {
			if ($tag['count'] > $maxCount) {
				$maxCount = $tag['count'];
			}
		}
		
		foreach ($data as $tagName => $tagData) {			
			// calculate scaling and weight factor
			$scale = pow((int) $tagData['count'] / $maxCount, $nonLinScale) * ($max - $min) + $min;
			
			// construct tag link
			$tags[] = sprintf('<a%s href="%s" title="%d" style="font-size:%d%%;opacity:%f;">%s<span class="onlyAural">(%d)</span></a>', 
				              ($tagData['selected'] == 'selected' ? " class=\"$tagData[selected]\"" : ''), 
				              $tagData['url'], 
				              $tagData['count'], 
				              $scale * 100, 
				              $scale, 
				              $tagName, 
				              $tagData['count']);
		}
		
		$content = $this->render('tagcloud', $tags, 'tags');
		
		return $content;
	}
}


