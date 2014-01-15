<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Welcome extends Controller {

	public function action_index()
	{
		$this->request->forward('guide');
		//$this->response->body('hello, world!');
	}
	
	public function action_sample()
	{
		$this->template->set('person', 'akira');
		//$this->response->body(__TEMPLATE__);
	}
} // End Welcome
