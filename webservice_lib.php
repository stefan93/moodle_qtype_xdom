<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 9.5.2016
 * Time: 9:46
 */
require_once($CFG->dirroot . '/question/type/xdom/lib.php');
require_once($CFG->dirroot . '/question/type/xdom/my_classes.php');
require_once("$CFG->libdir/externallib.php");

class x3domAjaxController extends external_api
{

    public static function getPosAnsForScene_parameters()
    {
        return new external_function_parameters(
            array('sceneid' => new external_value(PARAM_INT, 'Parametar id scene za koji se traze shape-ovi,"', VALUE_OPTIONAL))
        );
    }
    public static function getPosAnsForScene($sceneid)
    {
        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::getPosAnsForScene_parameters(),
            array('sceneid' => $sceneid));
        if (isset($params['sceneid']))
            return get_shapes_from_scene($params['sceneid']);
        return get_all_shapes();
    }
    public static function getPosAnsForScene_returns()
    {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'id shape-a'),
                    'name' => new external_value(PARAM_TEXT, 'Naziv shape-a'),
                    'shapexdom' => new external_value(PARAM_RAW, 'x3d shape-a')
                )
            )
        );
    }

    public static function getSceneX3d_parameters()
    {
        return new external_function_parameters(
            array('sceneid' => new external_value(PARAM_INT, 'Parametar id scene za koji se trazi x3d,"', VALUE_REQUIRED))
        );
    }
    public static function getSceneX3d($sceneid)
    {
        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::getSceneX3d_parameters(),
            array('sceneid' => $sceneid));
        $s = new Scene($params['sceneid']);
        return $s->makeSceneWithShapes();
    }
    public static function getSceneX3d_returns()
    {
        return new external_single_structure(
            array(
                'x3d' => new external_value(PARAM_TEXT, 'x3d trazene scene.')
            )
        );
    }

    public static function saveCoords_parameters()
    {
        return new external_function_parameters(
            array('x' => new external_value(PARAM_FLOAT, 'X koordinata', VALUE_REQUIRED),
                'y' => new external_value(PARAM_FLOAT, 'Y koordinata', VALUE_REQUIRED),
                'z' => new external_value(PARAM_FLOAT, 'Z koordinata', VALUE_REQUIRED),)
        );
    }
    public static function saveCoords($x, $y, $z)
    {
        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::saveCoords_parameters(),
            array('x' => $x, 'y' => $y, 'z' => $z));
        $cmid=required_param('cmid', PARAM_INT);
        $objectid=required_param('attempt', PARAM_INT);
        $event = \qtype_xdom\event\user_moved::create(array(
            'context' => context_module::instance($cmid), 'other' => array('x' => $params['x'], 'y' => $params['y'], 'z' => $params['z']),
            'objectid' => $objectid
        ));
        $event->trigger();
        return true;

    }
    public static function saveCoords_returns()
    {
        return new external_value(PARAM_BOOL, 'Boolean vrednost koja oznacava ispravnost akcije', VALUE_REQUIRED);
    }

    public static function saveNewShapesOnScene_parameters()
    {
        return new external_function_parameters(
            array(
                'sceneid' => new external_value(PARAM_INT, 'Parametar id scene za koji se traze shape-ovi,"', VALUE_REQUIRED),
                'newShapes' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'shapeid' => new external_value(PARAM_INT, 'Id shape-a koji traba da se ubaci', VALUE_REQUIRED),
                            'shapeCords' => new external_single_structure(
                                array(
                                    'x' => new external_value(PARAM_FLOAT, 'X koordinata', VALUE_REQUIRED),
                                    'y' => new external_value(PARAM_FLOAT, 'Y koordinata', VALUE_REQUIRED),
                                    'z' => new external_value(PARAM_FLOAT, 'Z koordinata', VALUE_REQUIRED),
                                ), 'Koordinate shape-a', VALUE_REQUIRED
                            )
                        ), 'Podaci o shape-u', VALUE_REQUIRED
                    ), 'Niz sa shape-ovima koji treba da se dodaju', VALUE_REQUIRED
                )
            )
        );
    }
    public static function saveNewShapesOnScene($sceneid, $newShapes)
    {
        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::saveNewShapesOnScene_parameters(),
            array('sceneid' => $sceneid, 'newShapes' => $newShapes));
        $scene = new Scene($params['sceneid']);
        $newShapesData = $params['newShapes'];
        $scene->edit($newShapesData);
        return true;
    }
    public static function saveNewShapesOnScene_returns()
    {
        return new external_value(PARAM_BOOL, 'Boolean vrednost koja oznacava ispravnost akcije', VALUE_REQUIRED);
    }

    public static function qtypeManagment_parameters()
    {
        return new external_function_parameters(
            array('type' => new external_value(PARAM_TEXT, 'Koja se operacija radi"', VALUE_REQUIRED),
            'id' => new external_value(PARAM_INT, 'Id u vezi', VALUE_OPTIONAL))
        );
    }
    public static function qtypeManagment($type,$id)
    {
        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::qtypeManagment_parameters(),
            array('type' => $type, 'id' => $id));
        switch ($params['type']) {
            case 'get_all_scenes':
                $result['data'] = get_all_scenes();
                break;
            case 'get_all_shapes':
                $result['data'] = get_all_shapes();
                break;
            case 'get_all_backgroundscenes':
                $result['data'] = get_all_background_scenes();
                break;
            case 'delete_scene_by_id':
                $result = delete_scene($params['id']);
                break;
            case 'delete_shape_by_id':
                $result = delete_shape($params['id']);
                break;
            case 'delete_bscene_by_id':
                throw new UnexpectedValueException("Not yet implemented");
                break;
            default:
                $result = "";
        }
        return $result;
    }
    public static function qtypeManagment_returns()
    {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'id shape-a'),
                    'name' => new external_value(PARAM_TEXT, 'Naziv shape-a'),
                )
            )
        );
    }

    public static function saveNewComponent_parameters()
    {
        return new external_function_parameters(
            array(
                'type' => new external_value(PARAM_TEXT,'tip nove komponente',VALUE_REQUIRED),
                'data' => new external_value(PARAM_RAW, 'X3d koji treba da se cuva', VALUE_REQUIRED),
                'name' => new external_value(PARAM_TEXT, 'naziv nove komponente', VALUE_REQUIRED)
            )
        );
    }
    public static function saveNewComponent($type, $data, $name)
    {
        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::saveNewComponent_parameters(),
            array('type' => $type,'data' => $data, 'name' => $name, ));
        $ok=false;
        switch($type) {
            case 'scene':
                $ok = save_new_scene($params['name'],$params['data']);
                break;
            case 'backgroundScene':
                $ok = save_new_background_scene($params['name'],$params['data']);
                break;
            case 'shape':
                $ok = save_new_shape($params['name'],$params['data']);
                break;
            default:
        }
        return $ok;
    }
    public static function saveNewComponent_returns()
    {
        return new external_value(PARAM_BOOL, 'Boolean vrednost koja oznacava ispravnost akcije', VALUE_REQUIRED);
    }
}
