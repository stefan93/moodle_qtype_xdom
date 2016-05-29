<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 29.5.2016
 * Time: 18:08
 */
namespace qtype_xdom\event;
defined('MOODLE_INTERNAL') || die();
/**
 * The EVENTNAME event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - PUT INFO HERE
 * }
 *
 * @since     Moodle MOODLEVERSION
 * @copyright 2014 YOUR NAME
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class user_moved extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        //$this->data['objecttable'] = '...';
    }

    public static function get_name() {
        return get_string('eventEVENTNAME', 'FULLPLUGINNAME');
    }

    public function get_description() {
        return "The user with id {$this->userid} created ... ... ... with id {$this->objectid}.";
    }

    public function get_url() {
        //return new \moodle_url('....', array('parameter' => 'value', ...));
    }

    public function get_legacy_logdata() {
        // Override if you are migrating an add_to_log() call.
        return array($this->courseid, 'PLUGINNAME', 'LOGACTION',
            '...........',
            $this->objectid, $this->contextinstanceid);
    }

    public static function get_legacy_eventname() {
        // Override ONLY if you are migrating events_trigger() call.
        return 'MYPLUGIN_OLD_EVENT_NAME';
    }

    protected function get_legacy_eventdata() {
        // Override if you migrating events_trigger() call.
        $data = new \stdClass();
        $data->id = $this->objectid;
        $data->userid = $this->relateduserid;
        return $data;
    }
}