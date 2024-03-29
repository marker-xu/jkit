# JKit 框架简介

JKit 是基于 [Kohana] 3.2 版本基础上开发的框架级插件，她在Kohana原有基础上做了易用性改进，增加了一些功能。

## JKit 有什么特点

1. 继承 Kohana [文件层级系统](../kohana/files) ，利用自动加载的技术，基于路径优先级来管理和加载php文件，无需配置，简单可依赖。

         $path = JKit::find_file('views', 'foo/bar'); //找到返回$path，找不到返回false

1. 更安全方便的请求参数 `$this->request->param()`

 [!!] 先搜索Route规则中的参数， 然后 $_POST、最后再搜索$_GET

         $foo = $this->request->param('foo', 'bar');
         $this->request->body($foo);

 [!!] 不带参数返回整个数组

         $validation = new Validation(this->request->param(), $rules);

 [!!] 如果设置了 `JKit::$security['xss']` 那么 `$this->request->param()` 会被做 XSS 过滤，因此尽量用这个，不要用原生的 $_GET 和 $_POST

1. 改进 Kohana 的 MVC 模型，利用约定来智能管理模板渲染机制，在 Action Controller 中少写甚至几乎不写代码就能实现复杂的功能。

         //in class Controll_My  
         protected function action_directOnput(){
             //直接输出字符串
             $this->response->body('Hello world!');
         }

         protected function action_static(){
             //渲染静态模板，路径为标准路径 /views/my/static.php
             $this->response->body(__Template__);
         }

         protected function action_vars(){
             //为模板设置变量
             $this->template->set('foo', 'bar');
         }

         protected function action_userDefine(){
             //用户自定义路径下的模板
             $this->template = View::factory('foo/bar');
             $this->template->set($myData);
         }

         protected function action_debug(){
             //调试模板
             $this->response->debug('test', array('foo' => 'bar'));
         }

