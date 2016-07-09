<?php
define('ajax',true);
$functions = array(
    'getPosAnsForScene' => array(
        'classname'   => 'x3domAjaxController',
        'methodname'  => 'getPosAnsForScene',
        'classpath'   => 'question/type/xdom/webservice_lib.php',
        'description' => 'Vraca shape-ove u trazenoj sceni.',
        'type'        => 'read',
        'ajax'        => true,
    ),
    'getSceneX3d' => array(
        'classname'   => 'x3domAjaxController',
        'methodname'  => 'getSceneX3d',
        'classpath'   => 'question/type/xdom/webservice_lib.php',
        'description' => 'Vraca x3d trazene scene',
        'type'        => 'read',
        'ajax'        => true,
    ),
    'saveNewShapesOnScene' => array(
        'classname'   => 'x3domAjaxController',
        'methodname'  => 'saveNewShapesOnScene',
        'classpath'   => 'question/type/xdom/webservice_lib.php',
        'description' => 'Cuvanje trenutno editovanje scene',
        'type'        => 'read',
        'ajax'        => true,
    ),
    'saveCoords' => array(
        'classname'   => 'x3domAjaxController',
        'methodname'  => 'saveCoords',
        'classpath'   => 'question/type/xdom/webservice_lib.php',
        'description' => 'Trigeruje event koji salje kordinate',
        'type'        => 'create',
        'ajax'        => true,
    ),
    'qtypeManagment' => array(
        'classname'   => 'x3domAjaxController',
        'methodname'  => 'qtypeManagment',
        'classpath'   => 'question/type/xdom/webservice_lib.php',
        'description' => 'Funkcije u vezi managment tab-a',
        'type'        => 'read',
        'ajax'        => true,
    )

);
// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
    'x3domWebService' => array(
        'functions' => array ('getPosAnsForScene','getSceneX3d','saveNewShapesOnScene','qtypeManagment'),
        'requiredcapability' => '',
        'restrictedusers' => 0,
        'enabled'=>1,
    )
);

?>