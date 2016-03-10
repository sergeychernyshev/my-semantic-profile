<?php

class GeoLocationModule extends EditModule
{
	function getName()
	{
		return "Location";
	}

	function getSlug()
	{
		return "geo";
	}

	function displayForm($model, $personURI, $language)
	{
		global $foaf, $geo;
		?>
		<div id="<?php echo $this->getSlug()?>">
<?php
		$query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
		PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
		select ?lat, ?lng
		where { <'.$personURI->getURI().'> foaf:based_near ?point .
		?point  geo:lat  ?lat .
		?point  geo:long ?lng
		}';
		#echo "$query\n";
		$locations = $model->sparqlQuery($query);

		if ($locations)
		{
			foreach ($locations as $location)
			{
				$locationstoedit[] = array(
					'lat' => $location['?lat']->getLabel(),
					'lng' => $location['?lng']->getLabel()
				);
			}

			foreach ($locationstoedit as $location)
			{
?>
				<div>
				Lattitude: <input type="text" name="<?php echo $this->getSlug()?>_lat[]" value="<?php echo htmlspecialchars($location['lat'])?>" size="60"/>
				Longitude: <input type="text" name="<?php echo $this->getSlug()?>_lng[]" value="<?php echo htmlspecialchars($location['lng'])?>" size="60"/>
				</div>
<?php
			}
		}
?>
		<div class="new_entries_locations">
		<div>
		Lattitude: <input type="text" name="<?php echo $this->getSlug()?>_lat[]" value="" size="60"/>
		Longitude: <input type="text" name="<?php echo $this->getSlug()?>_lng[]" value="" size="60"/>
		</div>
		</div>

		</div>
<?php
	}

	function saveChanges($model, &$personURI, $language)
	{
		global $foaf, $geo;

		/*
		 * Accounts 
		 */
		$new_locations = array();

		$new_lat = $_REQUEST[$this->getSlug().'_lat'];
		$new_lng = $_REQUEST[$this->getSlug().'_lng'];

		// making sense of submitted data
		foreach($new_lat as $lat)
		{
			$lng = array_shift($new_lng);

			if ($lat == '' || $lng == '')
			{
				continue;
			}

			$new_locations[] = array( 'lat' => $lat, 'lng' => $lng);
		}

		// now deleting all locations and any statements associated with them 
		$it = $model->findAsIterator($personURI, new Resource($foaf.'based_near'), NULL);
		while ($it->hasNext())
		{
			$statement = $it->next();
			$it2 = $model->findAsIterator($statement->getObject(), NULL, NULL);
			while ($it2->hasNext())
			{
				$model->remove($it2->next());
			}

			/*
			 * Remove location
			 */
			$model->remove($statement);
		}

		$basedNearResource = new Resource($foaf.'based_near');
		$latResource = new Resource($geo.'lat');
		$lngResource = new Resource($geo.'long');

		foreach ($new_locations as $new_location)
		{
#			var_export($new_location); die;

			$locationResource = new BlankNode($model);

			$model->add(new Statement($personURI, $basedNearResource, $locationResource));
			$model->add(new Statement($locationResource,
				$latResource,
				new Literal($new_location['lat'])
			));
			$model->add(new Statement($locationResource,
				$lngResource,
				new Literal($new_location['lng'])
			));
		}

		return true;
	}
}

$modules[] = new GeoLocationModule();
