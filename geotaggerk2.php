<?php
/*	
*	Geotagger for K2
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

defined('_JEXEC') or die;

# Joomla 3.0 nonsense
if( !defined('DS') )
	define( 'DS', DIRECTORY_SEPARATOR );

jimport('joomla.plugin.plugin');

JLoader::register( 'K2Plugin', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'lib'.DS.'k2plugin.php' );

require_once ( JPATH_PLUGINS.DS.'k2'.DS.'geotaggerk2'.DS.'static'.DS.'classes'.DS.'common'.'.php' );

class plgK2GeotaggerK2 extends K2Plugin {

	public 		$pluginName 				= "geotaggerk2";
	public 		$pluginNameHumanReadable;
	public  	$pluginVersion 				= "1.0";
	public		$pluginLongVersion 			= "Version 1.0 \"Leif Ericson\"";
	public  	$pluginReleaseDate 			= "February 6, 2014";
	public  	$joomlaVersion;
	public 		$marker_url;
	public 		$default_marker_url;
	
	private		$geoData 					= null;
	private		$inputString				= array(
													'longitude' => 0,
													'latitude' 	=> 0,
													'address'	=> null,
													'label'		=> null,
													'marker'	=> null,
													'kml'		=> null
												);
	private		$_com						= "com_k2";

	public function __construct( &$subject, $config ) {
		
		$app 			= JFactory::getApplication();
		$option 		= JRequest::getCmd('option');
		$document 		= JFactory::getDocument();
		$root_url 		= substr( JURI::root(), 0, strlen(JURI::root())-1 );
		$post_id 		= null;

		$version 				= new JVersion;
		$this->joomlaVersion 	= substr($version->getShortVersion(), 0, 3);
		
		// kill this when not in correct context
		if( !$app->isAdmin() || $option != "com_k2" || JRequest::getVar("view") != "item" )
			return false;

		$settings 	= $this->build_settings();
		
		JPlugin::loadLanguage('plg_k2_'.$this->pluginName, JPATH_ADMINISTRATOR);

		$this->pluginNameHumanReadable = JText::_('WEEVERMAPSK2_PLG_NAME');
		
		if( $id = JRequest::getVar("cid") ) {

			$this->getGeoData( $id );

			$post_id = $id;
			//$this->implodeGeoData();

		}

		$this->default_marker_url = $root_url . "/plugins/k2/geotaggerk2/assets/images/default-marker.png";

		if( isset($this->geoData[0]) && $this->geoData[0]->marker ) {

			$this->marker_url = $this->geoData[0]->marker;

		} else $this->marker_url = $this->default_marker_url;

		if( isset($this->geoData[0]) && $this->geoData[0]->kml ) {

			$this->kml_url = $this->geoData[0]->kml;

		}

		$jsMetaVar		= "var meta = " . $this->getJsMetaVar();

		$jsFormInsert	= "

			/* some dancing around for jQuery UI in K2 */
			var tabLength = \$j('#k2Tabs ul.simpleTabsNavigation li').length;

			\$j('<li id=\"tabGeotagger\" class=\"ui-state-default ui-corner-top\"><a href=\"#k2Tab' + tabLength + '\" id=\"k2TabGeotaggerA\" style=\"background-image: url(". $root_url . "/plugins/k2/geotaggerk2/assets/images/marker.png);background-position:4px 3px;background-repeat: no-repeat;background-size:15px;\">Geotagger</a></li>').appendTo('#k2Tabs ul.simpleTabsNavigation');

			\$j('<div id=\"k2Tab'+ tabLength +'\" class=\"simpleTabsContent ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide\"></div>').appendTo('#k2Tabs' ); 

			\$j('#k2TabGeotagger').appendTo('#k2Tab'+ tabLength)

			\$j('#geotagger-inner-hide').show();

			\$j('a[href*=\"#k2Tab\"]').on('click', function(event) {

				if( \$j(this).attr(\"href\") == \"#k2Tab\" + tabLength ) {

					\$j(\"div.simpleTabsContent\").addClass('ui-tabs-hide');
					\$j(\"div#k2Tabs ul li\").removeClass('ui-state-active').removeClass('ui-tabs-selected');

					\$j(\"#k2Tab\" + tabLength ).removeClass('ui-tabs-hide');
					\$j('#tabGeotagger').addClass('ui-state-active').addClass('ui-tabs-selected');
					
				} else {

					\$j(\"#k2Tab\" + tabLength).addClass('ui-tabs-hide');
					\$j('#tabGeotagger').removeClass('ui-state-active').removeClass('ui-tabs-selected');

				} 

				google.maps.event.trigger( \$geotagger.map,'resize' );
				\$geotagger.map.setCenter( \$geotagger.center );

				event.preventDefault();

			});

		";

		$document->addStyleSheet( $root_url . '/plugins/k2/geotaggerk2/static/assets/css/style.css', 'text/css', null, array() );

		if( $this->joomlaVersion[0] < 3 ) {

			$document->addStyleSheet( $root_url . '/plugins/k2/geotaggerk2/assets/css/k2.style.css', 'text/css', null, array() );

		} else {

			$document->addStyleSheet( $root_url . '/plugins/k2/geotaggerk2/assets/css/k2.bootstrap.style.css', 'text/css', null, array() );

		}

		require_once ( JPATH_PLUGINS.DS.'k2'.DS.'geotaggerk2'.DS.'views'.DS.'k2.box.view.html.php' );
		require_once ( JPATH_PLUGINS.DS.'k2'.DS.'geotaggerk2'.DS.'static'.DS.'js'.DS.'editor.js.php' );
		

		parent::__construct( $subject, $config );
		
	}
	
	
	private function implodeGeoData() {
	
		foreach( (array) $this->geoData as $k=>$v )
		{
		
			$point = array();
			$_ds = ";";
			
			$this->convertToLatLong($v);
			
			$this->inputString['longitude'] 	.= $v->longitude 	. $_ds;
			$this->inputString['latitude'] 		.= $v->latitude 	. $_ds;
			$this->inputString['address'] 		.= $v->address 		. $_ds;
			$this->inputString['label'] 		.= $v->label 		. $_ds;
			$this->inputString['marker'] 		.= $v->marker 		. $_ds;
			$this->inputString['kml'] 			.= $v->kml 			. $_ds;
		
		}
	
	}
	
	
	private function convertToLatLong( &$obj ) {
	
		$point = rtrim( ltrim( $obj->location, "(POINT" ), ")" );
		$point = explode(" ", $point);
		$obj->latitude = $point[0];
		$obj->longitude = $point[1];
	
	}
	
	
	private function getGeoData( $id ) {
	
		$db = JFactory::getDBO();
		
		$query = "SELECT component_id, AsText(location) AS location, address, label, kml, marker ".
				"FROM
					#__weever_maps ".
				"WHERE
					component = ".$db->quote($this->_com)." 
					AND
					component_id = ".$db->quote($id);
					
		$db->setQuery($query);
		$this->geoData = $db->loadObjectList();

		if( !$this->geoData )
			return;

		foreach( (array) $this->geoData as $k=>$v ) {

			$this->convertToLatLong( $v );

		}
	
	}

	public function onAfterK2Save( &$item, $isNew ) {
		
		$_ds = ";";		
		
		$geoLatArray = 		explode( 	$_ds, rtrim( JRequest::getVar("geolocation-latitude"), 		$_ds) 	);
		$geoLongArray = 	explode( 	$_ds, rtrim( JRequest::getVar("geolocation-longitude"), 	$_ds) 	);
		$geoAddressArray = 	explode( 	$_ds, rtrim( JRequest::getVar("geolocation-address"), 		$_ds) 	);
		$geoLabelArray = 	explode( 	$_ds, rtrim( JRequest::getVar("geolocation-label"), 		$_ds) 	);
		$geoMarkerArray = 	explode( 	$_ds, rtrim( JRequest::getVar("geolocation-pin"), 		$_ds) 	);
		
		$db = JFactory::getDBO();
		
		$query = " 	DELETE FROM #__weever_maps 
					WHERE
						component_id = ".$db->Quote($item->id)."
						AND
						component = ".$db->Quote('com_k2');
						
	
		$db->setQuery($query);
		$db->query();

		if( JRequest::getVar("geolocation-on") == 0 )
			return;

		if( $kml = rtrim( JRequest::getVar("geolocation-url"), $_ds) )	{
			
			$query = " 	INSERT  ".
					"	INTO	#__weever_maps ".
					"	(component_id, component, kml) ".
					"	VALUES ('".$item->id."', 'com_k2', ".$db->quote($kml).")";
			
			$db->setQuery($query);
			$db->query();

		}

		if( ( $geoLatArray[0] == 0 && $geoLongArray[0] == 0 ) )
			return; 
		
		foreach( (array) $geoLatArray as $k=>$v )
		{
		
			$query = " 	INSERT  ".
					"	INTO	#__weever_maps ".
					"	(component_id, component, location, address, label, marker) ".
					"	VALUES (".$item->id.", ".$db->Quote('com_k2').", 
							GeomFromText(' POINT(".$geoLatArray[$k]." ".$geoLongArray[$k].") '),
							".$db->Quote($geoAddressArray[$k]).", 
							".$db->Quote($geoLabelArray[$k]).", 
							".$db->Quote($geoMarkerArray[$k]).")";
		
			$db->setQuery($query);
			$db->query();
		
		}
		
	
	}

	private function build_settings() {

		$settings 	= new GeolocationSettings();
		$settings->map_width       = 450;
		$settings->map_height      = 200;
		$settings->default_zoom    = 16;
		//$settings->map_position    = get_option('geolocation_map_position');
		// Do we want to display the pin?
		$settings->show_custom_pin = 0;
		$settings->pin_url         = "http://weeverapp.com/media/sprites/default-marker.png";
		$settings->pin_shadow_url  = "";

		return $settings;
		//$settings->pin_shadow_url  = plugins_url('img/wp_pin_shadow.png', __FILE__ );
		//$settings->zoom_url        = esc_js(plugins_url('img/zoom/', __FILE__));

		/*if ( $this->settings === null ) {
			if(get_option('geolocation_map_width') == '0')
				update_option('geolocation_map_width', '450');
				
			if(get_option('geolocation_map_height') == '0')
				update_option('geolocation_map_height', '200');
				
			if(get_option('geolocation_default_zoom') == '0')
				update_option('geolocation_default_zoom', '16');
				
			if(get_option('geolocation_map_position') == '0')
				update_option('geolocation_map_position', 'after');

			$settings = new GeolocationSettings();
			$settings->map_width       = (int) get_option('geolocation_map_width');
			$settings->map_height      = (int) get_option('geolocation_map_height');
			$settings->default_zoom    = (int) get_option('geolocation_default_zoom');
			$settings->map_position    = get_option('geolocation_map_position');
			// Do we want to display the pin?
			$settings->show_custom_pin = get_option('geolocation_wp_pin');
			$settings->pin_url         = esc_js(esc_url(plugins_url('img/wp_pin.png', __FILE__ )));
			$settings->pin_shadow_url  = plugins_url('img/wp_pin_shadow.png', __FILE__ );
			$settings->zoom_url        = esc_js(plugins_url('img/zoom/', __FILE__));
		}

		return $settings;*/
	}

	private function getJsMetaVar() {

		$meta = new stdClass;

		$meta->geo 		= $this->geoData;
		$meta->on 		= 1;
		$meta->public 	= 1;

		return json_encode($meta);

	}

	
} 



