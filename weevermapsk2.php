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

require_once ( JPATH_PLUGINS.DS.'content'.DS.'weevermapsk2'.DS.'static'.DS.'classes'.DS.'common'.'.php' );

class plgContentWeeverMapsK2 extends K2Plugin {

	public 		$pluginName 				= "weevermapsk2";
	public 		$pluginNameHumanReadable;
	public  	$pluginVersion 				= "1.0";
	public		$pluginLongVersion 			= "Version 1.0 \"Leif Ericson\"";
	public  	$pluginReleaseDate 			= "February 6, 2014";
	public  	$joomlaVersion;
	
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
		if( !$app->isAdmin() || $option != "com_k2" )
			return false;

		$settings 	= $this->build_settings();
		
		JPlugin::loadLanguage('plg_content_'.$this->pluginName, JPATH_ADMINISTRATOR);
		
		$this->pluginNameHumanReadable = JText::_('WEEVERMAPSK2_PLG_NAME');
		
		if( $id = JRequest::getVar("id") ) {

			$this->getGeoData( $id );

			$post_id = $id;
			//$this->implodeGeoData();

		}

		$jsMetaVar		= "var meta = " . $this->getJsMetaVar();
		
		// if Joomla less than v3
		if( $this->joomlaVersion[0] < 3 ) {

			$document->addScript( "//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" );

			$jsFormInsert	= "

				\$j('#wx-geotagger-k2-panel').appendTo('#content-sliders-".$post_id."' ); 

				\$j('#wx-geotagger-k2-panel').show();

			"; 

		} else {

			$jsFormInsert	= "

				\$j('#geotagger').appendTo('#myTabContent' ); 

				\$j('#geotagger-inner-hide').show();

				\$j('<li><a data-toggle=\"tab\" href=\"#geotagger\">Geotagger</a></li>').appendTo('#myTabTabs');

			";

		}

		$document->addStyleSheet( $root_url . '/plugins/content/weevermapsk2/static/assets/css/style.css', 'text/css', null, array() );
		$document->addStyleSheet( $root_url . '/plugins/content/weevermapsk2/assets/css/k2.style.css', 'text/css', null, array() );

		require_once ( JPATH_PLUGINS.DS.'content'.DS.'weevermapsk2'.DS.'views'.DS.'k2.box.view.html.php' );
		require_once ( JPATH_PLUGINS.DS.'content'.DS.'weevermapsk2'.DS.'static'.DS.'js'.DS.'editor.js.php' );
		

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
		
		foreach( (array) $geoLatArray as $k=>$v )
		{
		
			$query = " 	INSERT  ".
					"	INTO	#__weever_maps ".
					"	(component_id, component, location, address, label, marker) ".
					"	VALUES ('".$item->id."', ".$db->Quote('com_k2').", 
							GeomFromText(' POINT(".$geoLatArray[$k]." ".$geoLongArray[$k].") '),
							".$db->Quote($geoAddressArray[$k]).", 
							".$db->Quote($geoLabelArray[$k]).", 
							".$db->Quote($geoMarkerArray[$k]).")";
						
		
			$db->setQuery($query);
			$db->query();
		
		}
		
		if($kml = rtrim( JRequest::getVar("geolocation-url"), $_ds) )	{
			
			$query = " 	INSERT  ".
					"	INTO	#__weever_maps ".
					"	(component_id, component, kml) ".
					"	VALUES ('".$item->id."', 'com_k2', ".$db->quote($kml).")";
			
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



/* Stupid trick to make this work in J3.0 and J2.5 */
/*
if (version_compare ( JVERSION, '3.0', '<' )) {
  class plgContentWeeverMapsK2 extends plgContentWeeverMapsK2Intermed {
   public function onContentAfterSave($context, &$article, $isNew) {
   $this->onContentAfterSaveIntermed ( $context, $article, $isNew );
  }
}
} else {
  class plgContentWeeverMapsK2 extends plgContentWeeverMapsK2Intermed {
   public function onContentAfterSave($context, $article, $isNew) {
   $this->onContentAfterSaveIntermed ( $context, $article, $isNew );
  }
}
}
*/
