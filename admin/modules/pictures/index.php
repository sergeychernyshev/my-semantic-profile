<?
class PicturesModule extends EditModule
{
	function getName()
	{
		return "Pictures";
	}

	function getSlug()
	{
		return "pics";
	}
}

$modules[] = new PicturesModule();
