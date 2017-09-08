<?php

/**
 * Description of Controller
 *
 * @author Sgenmi
 * @date 2017-9-7
 * @Email 150560159@qq.com
 */
class Controller {
    
    public function __call($name, $arguments) {
        return ['code'=>99999];
    }
}
