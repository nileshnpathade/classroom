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
 * Editing Classroom form.
 *
 * @package   format_classroom
 * @copyright 2018 eNyota Learning Pvt Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once('../../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/filelib.php');
require_login();

/**
 * Editing/Modifying Classroom details form.
 *
 * @package   format_classroom
 * @copyright 2018 eNyota Learning Pvt Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class classroom_edit_form extends moodleform {
    /**
     * Modify/Update classrooom form.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;
        $cid = $this->_customdata['id'];
        $locationid = $this->_customdata['location_id'];
        $mform->addElement('hidden', 'cid', $cid);
        $mform->setType('cid', PARAM_INT);

        $mform->addElement('hidden', 'location_id', $locationid);
        $mform->setType('location_id', PARAM_INT);

        $mform->addElement('header', 'update_classroom', get_string('update_classroom', 'format_classroom'));

        // Classroom name text box.
        $mform->addElement('text', 'classroom', get_string('classroom', 'format_classroom'));
        $mform->setType('classroom', PARAM_RAW);
        $mform->addHelpButton('classroom', 'classroom', 'format_classroom');
        $mform->addRule('classroom', get_string('required'), 'required', null, 'client');

        // Classroom email id to contact to classroom.
        $mform->addElement('text', 'emailid', get_string('emailid', 'format_classroom'));
        $mform->setType('emailid', PARAM_RAW);
        $mform->addRule('emailid', get_string('emailvalidation', 'format_classroom'), 'email', null, 'client');
        $mform->addHelpButton('emailid', 'emailid', 'format_classroom');

        // Phone number for contacting to classroom.
        $mform->addElement('text', 'phoneno', get_string('phoneno', 'format_classroom'));
        $mform->setType('phoneno', PARAM_RAW);
        $mform->addHelpButton('phoneno', 'phoneno', 'format_classroom');
        $mform->addRule('phoneno', get_string('number_required', 'format_classroom'), 'numeric', null, 'client');

        // Seats is showing capacity of classroom.
        $mform->addElement('text', 'seats', get_string('seats', 'format_classroom'));
        $mform->setType('seats', PARAM_RAW);
        $mform->addHelpButton('seats', 'seats', 'format_classroom');
        $mform->addRule('seats', get_string('required'), 'required', null, 'client');
        $mform->addRule('seats', get_string('number_required', 'format_classroom'), 'numeric', null, 'client');
        $mform->addRule('seats', get_string('negativenumber', 'format_classroom'), 'regex', '/^[1-9]\d*$/', 'client');

        // Classroom other details.
        $mform->addElement('textarea', 'details', get_string("details", "format_classroom"),
            'rows="5" cols="19" maxlength="5000"');
        $mform->setType('details', PARAM_RAW);
        $mform->addHelpButton('details', 'details', 'format_classroom');

        // Classroom with equipment or not.
        $mform->addElement('textarea', 'equipment', get_string("equipment", "format_classroom"),
            'rows="5" cols="19" maxlength="5000"');
        $mform->setType('equipment', PARAM_RAW);
        $mform->addHelpButton('equipment', 'equipment', 'format_classroom');

        // Submit form.
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
        if ($data['seats'] <= 0) {
            $err['seats'] = get_string('zeroseats', 'format_classroom');
        }
        if ($data['emailid']) {
            if (!validate_email($data['emailid'])) {
                $err['emailid'] = get_string('invalidemail');
            }
        }
        $classroom = $data['classroom'];
        $locationid = $data['location_id'];
        $cid = $data['cid'];
        $sql = 'SELECT * FROM {format_classroom} WHERE classroom = ? AND id != ? AND isdeleted = 1 AND location_id = ?';
        $getclassroom = $DB->get_records_sql($sql, array($classroom, $cid, $locationid));
        if (!empty($getclassroom)) {
            $err['classroom'] = get_string('duplicateclassroom', 'format_classroom');
        }
        if (count($err) == 0) {
            return true;
        } else {
            return $err;
        }
    }
}