<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 22.3.2016
 * Time: 23:02
 */

defined('MOODLE_INTERNAL') || die();
require_once 'simple_html_dom.php';

class Scene {
    private $id;
    private $shapes = array();
    private $background_scene_x3d;
    private $name;
    public function __construct($id) {
        $this->id = $id;
    }
    private function _loadBackgoundSceneX3D() {
        global $DB,$PAGE;
        $temp = $DB->get_record_sql('SELECT zipfilename,{qtype_xdom_scenebackground}.name,{qtype_xdom_scenebackground}.id
        FROM {qtype_xdom_scenebackground}
        JOIN {qtype_xdom_scenes}
        ON {qtype_xdom_scenes}.scenebackground={qtype_xdom_scenebackground}.id
        WHERE {qtype_xdom_scenes}.id=?
        ', array($this->id));
        $this->name = $temp->name;
        $fs = get_file_storage();
        $fileinfo = array(
            'contextid' => CONTEXT_MODULE, // ID of context
            'component' => 'qtype_xdom',     // usually = component name
            'filearea' => 'qtype_xdom_scenebackground',     // usually = table name
            'itemid' => $temp->id,               // usually = ID of row in table
            'filepath' => '/x3d/',           // any path beginning and ending in /
            'filename' => $temp->zipfilename); // any filename
        $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
            $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
        if ($file) {
            $contents = $file->get_content();
            $this->background_scene_x3d = $contents;
        } else {
            throw new file_exception('Doslo je do greske. Nema trazene scene.Kontekst:'.$PAGE->context->id);
        }

    }
    private function _loadName() {
        global $DB;
        $temp = $DB->get_record_sql('SELECT {qtype_xdom_scenebackground}.name
        FROM {qtype_xdom_scenebackground}
        JOIN {qtype_xdom_scenes}
        ON {qtype_xdom_scenes}.scenebackground={qtype_xdom_scenebackground}.id
        WHERE {qtype_xdom_scenes}.id=?
        ', array($this->id));
        $this->name = $temp->name;
    }
    private function _loadShapes() {
        global $DB;
        $shapes = $DB->get_records_sql('SELECT {qtype_xdom_sceneshapes}.id
        FROM {qtype_xdom_shapes}
        JOIN {qtype_xdom_sceneshapes}
        ON {qtype_xdom_sceneshapes}.shape={qtype_xdom_shapes}.id
        WHERE {qtype_xdom_sceneshapes}.scene=?',array($this->id));
        foreach ($shapes as $shape) {
            $shapeObj = new Shape(null,$shape->id);
            array_push($this->shapes, $shapeObj);
        }
    }
    public function getBackgroundSceneX3D() {
        if (is_null($this->background_scene_x3d)) {
            $this->_loadBackgoundSceneX3D();
        }
        return $this->background_scene_x3d;
    }
    public function getId() {
        return $this->id;
    }
    public function getName() {
        if(is_null($this->name)) {
            $this->_loadName();
        }
        return $this->name;
    }
    public function getShapes() {
        if(empty($this->shapes)) {
            $this->_loadShapes();
        }
        return $this->shapes;
    }
    public function edit($shapes_data) {
        global $DB;
        $tr = $DB->start_delegated_transaction();
        try {
            $DB->delete_records('qtype_xdom_sceneshapes',array('scene' => $this->getId()));
            foreach($shapes_data as $shape_data) {
                $data = new stdClass();
                $data->scene = $this->getId();
                $data->shape = $shape_data['shapeid'];
                $data->xcoord = $shape_data['shapeCords']['x'];
                $data->ycoord = $shape_data['shapeCords']['y'];
                $data->zcoord = $shape_data['shapeCords']['z'];
                $DB->insert_record('qtype_xdom_sceneshapes',$data);
            }
            $tr->allow_commit();
        } catch (Exception $e) {
            $tr->rollback($e);
            return false;
        }
        return true;
    }
    /** @noinspection PhpExpressionResultUnusedInspection */
    public function makeSceneWithShapes() {
        $sceneWithShapes = str_get_html($this->getBackgroundSceneX3D());
        $shapes=$this->getShapes();
        foreach($shapes as $shape) {
            $shape_html = str_get_html($shape->shape_x3d);
            $shape_html->getElementByTagName('Transform')->setAttribute('translation',"$shape->x $shape->y $shape->z");
            $shape_html->getElementByTagName('Transform')->setAttribute('def',"$shape->id_for_response");
            $shape_html->getElementByTagName('Transform')->setAttribute('shapeName',"$shape->name");
            $shape_html->getElementByTagName('Transform')->setAttribute('shapeId',"$shape->id");
            $sceneWithShapes->getElementByTagName('scene')->innertext.=$shape_html;
            $shape_html->clear();
        }
        $a=$sceneWithShapes->save();
        $sceneWithShapes->clear();
        return $a;
    }
    static public function getAllScenes() {
        global $DB;
        $scenes=$DB->get_records('qtype_xdom_scenes');
        return $scenes;
    }
}

class Shape {
    public $id;
    public $id_for_response;
    public $shape_x3d;
    public $name;
    public $x, $y, $z;
    public function __construct($shapeID=null,$sceneShapeId=null)  {
        global $DB;
            if (!is_null($sceneShapeId)) {
                $shape = $DB->get_record_sql('SELECT {qtype_xdom_shapes}.*,{qtype_xdom_sceneshapes}.id as shapeIdForResponse,{qtype_xdom_sceneshapes}.xcoord,{qtype_xdom_sceneshapes}.ycoord,{qtype_xdom_sceneshapes}.zcoord
            FROM {qtype_xdom_shapes}
            JOIN {qtype_xdom_sceneshapes}
            ON {qtype_xdom_sceneshapes}.shape={qtype_xdom_shapes}.id
            WHERE {qtype_xdom_sceneshapes}.id=?', array($sceneShapeId));
                $this->x = $shape->xcoord;
                $this->y = $shape->ycoord;
                $this->z = $shape->zcoord;
                $this->id_for_response = $shape->shapeidforresponse;
                $this->shape_x3d = $shape->shapexdom;
                $this->name = $shape->name;
                $this->id = $shape->id;
            } else if(!is_null($shapeID)) {
                $shape = $DB->get_record_sql('SELECT {qtype_xdom_shapes}.*
            FROM {qtype_xdom_shapes}
            WHERE {qtype_xdom_shapes}.id=?', array($shapeID));
                $this->shape_x3d = $shape->shapexdom;
                $this->name = $shape->name;
                $this->id = $shape->id;
            }
    }
}