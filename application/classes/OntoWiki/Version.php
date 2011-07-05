<?php
/**
 * OntoWiki
 *
 * LICENSE
 *
 * This file is part of the OntoWiki project.
 * Copyright (C) 2006-2010, AKSW
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the 
 * Free Software Foundation; either version 2 of the License, or 
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
 * Public License for more details.
 *
 * A copy of the GNU General Public License is bundled with this package in
 * the file LICENSE.txt. It is also available through the world-wide-web at 
 * this URL: http://opensource.org/licenses/gpl-2.0.php
 *
 * @category   OntoWiki
 * @package    Version
 * @copyright  Copyright (c) 2006-2010, {@link http://aksw.org AKSW}
 * @license    {@link http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)}
 */
 
/**
 * This class identifies the OntoWiki version.
 * 
 * It can be used to determine the version of OntoWiki as well as comparing 
 * OntoWiki versions. 
 * 
 * @category   OntoWiki
 * @package    Version
 * @copyright  Copyright (c) 2006-2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php   GNU General Public License, version 2 (GPLv2)
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 */
class OntoWiki_Version 
{
    /**
     * This constant contains the version of this OntoWiki distribution.
     * 
     * The first number in the version string identifies a major release. The 
     * second number is incremented for each feature release. A feature release
     * includes at least one new feature. The third number identifies bug fix
     * releases, which contain no new features. Each of the numbers has to be a
     * positive integer and all of them are allowed to be greater than 9.
     * 
     * For development versions and pre-releases an additional suffix must be
     * added to the version string. The suffix must be one of:
     *
     * dev   - if the specified version is currently developed,
     * alpha - if the specified version is in an early testing stage and
     * beta  - if the specified version is in an advanced testing stage.
     * 
     * Please refer to [1] for further information how version strings are 
     * related to each other.
     * 
     * [1] {@link http://de.php.net/manual/en/function.version-compare.php}
     * 
     * @var string
     */
    const VERSION = '1.0.0dev';
    
    /**
     * This static function compares a version string specified by the $version
     * parameter with the current OntoWiki_Version::VERSION of OntoWiki.
     * 
     * @param  string $version A version string to compare with the current 
     *                         version.
     * @return int             Returns -1 if $version is older, 0 if $version
     *                         is equal to this version and +1 if $version is
     *                         newer than this version.
     */
    public static function compareVersion($version)
    {
        return version_compare($version, strtolower(self::VERSION));
    }
}