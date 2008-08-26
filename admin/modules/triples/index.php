<? // $Id$
class TriplesModule extends EditModule
{
	function getName()
	{
		return "Triples";
	}

	function getSlug()
	{
		return "triples";
	}

	function displayForm($model, $personURI, $language)
	{
		$rdf = $model->writeRdfToString();

		/*
		 * URI
		 */
		?><h2>RDF/XML</h3>
		<div id="<?=$this->getSlug()?>_rdfxml">
		<textarea name="<?=$this->getSlug()?>_triples" style="width: 100%" rows="25"><?=htmlspecialchars($rdf)?></textarea>
		</div>
<?
	}

	function saveChanges(&$model, $personURI, $language)
	{
		global $profileDocumentType;

		$rdf = $_REQUEST[$this->getSlug().'_triples'];


		$newmodel = ModelFactory::getDefaultModel();
		$newmodel->addNamespace('foaf', $foaf);
		$newmodel->addNamespace('dc', $dc);
		$newmodel->addNamespace('rdf', $rdf);

		$newmodel->loadFromString($rdf, $profileDocumentType);

		$model->close();
		$model = $newmodel;

		return true;
	}
}

$modules[] = new TriplesModule();
