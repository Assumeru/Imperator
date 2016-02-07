<?php
namespace imperator\url;
use imperator\Imperator;

class PageURL extends URL {
	public function __construct($page = null, $id = null, $name = null) {
		parent::__construct(Imperator::getSettings()->getBaseURL().'/');
		if($page) {
			if($id !== null) {
				$page .= '/'.$id.'/'.$name;
			}
			if(Imperator::getSettings()->useSEOURLs()) {
				$this->addDirect($page);
			} else {
				$this->addArgument('page', $page);
			}
		}
	}
}