<?php
/**
 *	Reads CSV Files using the File_CSV_Iterator.
 *
 *	Copyright (c) 2007-2010 Christian Würker (ceus-media.de)
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *	@category		cmClasses
 *	@package		file.csv
 *	@uses			File_CSV_Iterator
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			10.12.2007
 *	@version		$Id: IteratorReader.php5 607 2010-03-24 23:39:16Z christian.wuerker $
 */
require_once('Iterator.php');
/**
 *	Reads CSV Files using the File_CSV_Iterator.
 *	@category		cmClasses
 *	@package		file.csv
 *	@uses			File_CSV_Iterator
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			10.12.2007
 *	@version		$Id: IteratorReader.php5 607 2010-03-24 23:39:16Z christian.wuerker $
 */
class File_CSV_IteratorReader
{
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		string		$fileName		File Name of CSV File
	 *	@param		string		$delimiter		Delimiter between Information
	 *	@return		void
	 */
	public function __construct( $fileName, $delimiter = NULL )
	{
		$this->iterator	= new File_CSV_Iterator( $fileName, $delimiter );	
	}
	
	/**
	 *	Returns CSV Data as Array or associative Array.
	 *	@access		public
	 *	@param		bool		$useHeaders		Flag: use first Line as Headers and return associative Array
	 *	@return		array
	 */
	public function toArray( $useHeaders = false )
	{
		$list	= array();
		if( $useHeaders )
		{
			$headers	= $this->iterator->next();
			while( $data = $this->iterator->next() )
			{
				$list[]	= array_combine( $headers, $data );
			}
		}
		else
		{
			while( $list[] = $this->iterator->next() );
		}
		return $list;
	}
}
?>
