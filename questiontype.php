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
 * Question type class for the xdom question type.
 *
 * @package    qtype
 * @subpackage xdom
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/xdom/question.php');
require_once($CFG->dirroot . '/question/type/xdom/my_classes.php');

/**
 * The xdom question type.
 *
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_xdom extends question_type {

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }
    public function delete_question($questionid, $contextid) {
        global $DB;
        $DB->delete_records('question_xdom', array('question' => $questionid));

        parent::delete_question($questionid, $contextid);
    }
    public function save_question_options($question) {
        global $DB;
        $result = new stdClass();
        $context = $question->context;

        // Fetch old answer ids so that we can reuse them.
        $oldanswers = $DB->get_records('question_answers',
            array('question' => $question->id), 'id ASC');

        // Save the coords in answer
        $answer = array_shift($oldanswers);
        if (!$answer) {
            $answer = new stdClass();
            $answer->question = $question->id;
            $answer->answer = '';
            $answer->feedback = '';
            $answer->id = $DB->insert_record('question_answers', $answer);
        }
        $answer->answer = $question->correctshape; //  id of the correct shape - sceneshape id
        $answer->fraction = '1';
        $DB->update_record('question_answers', $answer);

        // Delete any left over old answer records.
        $fs = get_file_storage();
        foreach ($oldanswers as $oldanswer) {
            $fs->delete_area_files($context->id, 'question', 'answerfeedback', $oldanswer->id);
            $DB->delete_records('question_answers', array('id' => $oldanswer->id));
        }


        // Save question options in question_xdom
        if ($options = $DB->get_record('question_xdom', array('question' => $question->id))) {
            // No need to do anything, since the answer IDs won't have changed
            // But we'll do it anyway, just for robustness.
            $options->scene=$question->sceneid;
            $DB->update_record('question_xdom', $options);
        } else {
            $options = new stdClass();
            $options->question    = $question->id;
            $options->scene=$question->sceneid;
            $DB->insert_record('question_xdom', $options);
        }


        $this->save_hints($question);
        return true;
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        $question->scene = new stdClass();
        $answer=array_pop($questiondata->options->answers);
        $question->correctshape=$answer->answer;
        $sceneid = $questiondata->options->scene;
        $scene = new Scene($sceneid);
        $question->scene = $scene;
        parent::initialise_question_instance($question, $questiondata);
    }

    public function get_random_guess_score($questiondata) {
        // TODO.
        return 0;
    }
    public function get_possible_responses($questiondata) {
        // TODO.
        return array();
    }
    public function get_question_options($question) {
        global $DB, $OUTPUT;
        // Get additional information from database
        // and attach it to the question object.
        if (!$question->options = $DB->get_record('question_xdom',
            array('question' => $question->id))) {
            echo $OUTPUT->notification('Error: Missing question options!');
            return false;
        }

        // Load the answers.
        if (!$question->options->answers = $DB->get_records('question_answers',
            array('question' =>  $question->id), 'id ASC')) {
            echo $OUTPUT->notification('Error: Missing question answers for xdom question ' .
                $question->id . '!');
            return false;
        }

        return true;
    }
}
