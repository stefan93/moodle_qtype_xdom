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
 * Defines the editing form for the xdom question type.
 *
 * @package    qtype
 * @subpackage xdom
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot.'/question/type/edit_question_form.php');
require_once($CFG->dirroot.'/question/type/xdom/lib.php');
require_once($CFG->libdir . '/pagelib.php');
/**
 * xdom question editing form definition.
 *
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_xdom_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        global $PAGE, $CFG;
        $event = \qtype_xdom\event\user_moved::create(array(
            'context' => context_module::instance(22)
        ));
        //$event->trigger();
        //izbrisi_file("helikopter.x3d","3");
       // ubaci_fajl("C:\\wamp/www/modeli/novo/example.html");
        //ubaci_folder("C:\\wamp/www/modeli/novo/binGeo/","/binGeo/");
        $fileinfo = array(
            'contextid' => CONTEXT_MODULE, // ID of context
            'component' => 'qtype_xdom',     // usually = component name
            'filearea' => 'qtype_xdom_scenebackground',     // usually = table name
            'itemid' => 3,               // usually = ID of row in table
            'filepath' => '/x3d/',           // any path beginning and ending in /
            'filename' => 'helikopter.x3d'); // any filee
        $PAGE->requires->css("/lib/jquery/ui-1.11.4/jquery-ui.css");
        $PAGE->requires->js_call_amd('qtype_xdom/xdommodule','edit_form');
        $scenes=get_all_scenes();
        $oldscene=-1;
        if(isset($this->question->id)) {
            $oldscene=$this->question->options->scene;
        }
        $scenes_html="";
        foreach($scenes as $id => $scene) {
            $selected = ($id==$oldscene ? "selected" : "");
            $scenes_html.="<option value='$id' $selected>$scene</option>";
        }
        // ako je staro pitanje ucitaj shape-ove za tu scenu
        $shapes_html="";
        if(isset($this->question->id)) {
            $sceneid=$this->question->options->scene;
            $scena=new Scene($sceneid);
            $shapes=$scena->getShapes();
            $oldanswer=array_pop($this->question->options->answers)->answer;
            foreach($shapes as $shape) {
                $selected=($shape->id_for_response==$oldanswer ? "selected" : "");
                $shapes_html.="<option value='$shape->id_for_response' $selected>$shape->name</option>";
            }
        } else {
            if (!is_null($scenes)) { // ako ima scena u bazi
                $sceneid=array_keys($scenes)[0]; // prva scena u nizu scena
                $scena=new Scene($sceneid);
                $shapes=$scena->getShapes();
                foreach($shapes as $shape) {
                    $shapes_html.="<option value='$shape->id_for_response'>$shape->name</option>";
                }
            }
        }
        $window=<<< EOT
        <div id="tabsloading" class="loading"></div>
        <div id="tabs" style="display:none">
          <ul>
            <li><a href="#fragment-1">Podesavanje pitanja</a></li>
            <li><a href="#fragment-2">Podesavanje trenutne scene</a></li>
            <li><a href="#fragment-3">Upravljanje scenama</a></li>
          </ul>
          <div id="fragment-1">
                <table>
                    <tr>
                        <td>
                            <h3>Scena:</h3>
                            <select name='choosenscene' id='izabranascena'>
                                $scenes_html
                            </select>
                        </td>
                        <td>
                            <h3>Tacan odgovor:</h3>
                            <select name='correctshape' id='izabranodgovor'>
                                $shapes_html
                            </select>
                            <div id="shapesLoading" class="loading" style="display:none"></div>
                        </td>
                    </tr>
                </table>
            </div>
          <div id="fragment-2">
            <div id='saveScene'>Sacuvaj scenu</div>
            <div id="sceneLoading" class="loading"></div>
            <div id='draggableArea'>
                <div id="scena" style="overflow:auto"></div>
                <ol id="shapesAll">
                </ol>
                <div class='hasmenu'></div>
            </div>
          </div>
          <div id="fragment-3">
          <div id="fragment-3-content">
          <div id="scenes">
            <h1>Scene</h1>
            <div class='btn' onclick='sceneAddSceneDlg()'>Dodaj scenu</div>
            <table id="tableScenes">
                <thead>
                </thead>
            </table>
            </div>
            <div id="shapes">
            <h1>Predmeti</h1>
            <div class='btn' onclick='shapeAddShapeDlg()'>Dodaj predmet</div>
            <table id="tableShapes">
                <thead>
                </thead>
            </table>
            </div>
            <div id="backgroundScenes">
            <h1>Pozadinske scene</h1>
            <div class='btn' onclick='shapeAddBSceneDlg()'>Dodaj pozadinksu scenu</div>
            <table id="tableBScenes">
                <thead>
                </thead>
            </table>
            </div>
          </div>
        </div>
EOT;
        $mform->addElement('header', 'xdomsettings', 'X3Dom podesavanja');
            $mform->addElement('html',$window);
            // prvi shape
            $prvi_shape=array_shift($shapes);
            //hidden elementi za correctshape i sceneid
            $mform->addElement('hidden','correctshape',$prvi_shape->id_for_response);
            $mform->setType('correctshape',PARAM_INT);
            $mform->addRule('correctshape','Morate da izaberete tacan odgovor','required',null,'server');
            $mform->addElement('hidden','sceneid',isset($sceneid) ? $sceneid : -1);
            $mform->setType('sceneid',PARAM_INT);
            $mform->addRule('sceneid','Morate da izaberete scenu','required',null,'server');
        $mform->closeHeaderBefore('xdomsettings');
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question);
        $question = $this->data_preprocessing_hints($question);
        return $question;
    }

    public function qtype() {
        return 'xdom';
    }
}