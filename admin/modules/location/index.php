<?

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
}

$modules[] = new GeoLocationModule();
