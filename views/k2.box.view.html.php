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


if( $this->joomlaVersion[0] < 3 ) :

?>

<div class="panel" id="wx-geotagger-k2-panel" style="display:none;">

<h3 class="pane-toggler title"><a href="javascript:void(0);"><span>Geotagger mobile</span></a></h3>

<div id="wx-geotagger-k2-panel-pane" class="pane-slider content" style="padding-top: 0px; border-top: medium none; padding-bottom: 0px; border-bottom: medium none; overflow: hidden; height: auto;">

<fieldset class="panelform">

<?php 
require_once ( JPATH_PLUGINS.DS.'content'.DS.'weevermaps'.DS.'static'.DS.'views'.DS.'form.view.html.php' ); 
?>

</fieldset>

</div>

</div>

<?php

 else : 

?>

<div id="geotagger" class="tab-pane"><div id="geotagger-inner-hide" style="display:none;">

<?php 
require_once ( JPATH_PLUGINS.DS.'content'.DS.'weevermaps'.DS.'static'.DS.'views'.DS.'form.view.html.php' ); 
?>

</div></div>

<?php

endif;
