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

class plgK2GeotaggerK2InstallerScript { 

	public function install( $parent ) 
	{ 

		$db 				= JFactory::getDbo();

		$query = $db->getQuery(true)
		 	->update($db->qn('#__extensions'))
		 	->set($db->qn('enabled') . ' = ' . $db->q(1))
		 	->where($db->qn('type') . ' = ' . $db->q('plugin'))
		 	->where($db->qn('folder') . ' = ' . $db->q('k2'))
		 	->where($db->qn('element') . ' = ' . $db->q('geotaggerk2'));
		 $db->setQuery($query);
		 $db->execute();


		$query = $db->getQuery(true)
		 	->select('*')
		 	->from($db->qn('#__extensions'))
		 	->where($db->qn('type') . ' = ' . $db->q('plugin'))
		 	->where($db->qn('enabled') . ' = ' . $db->q('1'))
		 	->where($db->qn('folder') . ' = ' . $db->q('k2'))
		 	->where($db->qn('element') . ' = ' . $db->q('weevermapsk2'));
		$db->setQuery($query);
		$enabled_plugins = $db->loadObjectList();

		// disabling old plugin
		if( isset($enabled_plugins[0]) ) {

			 $query = $db->getQuery(true)
			 	->update($db->qn('#__extensions'))
			 	->set($db->qn('enabled') . ' = ' . $db->q(0))
			 	->where($db->qn('type') . ' = ' . $db->q('plugin'))
			 	->where($db->qn('folder') . ' = ' . $db->q('k2'))
			 	->where($db->qn('element') . ' = ' . $db->q('weevermapsk2'));
			$db->setQuery($query);
			$db->execute();

		}
		  
	} 
  
}
