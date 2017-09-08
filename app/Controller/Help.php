<?php

namespace Controller;

/**
 * Description of Help
 *
 * @author Sgenmi
 * @date 2017-9-7
 * @Email 150560159@qq.com
 */
class Help extends \Controller {
    
    public function apiListAction(){
        return \HttpServer::$router;
    }
    
}
