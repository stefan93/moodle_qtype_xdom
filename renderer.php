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
 * xdom question renderer class.
 *
 * @package    qtype
 * @subpackage xdom
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/xdom/my_classes.php');
/**
 * Generates the output for xdom questions.
 *
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_xdom_renderer extends qtype_renderer {
    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {
        $question = $qa->get_question();
        $response = $qa->get_last_qt_var('correctshape', '');
        $questiontext = $question->format_questiontext($qa);
        $placeholder = false;
        if (preg_match('/_____+/', $questiontext, $matches)) {
            $placeholder = $matches[0];
        }
        $input = '**subq controls go in here**';

        if ($placeholder) {
            $questiontext = substr_replace($questiontext, $input,
                    strpos($questiontext, $placeholder), strlen($placeholder));
        }
        $inputnamex = $qa->get_qt_field_name('xcord');
        $inputnamey = $qa->get_qt_field_name('ycord');
        $inputnamez = $qa->get_qt_field_name('zcord');
        $inputnamecs = $qa->get_qt_field_name('correctshape');
        $this->page->requires->js_call_amd('qtype_xdom/xdommodule','init');
        $result = html_writer::tag('div', $questiontext, array('class' => 'qtext'));
        $scene = $question->scene->makeSceneWithShapes();
        $result = $result . html_writer::tag('div', $scene, array('class' => 'scene'));
        $result = $result . html_writer::empty_tag('input',array('name' => $inputnamex, 'type' => 'hidden', 'id' => 'xcord', 'class' => 'r0'));
        $result = $result . html_writer::empty_tag('input',array('name' => $inputnamey, 'type' => 'hidden', 'id' => 'ycord', 'class' => 'r1'));
        $result = $result . html_writer::empty_tag('input',array('name' => $inputnamez, 'type' => 'hidden', 'id' => 'zcord', 'class' => 'r2'));
        $result = $result . html_writer::empty_tag('input',array('name' => $inputnamecs, 'type' => 'hidden', 'id' => 'choosenshape', 'class' => 'r3'));
        /* if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                    $question->get_validation_error(array('answer' => $currentanswer)),
                    array('class' => 'validationerror'));
        }*/
        //$scene->clear();
        unset($scene);
        return $result;
    }

    public function specific_feedback(question_attempt $qa) {
        // TODO.
        return '';
    }
}
