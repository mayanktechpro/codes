<?php
App::uses('AppController', 'Controller');
/**
 * Students Controller
 *
 */
class CodesController extends AppController {
	function codestan() {
		$this->render(false);
		$this->layout = false;
		$output = 'fd';
		exec('git log -p --pretty=format:"%H %cn %ce %cd %s" --after="2014-01-30"', $output);
		var_dump($output);
	}

}