<?php

namespace Controller;

/**
 * Description of LouPan
 *
 * @author Sgenmi
 * @date 2017-9-6
 * @Email 150560159@qq.com
 */
class LouPan extends \Controller {

    //get /loupan
    public function ListAction() {
        $esClient = \ElasticSearch::getClient();
        if (!$esClient) {
            return ['code' => 88999];
        }
//        $esClient = new \Elasticsearch\Client();

        $params = [
            'index' => 'map_house',
            'type' => 'map',
            'id' => '7be12852dd79faadf1bfa3af6860aa94'
        ];

       $d =  $esClient->get($params);

       print_r($d);

        $ela = new \Elastica\Client($params);
       
       
       
       

        return $d ;
//       print_r($esClient);
//       print_r(unserialize($esClient));
    }

}
