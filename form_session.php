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
 * Adding Session form.
 *
 * @since 3.4.2
 * @package format_classroom
 * @copyright eNyota Learning Pvt Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Class for config/adding Session form.
 *
 * @package   format_classroom
 * @copyright 2018 eNyota Learning Pvt Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class config_session_form extends moodleform {
    /**
     * Add session form definition.
     *
     * @return void
     */
    public function definition() {
        global $USER, $CFG, $COURSE, $DB, $PAGE;
        $mform =& $this->_form;
        $PAGE->requires->css( new moodle_url($CFG->wwwroot . '/course/format/classroom/css/style.css'));
        $PAGE->requires->js( new moodle_url($CFG->wwwroot . '/course/format/classroom/myjavascript.js'));
        $courseid = $this->_customdata['courseid'];
        $mform->addElement('header', 'addsession', get_string('addsession', 'format_classroom'));
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('text', 'session', get_string('session', 'format_classroom'));
        $mform->setType('session', PARAM_RAW);
        $mform->addRule('session', get_string('required'), 'required', null, 'client');

        $option = array(
            'startyear' => date('Y'),
            'stopyear'  => 2090,
            'timezone'  => 99,
            'step'      => 5
        );

        // Start Date Time.location.
        $mform->addElement('date_time_selector', 'session_date', get_string('sessiondatetime', 'format_classroom') , $option);

        // End Date Time.
        $mform->addElement('date_time_selector', 'session_date_end', get_string('sessiondatetime_end',
            'format_classroom'), $option);

        $classrooms = $DB->get_records_sql('select id,location from {classroom_location}
            where isdeleted != ?', array(0));
        $key = array(null => 'Select Location');
        foreach ($classrooms as $classr) {
            $key[$classr->id] = $classr->location;
        }

        $attributes = array();
        $mform->addElement('selectwithlink', 'location', get_string('location',
            'format_classroom'), $key, array('onchange' =>
            'javascript:get_states("'.$CFG->wwwroot.'", this.value,this.id);'),
            array('link' => $CFG->wwwroot.'/course/format/classroom/manage_location.php?cid='.$COURSE->id,
                'target' => '_blank', 'label' => get_string('addlocation', 'format_classroom')));

        $mform->addRule('location', get_string('required'), 'required', null, 'client');

        $classroomlist = array(null => 'Select classroom');
        $mform->addElement('select', 'classroom', get_string('classroom', 'format_classroom') , $classroomlist);
        $mform->setType('classroom', PARAM_RAW);
        $mform->addRule('classroom', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'maxenrol', get_string('maxenrol', 'format_classroom'));
        $mform->setType('maxenrol', PARAM_RAW);
        $mform->addRule('maxenrol', get_string('number_required', 'format_classroom'), 'numeric', null, 'client');
        $mform->addRule('maxenrol', get_string('required'), 'required', null, 'client');

        $mform->addElement('date_time_selector', 'last_subscription_date',
            get_string('lastsubscriptiondate', 'format_classroom') , $option);
        $mform->addRule('last_subscription_date', get_string('required'), 'required', null, 'client');

        $mform->addElement('textarea', 'other_details', get_string("otherdetails", "format_classroom"),
            'rows = "5" cols = "35" maxlength="5000"');
        $mform->addRule('other_details', get_string('required'), 'required', null, 'client');
        $mform->setType('other_details', PARAM_RAW);
        $mform->addElement('html', '<div class="form-group row fitem">
            <div class="col-md-9 charpostion">5000 Character</div></div>');
        $this->add_action_buttons(true);
    }

    /**
     * Add session form definition after data set default classroom value.
     *
     * @return void
     */
    public function definition_after_data() {
        global $DB, $CFG;
        $mform = $this->_form;
        $classroom = $mform->getElementValue('classroom');
        $mform->setDefault('classroom', array('value' => $classroom));
    }

    /**
     * Custom validation should be added here.
     *
     * @return void
     */
    public function validation($data, $files) {
        global $CFG, $DB;
        $errors = array();
        $startday = $data['session_date'];
        $endday = $data['session_date_end'];
        $maxenrol = $data['maxenrol'];
        $getcoursedetails = $DB->get_record('course', array('id' => $data['courseid']));

        $seesionstartdate = $data['session_date'];
        $seesionenddate = $data['session_date_end'];
        $coursestartdate = $getcoursedetails->startdate;
        $courseenddate = $getcoursedetails->enddate + 24 * 60 * 59.9;
        $errors['session_date_end'] = '';
        $errors['session_date'] = '';

        // Duplicate session name.
        $sessionname = trim($data['session']);
        $resultsession = $DB->get_records('classroom_session', array('session' => $sessionname, 'courseid' => $data['courseid']));
        if (!empty($resultsession)) {
            $errors['session'] = get_string('duplicatesessionname', 'format_classroom');
            $errors['classroom'] = get_string('reselectlocationandclassroom', 'format_classroom');
        }

        // Session start date must be greater than current time.
        if ($data['session_date'] < time()) {
            $errors['session_date'] = get_string('invalidsessiondatecurrent', 'format_classroom');
            $errors['classroom'] = get_string('reselectlocationandclassroom', 'format_classroom');
        }

        // Session end date must be greater than current time.
        if ($data['session_date_end'] < time()) {
            $errors['session_date_end'] = get_string('invalidsessiondateenddate', 'format_classroom');
            $errors['classroom'] = get_string('reselectlocationandclassroom', 'format_classroom');
        }

        // Session start date must be bigger than end date.
        if ($startday >= $endday) {
            $errors['session_date_end'] .= get_string('invalidsessiondateenddaterange', 'format_classroom');
            $errors['classroom'] = get_string('reselectlocationandclassroom', 'format_classroom');
        }

        $er = explode('<br/>', $errors['session_date_end']);
        if (count($er) >= 2) {
            $errors['session_date_end'] = get_string('invalidsessiondateenddate', 'format_classroom');
        }

        $er = explode('<br/>', $errors['session_date']);
        if (count($er) >= 2) {
            $errors['session_date'] = get_string('invalidsessiondatecurrent', 'format_classroom');
        }

        // Session start date must be grather than course state date.
        if ( $seesionenddate > $courseenddate ) {
            $errors['session_date_end'] = get_string('invalidsessiondateenddate', 'format_classroom');
            $errors['classroom'] = get_string('reselectlocationandclassroom', 'format_classroom');
        }

        // Session start date less.
        if ( $seesionstartdate < $coursestartdate ) {
            $errors['session_date'] = get_string('sessiondatenotavailable', 'format_classroom');
            $errors['classroom'] = get_string('reselectlocationandclassroom', 'format_classroom');
        }

        // Last subscription date should be less than current date time.
        if ($data['last_subscription_date'] < time()) {
            $errors['last_subscription_date'] = get_string('lastsubscriptiontime', 'format_classroom');
            $errors['classroom'] = get_string('reselectlocationandclassroom', 'format_classroom');
        }

        // Last subscription date should be grather than session start date.
        if ($startday < $data['last_subscription_date']) {
            $errors['last_subscription_date'] = get_string('lastsubscriptiondatelessthenstart', 'format_classroom');
            $errors['classroom'] = get_string('reselectlocationandclassroom', 'format_classroom');
        }

        $result = $DB->get_records('classroom_session', array('isdeleted' => '1',
            'location' => $data['location'], 'classroom' => $_POST['classroom']));

        foreach ($result as $key => $value) {
            if (!((($value->session_date > $startday)
                AND ($value->session_date > $endday))
                OR ( ($value->session_date_end < $startday)
                AND ($value->session_date_end < $endday) ))) {
                $errors['classroom'] = get_string('sessionenddate', 'format_classroom');
            }
        }

        if (empty($_POST['classroom'])) {
            $errors['classroom'] = get_string('reselectlocationandclassroom', 'format_classroom');
        } else {
            // Validation for maxenrol.
            $getmaxenrol = $DB->get_record('classroom', array('id' => $_POST['classroom']));
            if ($maxenrol > $getmaxenrol->seats) {
                $errors['maxenrol'] = get_string('maxenrolmorethanseats', 'format_classroom');
                $errors['classroom'] = get_string('reselectlocationandclassroom', 'format_classroom');
            }
        }
        if (count($errors) == 2) {
            return true;
        } else {
            return $errors;
        }
    }
}
