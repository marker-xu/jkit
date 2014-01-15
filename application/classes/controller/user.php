<?php defined('SYSPATH') or die('No direct script access.');

class Controller_User extends Controller {
  public function action_index(){
    $strUserId = $this->request->param('uid');
    var_dump($strUserId);
    echo $this->response->body('hello world');
  }
}
