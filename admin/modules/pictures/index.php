<?
class PicturesModule extends EditModule
{
	function getName()
	{
		return "Pictures";
	}

	function getSlug()
	{
		return "pictures";
	}
}

$modules[] = new PicturesModule();
