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
 * xdom question definition class.
 *
 * @package    qtype
 * @subpackage xdom
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Represents a xdom question.
 *
 * @copyright  Stefan Pantic (pantic_stefan@hotmail.com)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_xdom_question extends question_graded_automatically {
    public $scene;
    public $correctshape;
    public function get_expected_data() {
        return array('correctshape' => PARAM_INT);
    }
    public function summarise_response(array $response) {
        if (array_key_exists('correctshape', $response))
            return $response['correctshape'];
        return null;
    }

    public function is_complete_response(array $response) {
        return (array_key_exists('correctshape', $response));
    }

    public function get_validation_error(array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string('pleaseselectananswer', 'qtype_xdom');
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        return (question_utils::arrays_same_at_key_missing_is_blank(
            $prevresponse, $newresponse, 'correctshape'));
    }


    public function get_correct_response() {
        return array('correctshape' => $this->correctshape);
    }


    public function check_file_access($qa, $options, $component, $filearea,
            $args, $forcedownload) {
        if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);

        } else {
            return parent::check_file_access($qa, $options, $component, $filearea,
                    $args, $forcedownload);
        }
    }

    public function grade_response(array $response) {
        $fraction=0;
        if ($this->correctshape==$response['correctshape'])
            $fraction=1;

        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }
}
