<?php

/**
 * Description of HttpServer
 *
 * @author Sgenmi
 * @date 2017-03-28
 * @email 150560159@qq.com
 */
define("BASE", __DIR__);
define("APP", BASE . "/app");
define("VER", "v1");
define("APP_NAME", "ESAPI");


// 加载类
spl_autoload_register(function ($class_name) {

//    echo $class_name,"\n";
    $filePath = [
        'app' => APP,
        'lib' => BASE . "/lib"
    ];
    foreach ($filePath as $v) {
        $file = $v . "/" . str_replace('\\', "/", $class_name) . '.php';
        if (is_file($file)) {
            require_once $file;
            return TRUE;
        }
    }
    throw new Exception("操作不存在");
});
//加载函数
include_once BASE . "/lib/fun.php";

class HttpServer {

    public static $instance;
    public static $http;
    public static $get;
    public static $header;
    public static $server;
    public static $config;
    public static $router;

    public function __construct() {
        swoole_set_process_name(APP_NAME);
        HttpServer::$config=   require APP . "/Config/conf.php";
                    //可以合并 array_merge
        HttpServer::$config['server']['elasticSearch'] = HttpServer::$config['elasticSearch'];
        
        $http = new Swoole\Http\Server("0.0.0.0", 3388, SWOOLE_PROCESS);
        $http->set(HttpServer::$config['server']);
        $http->on('workerStart', array($this, 'onWorkerStart'));
        $http->on('request', array($this, 'onRequest'));
        $http->on("task", array($this, "onTask"));
        $http->on("finish", array($this, "onFinish"));
        HttpServer::$http = $http;
        $http->start();
    }
    
  public function onWorkerStart($serv, $workerId) {
        //路由汇总
        $router = require APP . "/Config/router.php";
        foreach ($router as $v) {
            $patternArr = explode(" ", $v['pattern']);
            $method = strtoupper(trim($patternArr[0]));
//            $key = $method."_".$v['controller']."_".$v['action'];
            HttpServer::$router[$method][] = $v;
        }
        $processName = APP_NAME . "   worker";
        if ($workerId >= $serv->setting['worker_num']) {
            $processName = APP_NAME . "  task worker";
        }
        swoole_set_process_name($processName);
    }

    //restful 正则配置
    private function checkUri($str, $pattern) {
        $ma = array();
        $pattern = ltrim(rtrim($pattern, "/"));
        $pattern = "/" . str_replace("/", "\/", $pattern) . "\/?$/";
        $pattern = str_replace(":s", "([^\/]+)", $pattern);
        if (preg_match($pattern, $str, $ma) > 0) {
            return $ma;
        }
        return null;
    }

    /*
     * 路由 动态分配
     */

    private function onRouter(Swoole\Http\Request $request) {

        $method = \HttpServer::$server['request_method'];
        $router = \HttpServer::$router[$method];
        //非法的操作方式
        if (!isset(\HttpServer::$router[$method])) {
            return ['code' => 2001];
        }
        //移除版本配置
        $uri = str_replace("/api/" . VER, "", $request->server['request_uri']);

        foreach ($router as $v) {
            $pats = explode(" ", $v['pattern']);
            $uriArr = $this->checkUri(strtolower($uri), strtolower($pats[1]));
            if ($uriArr) {
                //移除第一个元素,保留实际参数
                array_shift($uriArr);
                $checkInfo = array(
                    'params' => $uriArr,
                    'controller' => $v['controller'],
                    'action' => $v['action']
                );
                break;
            }
        }
        //接口不存在
        if (!isset($checkInfo)) {
            return ['code' => 2002];
        }
        $className = "\\Controller\\" . ucfirst($checkInfo['controller']);
        //如果 class不存在,捕异常,返回错误code,控制器不存在
        try {
            $contrObj = new $className();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return ['code' => 2003];
        }
        //方法调用
        $ac = $checkInfo['action'] . 'Action';
        if($checkInfo['params']){
                               //也可以放到post,或get里,传过去,支持传统方式 
           //HttpServer::$get['a']=$checkInfo['params'][0];
          //HttpServer::$post['a']=$checkInfo['params'][0];
            $ret = $contrObj->$ac($checkInfo['params']);
        }else{
            $ret = $contrObj->$ac();
                    }
        
        print_r($ret);

        return $ret ;
    }



    public function onRequest(Swoole\Http\Request $request, Swoole\Http\Response $response) {
        if (isset($request->header)) {
            HttpServer::$header = $request->header;
        } else {
            HttpServer::$header = [];
        }
        if (isset($request->get)) {
            HttpServer::$get = $request->get;
        } else {
            HttpServer::$get = [];
        }

        if (isset($request->server)) {
            HttpServer::$server = $request->server;
            HttpServer::$server['request_method'] = strtoupper(HttpServer::$server['request_method']);
        } else {
            HttpServer::$server = [];
        }

//        print_r($request->header);
//        print_r($request->get);
//        print_r($request->server);
//        print_r($request->post);


       $retArr =  $this->onRouter($request);

        $response->status(200);
        $response->write(json_encode($retArr));
        $response->end();
    }

    public function onTask(Swoole\Server $serv, $taskId, $fromId, $taskData) {
                 
       
       return array("a"=>1);
    }

    function onFinish(Swoole\Server $serv, $taskId, $data) {
        return $data;
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new HttpServer;
        }
        return self::$instance;
    }

}

HttpServer::getInstance();
