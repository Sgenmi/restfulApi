<?php

return $config = [
    'server' => [
        'worker_num' => 2,
        'open_cpu_affinity' => 4,
        'daemonize' => FALSE,
        'max_request' => 1000000,
        'task_worker_num' => 2,
        'log_file' => APP . '/log',
        'backlog' => 1024
    ],
    'elasticSearch' => [
        '192.168.1.218:9200'
    ]
];
