<?php
/*	
*	Geotagger for Joomla
*	(c) 2012-2014 Weever Apps Inc. <http://www.weeverapps.com/>
*
*	Authors: 	Robert Gerald Porter <rob@weeverapps.com>
				Matt Grande <matt@weeverapps.com>
				Andrew Holden <andrew@weeverapps.com>
				Aaron Song <aaron@weeverapps.com>
*	Version: 	1.0
*   License: 	GPL v3.0
*
*   This extension is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   This extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details <http://www.gnu.org/licenses/>.
*
*/

class plgContentWeeverMapsInstallerScript
{ 

	public function install( $parent ) 
	{ 

		$db 				= JFactory::getDbo();
		$tableExtensions 	= $db->nameQuote("#__extensions");
		$columnElement   	= $db->nameQuote("element");
		$columnType      	= $db->nameQuote("type");
		$columnEnabled   	= $db->nameQuote("enabled");
		 
		$db->setQuery("UPDATE $tableExtensions SET $columnEnabled=1 WHERE $columnElement='weevermapsk2' AND $columnType='plugin'");
		$db->query();
		  
	} 
  
}
