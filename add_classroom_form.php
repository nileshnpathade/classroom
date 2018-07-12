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

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}
require_once('../../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/filelib.php');
require_login();

class simplehtml_form extends moodleform {
    public function definition() {
        global $CFG, $DB;
        $mform = $this->_form;
        $locationid = $this->_customdata['location_id'];
        $mform->addElement('html', '<style>.characterlable { float:right; padding-left:137px;font-size:10px;}</style>');

        $mform->addElement('hidden', 'cid');
        $mform->setType('cid', PARAM_INT);
        $mform->addElement('hidden', 'location_id', $locationid);
        $mform->setType('location_id', PARAM_INT);
        $mform->addElement('header', 'addclassroom', get_string('addclassroom', 'format_classroom'));
        $mform->addElement('text', 'classroom', get_string('classroom', 'format_classroom'));
        $mform->setType('classroom', PARAM_RAW);
        $mform->addRule('classroom', get_string('required'), 'required', null, 'client');

        $mform->addElement('textarea', 'details', get_string("details", "format_classroom"), 'rows="5" cols="19" maxlength="5000"');
        $mform->setType('details', PARAM_RAW);
        $mform->addElement('html', '<div class="form-group row fitem"><div class="col-md-9 characterlable">5000 Character</div></div>');
        $mform->addElement('text', 'seats', get_string('seats', 'format_classroom'));
        $mform->setType('seats', PARAM_RAW);
        $mform->addRule('seats', get_string('number_required', 'format_classroom'), 'numeric', null, 'client');
        $mform->addRule('seats', get_string('required'), 'required', null, 'client');

        $mform->addElement('textarea', 'equipment', get_string("equipment", "format_classroom"), 'rows="5" cols="19" maxlength="5000"');
        $mform->setType('equipment', PARAM_RAW);
        $mform->addElement('html', '<div class="form-group row  fitem"><div class="col-md-9 characterlable">5000 Character</div></div>');
        $this->add_action_buttons(true, 'Submit');
    }

    // Custom validation should be added here.
    public function validation($data, $files) {
        global $CFG, $DB;
        $err = array();
        if ($data['seats'] <= 0) {
            $err['seats'] = get_string('zeroseats', 'format_classroom');
        }
        if (count($err) == 0) {
            return true;
        } else {
            return $err;
        }
    }
}