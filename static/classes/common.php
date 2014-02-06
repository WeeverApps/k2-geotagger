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

//todo: make friendly with WP
defined('_JEXEC') or die;

if ( !class_exists('geotaggerHelper') ) {

	class geotaggerHelper {

		public static function geolocation_save_postdata( $post_id ) {

			if ( function_exists( CMS . '_before_geolocation_save_postdata' ) ) {
				$is_okay = call_user_func( CMS . '_before_geolocation_save_postdata' );
				if ( !$is_okay ) {
					return $post_id;
				}
			}

			$latitude = self::clean_coordinate($_POST['geolocation-latitude']);
			$longitude = self::clean_coordinate($_POST['geolocation-longitude']);
			$address = self::reverse_geocode($latitude, $longitude);
			$pin_url = $_POST['geolocation-pin'];
			$kml_url = $_POST['geolocation-url'];
			$public = $_POST['geolocation-public'];
			$on = $_POST['geolocation-on'];

			if((self::clean_coordinate($latitude) != '') && (self::clean_coordinate($longitude)) != '') {
				if ( function_exists( CMS . '_geolocation_save_postdata' ) ) {
					call_user_func( CMS . '_geolocation_save_postdata', $post_id, $latitude, $longitude, $address, $pin_url, $kml_url, $public, $on );
				}
			}

			return $post_id;

		}


		public static function reverse_geocode( $latitude, $longitude ) {

			$url = "http://maps.google.com/maps/api/geocode/json?latlng=".$latitude.",".$longitude."&sensor=false";
			$result = remote_get($url);
			$json = json_decode($result['body']);
			foreach ($json->results as $result)
			{
				foreach($result->address_components as $addressPart) {
					if((in_array('locality', $addressPart->types)) && (in_array('political', $addressPart->types)))
			    		$city = $addressPart->long_name;
			    	else if((in_array('administrative_area_level_1', $addressPart->types)) && (in_array('political', $addressPart->types)))
			    		$state = $addressPart->long_name;
			    	else if((in_array('country', $addressPart->types)) && (in_array('political', $addressPart->types)))
			    		$country = $addressPart->long_name;
				}
			}
			
			if(($city != '') && ($state != '') && ($country != ''))
				$address = $city.', '.$state.', '.$country;
			else if(($city != '') && ($state != ''))
				$address = $city.', '.$state;
			else if(($state != '') && ($country != ''))
				$address = $state.', '.$country;
			else if($country != '')
				$address = $country;
				
			return $address;

		}

		public static function clean_coordinate( $coordinate ) {

			$pattern = '/^(\-)?(\d{1,3})\.(\d{1,15})/';
			preg_match($pattern, $coordinate, $matches);
			return $matches[0];

		}

	}

	class GeolocationSettings {

		public $map_width;
		public $map_height;
		public $default_zoom;
		public $map_position;
		public $show_custom_pin;
		public $pin_url;
		public $pin_shadow_url;
		public $zoom_url;

	}

}