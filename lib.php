<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Serve question type files
 *
 * @since      2.0
 * @package    qtype_xdom
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Checks file access for xdom questions.
 * @package  qtype_xdom
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool
 */
function qtype_xdom_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;
    require_once($CFG->libdir . '/questionlib.php');
    //question_pluginfile($course, $context, 'qtype_xdom', $filearea, $args, $forcedownload, $options);
    //send_file('qtype_xdom/qtype_xdom_scenebackground/3/','helikopter.x3d');
    $fs = get_file_storage();
    $fileinfo = array(
        'contextid' => CONTEXT_MODULE, // ID of context
        'component' => 'qtype_xdom',     // usually = component name
        'filearea' => 'qtype_xdom_scenebackground',     // usually = table name
        'itemid' => $args[0],               // usually = ID of row in table
        'filepath' => "/$args[1]/",           // any path beginning and ending in /
        'filename' => $args[2]); // any filename
    $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
        $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
    send_stored_file($file);
}

function get_all_scenes() {
    global $DB;
    return $DB->get_records_menu('qtype_xdom_scenes',NULL,NULL,'id,name');
}
function get_all_shapes() {
    global $DB;
    return $DB->get_records_sql("SELECT * FROM mdl_qtype_xdom_shapes");
}
function get_all_shapes_id_name() {
    global $DB;
    return $DB->get_records_menu('qtype_xdom_shapes',NULL,NULL,'id,name');
}
function get_shapes_from_scene($scene_id) {
    global $DB;
    return $DB->get_records_sql('SELECT mdl_qtype_xdom_sceneshapes.id,name,shapexdom FROM mdl_qtype_xdom_shapes
                                  INNER JOIN mdl_qtype_xdom_sceneshapes ON mdl_qtype_xdom_shapes.id = mdl_qtype_xdom_sceneshapes.shape
                                  WHERE scene=?',array($scene_id));
}
function get_all_background_scenes() {
    global $DB;
    return $DB->get_records_menu('qtype_xdom_scenebackground',NULL,NULL,'id,name');
}
function ubaci_folder($dir,$filepath) {
    $fs = get_file_storage();
    //$dir='C:\\wamp\www\modeli\novo\binGeo\\';
    if (!is_dir($dir)) {
        throw new moodle_exception($dir.' nije direktorijum.');
    }
    $dh = opendir($dir);
    while (($file = readdir($dh)) !== false){
        if ($file=='.' || $file=='..')
            continue;
        $fileinfo = array(
            'contextid' => CONTEXT_MODULE, // ID of context
            'component' => 'qtype_xdom',     // usually = component name
            'filearea' => 'qtype_xdom_scenebackground',     // usually = table name
            'itemid' => 3,               // usually = ID of row in table
            'filepath' => $filepath,           // any path beginning and ending in /
            'filename' => $file
        );
        $fs->create_file_from_pathname($fileinfo,$dir.$file);
        //var_dump($file);
    }
}
function izbrisi_file($filename,$itemid) {
    $fs = get_file_storage();
    $fileinfo = array(
        'contextid' => CONTEXT_MODULE, // ID of context
        'component' => 'qtype_xdom',     // usually = component name
        'filearea' => 'qtype_xdom_scenebackground',     // usually = table name
        'itemid' => $itemid,               // usually = ID of row in table
        'filepath' => '/x3d/',           // any path beginning and ending in /
        'filename' => $filename); // any filename
    $file = $fs->delete_area_files($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
        $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
    return $file;
}
function ubaci_fajl($externalfilepath) {
    $fs = get_file_storage();
    $fileinfo = array(
        'contextid' => CONTEXT_MODULE, // ID of context
        'component' => 'qtype_xdom',     // usually = component name
        'filearea' => 'qtype_xdom_scenebackground',     // usually = table name
        'itemid' => 3,               // usually = ID of row in table
        'filepath' => '/x3d/',           // any path beginning and ending in /
        'filename' => 'helikopter.x3d'); // any filename
    $file = $fs->create_file_from_pathname($fileinfo,$externalfilepath);
    return $file;
}