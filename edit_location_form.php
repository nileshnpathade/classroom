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
 * Editing location form.
 *
 * @package   format_classroom
 * @copyright 2018 eNyota Learning Pvt Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    // It must be included from a Moodle page.
}
require_once('../../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/filelib.php');
require_login();
/**
 * Class editing location form.
 *
 * @package   format_classroom
 * @copyright 2018 eNyota Learning Pvt Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class location_edit_form extends moodleform {
    /**
     * Modify/Update location form.
     *
     * @return void
     */
    public function definition() {
        global $CFG, $PAGE;

        $mform = $this->_form;
        $cid = $this->_customdata['id'];
        $location = $this->_customdata['location'];
        $address = $this->_customdata['address'];
        $phoneno = $this->_customdata['phoneno'];
        $emailid = $this->_customdata['emailid'];
        $script = '<script
        src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyA3RCnSbZgjqVKOcixGRKB3cAbF6WdPc5M"></script>';

        $mform->addElement('html', $script);

        $PAGE->requires->js( new moodle_url($CFG->wwwroot . '/course/format/classroom/editmap.js'));
        $mform->addElement('hidden', 'cid', $cid);
        $mform->setType('cid', PARAM_INT);

        $mform->addElement('header', 'update_location', get_string('update_location', 'format_classroom'));
        $mform->addElement('text', 'location', get_string('location', 'format_classroom'), 'placeholder="Enter Location Name"');
        $mform->setType('location', PARAM_RAW);
        $mform->addHelpButton('location', 'location', 'format_classroom');
        $mform->addRule('location', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'address', get_string('address', 'format_classroom'), 'placeholder="Enter Address"');
        $mform->addHelpButton('location', 'location', 'format_classroom');
        $mform->setType('address', PARAM_RAW);
        $mform->addHelpButton('address', 'address', 'format_classroom');
        $mform->addRule('address', get_string('required'), 'required', null, 'client');
        $mform->addElement('text', 'phoneno', get_string('phoneno', 'format_classroom'), 'placeholder="Enter Phone Number"');
        $mform->setType('phoneno', PARAM_RAW);
        $mform->addHelpButton('phoneno', 'phoneno', 'format_classroom');
        $mform->addRule('phoneno', get_string('number_required', 'format_classroom'), 'numeric', null, 'client');
        $mform->addElement('text', 'emailid', get_string('emailid', 'format_classroom'), 'placeholder="Enter Email ID"');
        $mform->addHelpButton('emailid', 'emailid', 'format_classroom');
        $mform->addRule('emailid', get_string('emailvalidation', 'format_classroom'), 'email', null, 'client');
        $mform->setType('emailid', PARAM_RAW);

        $mform->addElement('html', '<div id="map"></div>');
        $this->add_action_buttons(true, 'Submit');
    }

    /**
     * Custom validation should be added here.
     *
     * @return void
     */
    public function validation($data, $files) {
        global $DB;
        $err = array();
        if ($data['emailid']) {
            if (!validate_email($data['emailid'])) {
                $err['emailid'] = get_string('invalidemail');
            }
        }
        if (empty(trim($data['location']))) {
            $err['location'] = get_string('required');
        }
        $results = $DB->get_records_sql("SELECT * FROM {format_classroom_location} WHERE isdeleted != 0 AND location=? AND id != ?",
            array($data['location'], $data['cid']));
        if (!empty($results)) {
            $err['location'] = get_string('duplicatelocation', 'format_classroom');
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