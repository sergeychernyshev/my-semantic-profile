<?
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
		<?
			$first=true;
			foreach ($locations as $location)
			{
				$markers[] = $location['?lat']->getLabel().','.$location['?lng']->getLabel();
				?>
				<span rel="foaf:based_near"><span typeof="geo:Point"<? if ($first) {?> class="geo"<? } ?>>
				<span property="geo:lat"<? if ($first) {?> class="latitude"<? } ?> style="display:none"><?=$location['?lat']->getLabel()?></span>
				<span property="geo:long"<? if ($first) {?> class="longitude"<? } ?> style="display:none"><?=$location['?lng']->getLabel()?></span>
				</span></span><?
				$first = false;
			}

		?>
		<div id="map" style="width: 600px; height: 400px"><img src="http://maps.google.com/staticmap?<?
			if (count($locations) < 2)
			{
				echo 'zoom=12&amp;';
			}
		?>size=600x400&amp;markers=<?=implode('|', $markers); ?>%7C&amp;maptype=roadmap&amp;key=<?=$googleMapsKey?>" alt="locations map" width="600" height="400"/></div>
		</div>
		<?
		}

		return true;
	}
}

$display_modules[] = new LocationDisplayModule();
