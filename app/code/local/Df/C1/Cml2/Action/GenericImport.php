<?php
namespace Df\C1\Cml2\Action;
use Df\C1\Cml2\State\Import as I;
abstract class GenericImport extends \Df\C1\Cml2\Action {
	/** @return \Df\C1\Cml2\Import\Data\Document */
	protected function getDocumentCurrent() {return I::s()->getDocumentCurrent();}

	/** @return \Df\C1\Cml2\File */
	protected function getFileCurrent() {return I::s()->getFileCurrent();}
}