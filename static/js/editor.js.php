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

	// NOTE - I'm not sure how you'll be able to get the post in Joomla.
	// Aaron/Rob, you'll have to look at this.
	//global $settings;
	//$zoom = $settings->default_zoom;
$zoom = 16;

	?>

		<script type="text/javascript" src="http://www.google.com/jsapi"></script>
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
		<script type="text/javascript">
		 	var $j = jQuery.noConflict();
			$j(function() {
				$j(document).ready(function() {

					<?php echo $jsFormInsert; /* only has value if form needs to be inserted with JS */ ?>

				    var hasLocation = false;
					var center = new google.maps.LatLng(0.0,0.0);
					var on = 0;
					var latitude = null, longitude = null;
					<?php echo $jsMetaVar; ?>;
					
					//if(meta.public == '0')
					//	$j("#geolocation-public").attr('checked', false);
					//else
					//	$j("#geolocation-public").attr('checked', true);
					
					if(meta.on == '0')
						disableGeo();
					else
						enableGeo();

					console.log( meta.geo );
					
					if( $j.isArray(meta.geo) && !!meta.geo[0] ) {
						longitude = meta.geo[0].longitude;
						latitude = meta.geo[0].latitude;
						center = new google.maps.LatLng(meta.geo[0].latitude, meta.geo[0].longitude);
						hasLocation = true;
						$j("#geolocation-latitude").val(center.lat());
						$j("#geolocation-longitude").val(center.lng());
						$j('#latlng').text( center.lat() + ', ' + center.lng() );
						reverseGeocode(center);
					}
						
				 	var myOptions = {
				      'zoom': <?php echo $zoom; ?>,
				      'center': center,
				      'mapTypeId': google.maps.MapTypeId.ROADMAP
				    };
				    var image = '<?php echo $settings->pin_url; ?>';

				    var markerIcon = new google.maps.MarkerImage(

		                image,
		                new google.maps.Size(32, 37),
		                new google.maps.Point(0,0),
		                new google.maps.Point(16, 37),
		                new google.maps.Size(64, 37)

		           );
				   /* var shadow = new google.maps.MarkerImage('<?php echo $settings->pin_shadow_url; ?>',
						new google.maps.Size(39, 23),
						new google.maps.Point(0, 0),
						new google.maps.Point(12, 25));*/
						
				    var map = new google.maps.Map(document.getElementById('geolocation-map'), myOptions);

					var marker = new google.maps.Marker({

						position: center, 
						map: map, 
						title:'Post Location',
						icon: markerIcon
	
					});
					
					if((!hasLocation) && (google.loader.ClientLocation)) {

				      center = new google.maps.LatLng(google.loader.ClientLocation.latitude, google.loader.ClientLocation.longitude);
				      reverseGeocode(center);

				    }
				    else if(!hasLocation) {

				    	map.setZoom(1);

				    }
					
					google.maps.event.addListener(map, 'click', function(event) {
						placeMarker(event.latLng);
					});
					
					var currentAddress;
					var customAddress = false;
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

					$j("#geolocation-pin").change(function() {
						image = $j("#geolocation-pin").val();
						$j("#geolocation-pin-img").attr('src', image);
						marker.setIcon(image);
					});

					function placeMarker(location) {
						marker.setPosition(location);
						map.setCenter(location);
						if((location.lat() != '') && (location.lng() != '')) {
							$j("#geolocation-latitude").val(location.lat());
							$j("#geolocation-longitude").val(location.lng());
							$j('#latlng').text( center.lat() + ', ' + center.lng() );
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
								    	map.setZoom(16);
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
