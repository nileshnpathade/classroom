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
global $DB;
require_login();
class location_edit_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $PAGE;

        $mform = $this->_form;
        $cid = $this->_customdata['id'];
        $location = $this->_customdata['location'];
        $address = $this->_customdata['address'];
        $phoneno = $this->_customdata['phoneno'];
        $emailid = $this->_customdata['emailid'];
        $script = '<script src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyAagk3rf5yEU6pZBaosT-A1Dkge5DHAJic"></script>';

        $mform->addElement('html', $script);

        $PAGE->requires->js( new moodle_url($CFG->wwwroot . '/course/format/classroom/editmap.js'));
        $mform->addElement('hidden', 'cid', $cid);
        $mform->setType('cid', PARAM_INT);

        $mform->addElement('header', 'update_location', get_string('update_location', 'format_classroom'));
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
        $this->add_action_buttons(true, 'Submit');
    }

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