1. 表单校验规则和 [QWrap](http://www.qwrap.com) 前端校验的规则一致，因此一份配置，前后端可统一使用。

         public function action_valid(){
                $rules = array(                
                    '@username' => array(
                        'reqmsg' => ' 不能为空！',
                        'datatype' => 'n-7.2',
                    ),
                    '@password' => array(
                        'datatype' => 'n-7',
                        'reqmsg' => ' 不能为空！',
                    ),
                    '@confirm' => array(
                        'datatype' => 'reconfirm',
                        'reconfirmfor' => 'password',
                    ),
                    '@testrd' => array(
                        'reqmsg' => '不能为空！',
                        'datatype' => 'n',
                    ),
                    '@testrd2' => array(
                        'datatype' => 'idnumber',
                    ),
                    '@testrd3' => array(
                        'datatype' => 'daterange',
                    ),
                    '@testrd4' => array(
                        'datatype' => 'magic',
                        'magic-pattern' => 'idnumber||email',
                    ),
                );
                $validation = new Validation(this->request->param(), $rules);

                //自定义复杂规则
                function complex($str){
                    return Valid::idnumber($str) || Valid::email($str);
                }
                $validation->rule('testrd4','complex');

                $this->valid($validation) and
                    $this->ok();
         }

1. 改进了 Kohana_View 系统，支持 Smarty 模板、字符串模板。
        
 [!!]使用Controller系统默认的模板：

         $this->create_template('foo/bar');
         $this->set('foo','bar');
         //下面两句可省略
         //$this->response->body($this->template);
         //$this->request->send_response();

 [!!]使用字符串模板：

         $this->template = View::factory('string:hello <%$person%>!');
         $this->template->set('person', 'Akira');
         //下面两句可省略
         //$this->response->body($this->template);
         //$this->request->send_response();

 [!!]直接使用Smarty对象

         $smarty = Template::factory();
         $smarty->assign('foo', 'bar');
         $this->response->body($smarty->fetch('foo/bar'));
         $this->request->send_response();

 [!!]混合使用View和Smarty对象

         $this->template->set('foo', 'bar');
         $smarty = $this->template->get_template_object();
         $smarty->assign('foo2', 'bar2');

1. 扩展了 Kohana_Request 和 Kohana_Response 提供了丰富的 Json、 Forward、Debug、xss_clean 等实用功能。

 [!!]输出JSONP数据

         $this->response->jsonp(array('err' => 'ok'))
         $this->request->send_response();

 [!!]Forward到某个新的AC

         $this->request->forward('foo/bar', array('new'=>'param'));

 [!!]开发环境下(`JKit::$environment == JKit::DEVELOPMENT`)在URL中加上参数 rdtest=1 能自动打开Debug页面，也可以手动调用：
        
         $this->response->debug('some debug info', array('my' => 'param'));
         $this->request->send_response(); 
         //立即发送出去，避免如果设置了template再被auto_render覆盖了debug信息

 [!!]Debug不仅能发字符串信息，还能发送模板
        
         $template = View::factory('foo/bar');
         $this->response->debug($template, $data);
         $this->request->send_response();

 [!!]Debug默认会发送设置到`$this->response->body`中的信息

         $this->response->body('Hello world!');
         if(DEBUG_MODE){ 
             $this->response->debug();
         }

1. 扩展了 Kohana_Controller，支持基本的 XSS 和 CSRF 安全防范功能。

         //安全设置 —— init.php
         JKit::$security['csrf'] = true;
         JKit::$security['xss']  = true;

 [!!]安全设置在Controller中的实现代码：

        /**
         * 在 Controller 的 action 被调用前自动运行： 重载了 [Kohana_Controller::before]  
         * 如果设置了 `JKit::$environment=JKit::DEVELOPMENT` 并且在 url 中传递了 rdtest 参数，那么设置 [View::$debugging] 为 true，打开调试信息
         *
         * @return  void
         */
        function before(){
                if (JKit::$security['csrf'] && count($this->request->post()))
                { //防止跨站请求伪造
                        if(!Security::check($this->request->post('csrf_token'))){
                                $this->handle_err(array('err'=>'sys.security.csrf'),'csrf detected');
                        }
                }

                if(JKit::$security['xss'])
                { //防止跨站脚本攻击
                        $this->request->xss_clean();
                }

                return parent::before();
        }

1. 封装了 Logic 基本业务逻辑的处理，在 Action Controller 中可以用可读性非常好的代码简单实现业务逻辑的前端处理。

 [!!]对业务逻辑进行处理

         $myLogic = new Model_Logic_SomeLogic();
         $result = $myLogic->someMethod();
         $logic_result = Logic::parseResult($result);
         $this->jsonp($logic_result);

 [!!]Controller中封装了强大的Logic处理方法

         !$this->valid($validation) //提交数据失败
             or $this->err($result = $myLogic->someMethod()) //逻辑错误
                 or $this->err($result += $myLogic->anotherMethod()) //逻辑错误
                     or $this->ok($result); //成功返回

1. 集成了改良的 Log、 Profiler 和 RPC。

 [!!]Log信息输出
 
         JKit::$log->debug('debug');
         JKit::$log->warn('warn');
         JKit::$log->error('error');

 [!!]Profiler性能监控

        //测试循环效率
        for($i = 0; $i < 10; $i++){
                JKit::profile('Test', 'my_profile');
        }
        JKit::profile('Test', 'my_profile');         

 [!!]RPC远程调用
 
        $objRpc = new Rpc_Http('baidu_news', array(array('host' => '220.181.112.138', 'port' => 80)));
        $strContent = $objRpc->call(
                array(
                        'action' => '/n?cmd=1&class=internews&pn=1&from=tab',
                )
        );                
        $this->response
             ->headers('content-type','text/html;charset=gbk')
             ->body($strContent);        

 [!!]读配置的RPC调用

        $t = Rpc::call('ku6pass', '/nonibw-session-check.htm', array());
        $this->response
             ->headers('content-type','text/html;charset=gbk')
             ->body($t);

1. 集成了 [Lafe](https://github.com/akira-cn/lafe) Layout框架。

 [!!]Layout对Action Controller层透明，依然把View作为一个普通的View来对待就行了  
 区别是当 View::factory('foo/bar') 时，若 classes/layout/foo/bar.php 存在，则程序在渲染模板前进入处理 Layout 的过程

         class Layout_Foo_Bar extends Layout{
                function render($default_view_file){
                        return $this->a->fetch($default_view_file);
                }

                protected function layout_a($data){
                        $this->{"Header test"}=array('test'=>1);
                        $this->{"Body test"}=array('test'=>2);
                        $this->{"Body/test"}=array('test'=>3);
                        $this->{"//a.Footer/test"}=array('test'=>4);

                        $this->b();

                        //another b.Left，不和上面那个b.Left合在一起，所以加一个id
                        $this->{"//a.Body b#2.Left b.Left test"}=array('test'=>8);  

                        $this->_la_layout_xmap["a b#2"] += array(
                                                                "myclass" => "test",
                                                                "css" => "color:red",
                                                            );
                }

                private function b(){
                        $this->with("a.Body b");
                                $this->{"Left test"}=array('test'=>5);
                                $this->{"Right test"}=array('test'=>6);
                                $this->{"Right test"}=array('test'=>7);
                        $this->endwith();
                }
         }

 Layout处理完成之后，继续载入 views/foo/bar.php 模板渲染。