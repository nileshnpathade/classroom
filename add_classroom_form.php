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
 * @since 3.4.2
 * @package format_classroom
 * @copyright eNyota Learning Pvt Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once('../../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/filelib.php');
require_login();

/**
 * Adding Classroom form.
 *
 * @package   format_classroom
 * @copyright 2018 eNyota Learning Pvt Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class addclassroom_form extends moodleform {
    /**
     * Add classrooom form with definition.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;
        $locationid = $this->_customdata['location_id'];
        $mform->addElement('hidden', 'cid');
        $mform->setType('cid', PARAM_INT);
        $mform->addElement('hidden', 'location_id', $locationid);
        $mform->setType('location_id', PARAM_INT);
        $mform->addElement('header', 'addclassroom', get_string('addclassroom', 'format_classroom'));
        $mform->addElement('text', 'classroom', get_string('classroom', 'format_classroom'));
        $mform->setType('classroom', PARAM_RAW);
        $mform->addHelpButton('classroom', 'classroom', 'format_classroom');
        $mform->addRule('classroom', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'emailid', get_string('emailid', 'format_classroom'));
        $mform->addRule('emailid', get_string('emailvalidation', 'format_classroom'), 'email', null, 'client');
        $mform->addHelpButton('emailid', 'emailid', 'format_classroom');
        $mform->setType('emailid', PARAM_RAW);

        $mform->addElement('text', 'phoneno', get_string('phoneno', 'format_classroom'));
        $mform->addHelpButton('phoneno', 'phoneno', 'format_classroom');
        $mform->addRule('phoneno', get_string('number_required', 'format_classroom'), 'numeric', null, 'client');
        $mform->setType('phoneno', PARAM_RAW);

        $mform->addElement('text', 'seats', get_string('seats', 'format_classroom'));
        $mform->setType('seats', PARAM_RAW);
        $mform->addHelpButton('seats', 'seats', 'format_classroom');
        $mform->addRule('seats', get_string('number_required', 'format_classroom'), 'numeric', null, 'client');
        $mform->addRule('seats', get_string('required'), 'required', null, 'client');
        $mform->addRule('seats', get_string('negativenumber', 'format_classroom'), 'regex', '/^[1-9]\d*$/', 'client');

        $mform->addElement('textarea', 'details', get_string("details", "format_classroom"),
            'rows="5" cols="19" maxlength="5000"');
        $mform->addHelpButton('details', 'details', 'format_classroom');
        $mform->setType('details', PARAM_RAW);

        $mform->addElement('textarea', 'equipment', get_string("equipment", "format_classroom"),
            'rows="5" cols="19" maxlength="5000"');
        $mform->addHelpButton('equipment', 'equipment', 'format_classroom');
        $mform->setType('equipment', PARAM_RAW);
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
        // Seats should be greater than zero.
        if ($data['seats'] <= 0) {
            $err['seats'] = get_string('zeroseats', 'format_classroom');
        }
        // Email Validation.
        if ($data['emailid']) {
            if (!validate_email($data['emailid'])) {
                $err['emailid'] = get_string('invalidemail');
            }
        }
        // Check classroom is duplicate or not.
        $classroom = $data['classroom'];
        $locationid = $data['location_id'];
        $getclassroom = $DB->get_record('classroom', array('classroom' => $classroom,
            'isdeleted' => 1, 'location_id' => $locationid));
        if ( !empty($getclassroom) ) {
            $err['classroom'] = get_string('duplicateclassroom', 'format_classroom');
        }

        if (count($err) == 0) {
            return true;
        } else {
            return $err;
        }
    }
}