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


defined('_JEXEC') or die;

	// NOTE - I'm not sure how you'll be able to get the post in Joomla.
	// Aaron/Rob, you'll have to look at this.
	//global $settings;
	//$zoom = $settings->default_zoom;
$zoom = 16;

	?>

		<script type="text/javascript" src="http://www.google.com/jsapi"></script>
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
		<script type="text/javascript">

		 	var $j 				= jQuery.noConflict(),
		 		$geotagger 		= {

		 			map: 				null,
		 			center: 			new google.maps.LatLng(0.0,0.0),
		 			kml: 				null,
		 			markerUrl: 			'<?php echo $this->marker_url; ?>',
		 			defaultMarkerUrl: 	'<?php echo $this->default_marker_url; ?>'

		 		};

			$j(function() {

				$j(document).ready(function() {

					<?php echo $jsFormInsert; /* only has value if form needs to be inserted with JS */ ?>

				    var hasLocation = false;
					var on = 0;
					var latitude = null, longitude = null;
					<?php echo $jsMetaVar; ?>;

					console.log( meta );

					$geotagger.addKmlLayer = function( url ) {

						if( $geotagger.kml instanceof google.maps.KmlLayer )
							$geotagger.kml.setMap(null);

						$geotagger.kml = new google.maps.KmlLayer({ url: url });
							
						$geotagger.kml.setMap( $geotagger.map );

					}
					
					//if(meta.public == '0')
					//	$j("#geolocation-public").attr('checked', false);
					//else
					//	$j("#geolocation-public").attr('checked', true);
					
					if(meta.on == 0)
						disableGeo();
					else
						enableGeo();
					
					if( $j.isArray(meta.geo) && !!meta.geo[0] ) {

						longitude 			= meta.geo[0].longitude;
						latitude 			= meta.geo[0].latitude;
						$geotagger.center 	= new google.maps.LatLng( meta.geo[0].latitude, meta.geo[0].longitude );
						hasLocation 		= true;

						$j("#geolocation-latitude").val($geotagger.center.lat());
						$j("#geolocation-longitude").val($geotagger.center.lng());
						$j('#latlng').text( $geotagger.center.lat() + ', ' + $geotagger.center.lng() );
						reverseGeocode($geotagger.center);

					}
						
				 	var myOptions 	= {

				      'zoom': 			<?php echo $zoom; ?>,
				      'center': 		$geotagger.center,
				      'mapTypeId': 		google.maps.MapTypeId.ROADMAP

				    };

				    var markerIcon = new google.maps.MarkerImage(

		                $geotagger.markerUrl,
		                new google.maps.Size(32, 37),
		                new google.maps.Point(0,0),
		                new google.maps.Point(16, 37),
		                new google.maps.Size(64, 37)

		           );

				   /* var shadow = new google.maps.MarkerImage('<?php echo $settings->pin_shadow_url; ?>',
						new google.maps.Size(39, 23),
						new google.maps.Point(0, 0),
						new google.maps.Point(12, 25));*/
						
				    $geotagger.map 	= new google.maps.Map(document.getElementById('geolocation-map'), myOptions);

					var marker 		= new google.maps.Marker({

						position: 		$geotagger.center, 
						map: 			$geotagger.map, 
						title: 			'Post Location',
						icon: 			markerIcon
	
					});

					if( !!meta.geo && !!meta.geo[0] && meta.geo[0].kml ) {

						$geotagger.addKmlLayer( meta.geo[0].kml );

					}

					/*if( (!hasLocation) && (google.loader.ClientLocation) ) {

				      $geotagger.center 	= new google.maps.LatLng(google.loader.ClientLocation.latitude, google.loader.ClientLocation.longitude);

				      reverseGeocode($geotagger.center);

				    } else */if(!hasLocation) {

				    	$geotagger.map.setZoom(2);

				    }
					
					google.maps.event.addListener( $geotagger.map, 'click', function(event) {
						
						placeMarker( event.latLng );

					});
					
					var currentAddress;
					var customAddress = false;

					$j('#geolocation-url').bind('keyup paste', function(e) {

						// prevent double jeopardy
						clearTimeout( $j(this).data('timeout') );

						$j(this).data('timeout', setTimeout( function() {
										
							$geotagger.addKmlLayer( $j('#geolocation-url').val() );

						}, 200));
					
					});


					$j("#geolocation-address").click(function(){
						currentAddress = $j(this).val();
						if(currentAddress != '')
							$j("#geolocation-address").val('');
					});
					
					$j("#geolocation-load").click(function(){
						if($j("#geolocation-address").val() != '') {
							customAddress = true;
							currentAddress = $j("#geolocation-address").val();
							geocode(currentAddress);
						}
					});
					
					$j("#geolocation-address").keyup(function(e) {
						if(e.keyCode == 13)
							$j("#geolocation-load").click();
					});
					
					$j("#geolocation-enabled").click(function(){
						enableGeo();
					});
					
					$j("#geolocation-disabled").click(function(){
						disableGeo();
					});

					$j("#geolocation-pin").change( function() {

						image = $j("#geolocation-pin").val() || $geotagger.defaultMarkerUrl;

						marker.setIcon(

							new google.maps.MarkerImage(

								image,
								new google.maps.Size(32, 37),
								new google.maps.Point(0,0),
								new google.maps.Point(16, 37),
								new google.maps.Size(64, 37)

							)

				        );

					});

					function placeMarker(location) {
						marker.setPosition(location);
						$geotagger.map.setCenter(location);
						if((location.lat() != '') && (location.lng() != '')) {
							$j("#geolocation-latitude").val(location.lat());
							$j("#geolocation-longitude").val(location.lng());
							$j('#latlng').text( $geotagger.center.lat() + ', ' + $geotagger.center.lng() );
						}
						
						if(!customAddress)
							reverseGeocode(location);
					}
					
					function geocode(address) {
						var geocoder = new google.maps.Geocoder();
					    if (geocoder) {
							geocoder.geocode({"address": address}, function(results, status) {
								if (status == google.maps.GeocoderStatus.OK) {
									placeMarker(results[0].geometry.location);
									if(!hasLocation) {
								    	$geotagger.map.setZoom(16);
								    	hasLocation = true;
									}
								}
							});
						}
						$j("#geodata").html(latitude + ', ' + longitude);
					}
					
					function reverseGeocode(location) {
						var geocoder = new google.maps.Geocoder();
					    if (geocoder) {
							geocoder.geocode({"latLng": location}, function(results, status) {
							if (status == google.maps.GeocoderStatus.OK) {
							  if(results[1]) {
							  	var address = results[1].formatted_address;
							  	if(address == "")
							  		address = results[7].formatted_address;
							  	else {
									$j("#geolocation-address").val(address);
									placeMarker(location);
							  	}
							  }
							}
							});
						}
					}
					
					function enableGeo() {
						$j("#geolocation-address").removeAttr('disabled');
						$j("#geolocation-load").removeAttr('disabled');
						$j("#geolocation-map").css('filter', '');
						$j("#geolocation-map").css('opacity', '');
						$j("#geolocation-map").css('-moz-opacity', '');
						//$j("#geolocation-public").removeAttr('disabled');
						$j("#geolocation-map").removeAttr('readonly');
						$j("#geolocation-disabled").removeAttr('checked');
						$j("#geolocation-enabled").attr('checked', 'checked');
						
						//if(meta.public == '1')
						//	$j("#geolocation-public").attr('checked', 'checked');
					}
					
					function disableGeo() {
						$j("#geolocation-address").attr('disabled', 'disabled');
						$j("#geolocation-load").attr('disabled', 'disabled');
						$j("#geolocation-map").css('filter', 'alpha(opacity=50)');
						$j("#geolocation-map").css('opacity', '0.5');
						$j("#geolocation-map").css('-moz-opacity', '0.5');
						$j("#geolocation-map").attr('readonly', 'readonly');
						//$j("#geolocation-public").attr('disabled', 'disabled');
						
						$j("#geolocation-enabled").removeAttr('checked');
						$j("#geolocation-disabled").attr('checked', 'checked');
						
						//if(meta.public == '1')
						//	$j("#geolocation-public").attr('checked', 'checked');
					}
				});
			});
		</script>

		<style>

			.inside input[type=text] {

				width: 75%;

			}

		</style>
