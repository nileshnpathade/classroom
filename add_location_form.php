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
    die('Direct access to this script is forbidden.');    // It must be included from a Moodle page.
}
require_once('../../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/filelib.php');
require_login();

class simplehtml_form_location extends moodleform {

    public function definition() {
        global $CFG, $PAGE, $DB;
        $mform = $this->_form;  // Don't forget the underscore!
        $mform->addElement('hidden', 'cid');
        $mform->setType('cid', PARAM_INT);
        $mform->addElement('html', '<script src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyAagk3rf5yEU6pZBaosT-A1Dkge5DHAJic"></script>');

        $PAGE->requires->js( new moodle_url($CFG->wwwroot . '/course/format/classroom/myjavascript.js'));
        $mform->addElement('header', 'addlocation', get_string('addlocation', 'format_classroom'));

        $mform->addElement('text', 'location', get_string('location', 'format_classroom'), 'placeholder="Enter Location Name"');
        $mform->setType('location', PARAM_RAW);
        $mform->addRule('location', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'address', get_string('address', 'format_classroom'), 'placeholder="Enter Address"');
        $mform->setType('address', PARAM_RAW);
        $mform->addRule('address', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'phoneno', get_string('phoneno', 'format_classroom'), 'placeholder="Enter Phone Number"');
        $mform->setType('phoneno', PARAM_RAW);
        $mform->addRule('phoneno', get_string('number_required', 'format_classroom'), 'numeric', null, 'client');

        $mform->addElement('text', 'emailid', get_string('emailid', 'format_classroom'), 'placeholder="Enter Email ID"');
        $mform->setType('emailid', PARAM_RAW);

        $mform->addElement('html', '<div id="map" style="height:200px"></div>');

        $classrooms = $DB->get_records_sql('select id,classroom from {classroom} where isdeleted != ?', array(0));
        $array = array();
        $key = array(null => 'Select Classroom');
        $i = 0;
        foreach ($classrooms as $classr) {
            $key[$classr->id] = $classr->classroom;
            $i++;
        }
        $this->add_action_buttons(true, 'Submit');
    }

    // Custom validation should be added here.
    public function validation($data, $files) {
        global $CFG, $DB;
        $err = array();
        if ($data['emailid']) {
            if (!validate_email($data['emailid'])) {
                $err['emailid'] = get_string('invalidemail');
            }
        }
        if (empty(trim($data['location']))) {
            $err['location'] = get_string('required');
        }
        if (empty(trim($data['address']))) {
            $err['address'] = get_string('required');
        }
        if (count($err) == 0) {
            return true;
        } else {
            return $err;
        }
    }
}