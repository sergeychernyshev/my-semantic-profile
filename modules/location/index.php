<?php
class LocationDisplayModule extends DisplayModule
{
	function getName()
	{
		return "Location";
	}

	function getSlug()
	{
		return "location";
	}

	function displayContent($model, $personURI, $lang)
	{
		global $googleMapsKey, $foaf, $dc, $rdfs;

		$query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
		PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
		select ?lat, ?lng
		where { <'.$personURI->getURI().'> foaf:based_near ?point .
		?point  geo:lat  ?lat
		?point  geo:long ?lng
		}';
		#echo "$query\n";
		$locations = $model->sparqlQuery($query);

		if ($locations)
		{
		?><h2>Location</h2>
		<div id="location">
		<?php
			$first=true;
			foreach ($locations as $location)
			{
				$markers[] = $location['?lat']->getLabel().','.$location['?lng']->getLabel();
				?>
				<span rel="foaf:based_near"><span typeof="geo:Point"<?php if ($first) {?> class="geo"<?php } ?>>
				<span property="geo:lat"<?php if ($first) {?> class="latitude"<?php } ?> style="display:none"><?php echo $location['?lat']->getLabel()?></span>
				<span property="geo:long"<?php if ($first) {?> class="longitude"<?php } ?> style="display:none"><?php echo $location['?lng']->getLabel()?></span>
				</span></span><?php
				$first = false;
			}

		?>
		<div id="map" style="width: 600px; height: 400px"><img src="https://maps.googleapis.com/maps/api/staticmap?<?php
			if (count($locations) < 2)
			{
				echo 'zoom=12&amp;';
			}
		?>size=600x400&amp;markers=<?php echo implode('|', $markers); ?>%7C&amp;maptype=roadmap&amp;key=<?php echo $googleMapsKey?>" alt="locations map" width="600" height="400"/></div>
		</div>
		<?php
		}

		return true;
	}
}

$display_modules[] = new LocationDisplayModule();
