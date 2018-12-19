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
        global $CFG, $DB, $PAGE;
        $mform =& $this->_form;
        $PAGE->requires->css( new moodle_url($CFG->wwwroot . '/course/format/classroom/css/style.css'));
        $PAGE->requires->js( new moodle_url($CFG->wwwroot . '/course/format/classroom/myjavascript.js'));
        $courseid = $this->_customdata['courseid'];
        $mform->addElement('header', 'addsession', get_string('addsession', 'format_classroom'));
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('text', 'session', get_string('session', 'format_classroom'));
        $mform->setType('session', PARAM_RAW);
        $mform->addHelpButton('session', 'session', 'format_classroom');
        $mform->addRule('session', get_string('required'), 'required', null, 'client');

        $option = array(
            'startyear' => date('Y'),
            'stopyear'  => 2090,
            'timezone'  => 99,
            'step'      => 5
        );

        $mform->addElement('date_time_selector', 'last_subscription_date_from',
            get_string('lastsubscriptiondatefrom', 'format_classroom') , $option);
        $mform->addHelpButton('last_subscription_date_from', 'lastsubscriptiondatefrom', 'format_classroom');

        $mform->addElement('date_time_selector', 'last_subscription_date',
            get_string('lastsubscriptiondateto', 'format_classroom') , $option);
        $mform->addHelpButton('last_subscription_date', 'lastsubscriptiondateto', 'format_classroom');

        // Start Date Time.location.
        $mform->addElement('date_time_selector', 'session_date', get_string('sessiondatetime', 'format_classroom') , $option);
        $mform->addHelpButton('session_date', 'sessiondatetime', 'format_classroom');

        // End Date Time.
        $mform->addElement('date_time_selector', 'session_date_end', get_string('sessiondatetime_end',
            'format_classroom'), $option);
        $mform->addHelpButton('session_date_end', 'sessiondatetime_end', 'format_classroom');

        $classrooms = $DB->get_records_sql('select id,location from {format_classroom_location}
            where isdeleted != ?', array(0));
        $key = array(null => 'Select Location');
        foreach ($classrooms as $classr) {
            $key[$classr->id] = $classr->location;
        }

        $mform->addElement('selectwithlink', 'location', get_string('location',
            'format_classroom'), $key, array('onchange' =>
            'javascript:get_states("'.$CFG->wwwroot.'", this.value,this.id);'),
            array('link' => $CFG->wwwroot.'/course/format/classroom/manage_location.php?cid='.$COURSE->id,
                'target' => '_blank', 'label' => get_string('addlocation', 'format_classroom')));

        $mform->addRule('location', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('location', 'location', 'format_classroom');
        $classroomlist = array(null => 'Select classroom');
        $mform->addElement('select', 'classroom', get_string('classroom', 'format_classroom') , $classroomlist);
        $mform->setType('classroom', PARAM_RAW);
        $mform->addRule('classroom', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('classroom', 'classroom', 'format_classroom');
        $roles = $DB->get_records_sql('select * from {role} where (shortname = ? OR shortname = ?)',
            array('editingteacher', 'teacher'));
        $contextid = context_course::instance($courseid);
        $arrayteachername = array();
        $arrayteacherid = array();

        foreach ($roles as $key => $role) {
            $teachers = get_role_users($role->id, $contextid);
            foreach ($teachers as $key => $teacher) {
                $teachername = $teacher->firstname.' '.$teacher->lastname;
                $teacherid = $teacher->id;
                array_push($arrayteacherid, $teacherid);
                array_push($arrayteachername, $teachername);
            }
        }

        $teacherlistselect = array(null => 'Select Teacher');
        $teacherlists = array_combine($arrayteacherid, $arrayteachername);
        $listofteacher = $teacherlistselect + $teacherlists;
        $mform->addElement('selectwithlink', 'teacher', get_string('selectteacher', 'format_classroom') ,
            $listofteacher, array(), array('link' => $CFG->wwwroot.'/user/index.php?id='.$courseid,
                'label' => 'Assign Teacher Role'));
        $mform->setType('teacher', PARAM_RAW);
        $mform->addHelpButton('teacher', 'selectteacher', 'format_classroom');
        $mform->addRule('teacher', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'maxenrol', get_string('maxenrol', 'format_classroom'));
        $mform->setType('maxenrol', PARAM_RAW);
        $mform->addHelpButton('maxenrol', 'maxenrol', 'format_classroom');
        $mform->addRule('maxenrol', get_string('required'), 'required', null, 'client');
        $mform->addRule('maxenrol', get_string('number_required', 'format_classroom'), 'numeric', null, 'client');
        $mform->addRule('maxenrol', get_string('negativenumber', 'format_classroom'), 'regex', '/^[1-9]\d*$/', 'client');

        $mform->addElement('textarea', 'other_details', get_string("otherdetails", "format_classroom"),
            'rows = "5" cols = "35" maxlength="5000"');
        $mform->addHelpButton('other_details', 'otherdetails', 'format_classroom');
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
        $mform = $this->_form;
        $classroom = $mform->getElementValue('classroom');
        $mform->setDefault('classroom', array('value' => $classroom));
    }

    /**
     * Custom validation should be added here.
     *
     * @return void
     * @param $data submited data.
     * @param $files files input submitted.
     */
    public function validation($data, $files) {
        global $DB;
        $errors = array();
        $startday = $data['session_date'];
        $endday = $data['session_date_end'];
        $maxenrol = $data['maxenrol'];
        $getcoursedetails = $DB->get_record('course', array('id' => $data['courseid']));
        $classroom = optional_param('classroom', '', PARAM_INT);
        $seesionstartdatetime = $data['session_date'];
        $seesionenddate = $data['session_date_end'];
        $coursestartdate = $getcoursedetails->startdate;
        if ( $getcoursedetails->enddate != 0 ) {
            $courseenddate = $getcoursedetails->enddate + 24 * 60 * 59.9;
        } else {
            $courseenddate = 0;
        }

        $errors['session_date_end'] = '';
        $errors['session_date'] = '';

        // Duplicate session name.
        $sessionname = trim($data['session']);
        $resultsession = $DB->get_records('format_classroom_session', array('session' => $sessionname,
            'courseid' => $data['courseid']));
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

        $sqlsessionother = "SELECT * FROM {format_classroom_session}
        WHERE ((session_date BETWEEN '".$data['session_date']."' AND '".$data['session_date_end']."')
        OR (session_date_end BETWEEN '".$data['session_date']."' AND '".$data['session_date_end']."')
        OR (session_date <= '".$data['session_date']."' AND session_date_end >= '".$data['session_date_end']."')) AND teacher = ?";
        $resultsessionothers = $DB->get_records_sql($sqlsessionother, array($data['teacher']));
        if (!empty($resultsessionothers)) {
            $errors['teacher'] = get_string('teacherbooked', 'format_classroom');
            $errors['classroom'] = get_string('reselectlocationandclassroom', 'format_classroom');
        }

        if ( $courseenddate != 0 ) {
            // Session start date must be grather than course state date.
            if ( $seesionenddate > $courseenddate ) {
                $errors['session_date_end'] = get_string('invalidsessiondateenddate', 'format_classroom');
                $errors['classroom'] = get_string('reselectlocationandclassroom', 'format_classroom');
            }
        }

        // Session start date less.
        if ( $seesionstartdatetime < $coursestartdate ) {
            $errors['session_date'] = get_string('sessiondatenotavailable', 'format_classroom');
            $errors['classroom'] = get_string('reselectlocationandclassroom', 'format_classroom');
        }

        // Subscription date from should be less than current date time.
        if ($data['last_subscription_date_from'] < time()) {
            $errors['last_subscription_date_from'] = get_string('lastsubscriptiontimefrom', 'format_classroom');
            $errors['classroom'] = get_string('reselectlocationandclassroom', 'format_classroom');
        }

        // Subscription date from should be less than session start date.
        if ($startday < $data['last_subscription_date_from']) {
            $errors['last_subscription_date_from'] = get_string('lsubdatefrom', 'format_classroom');
            $errors['classroom'] = get_string('reselectlocationandclassroom', 'format_classroom');
        }

        // Subscription date from should be less than current date time.
        if ($data['last_subscription_date'] < time()) {
            $errors['last_subscription_date'] = get_string('lastsubscriptiontime', 'format_classroom');
            $errors['classroom'] = get_string('reselectlocationandclassroom', 'format_classroom');
        }

        // Subscription date from should be grather than Subscription date to.
        if ($data['last_subscription_date'] < $data['last_subscription_date_from']) {
            $errors['last_subscription_date'] = get_string('tosublesssubdate', 'format_classroom');
            $errors['classroom'] = get_string('reselectlocationandclassroom', 'format_classroom');
        }

        // Subscription date to should be less than session end date.
        if ($endday < $data['last_subscription_date']) {
            $errors['last_subscription_date'] = get_string('tosublesssend', 'format_classroom');
            $errors['classroom'] = get_string('reselectlocationandclassroom', 'format_classroom');
        }

        $resultofsession = $DB->get_records('format_classroom_session', array('isdeleted' => '1',
            'location' => $data['location'], 'classroom' => $classroom));

        foreach ($resultofsession as $value) {
            if (!((($value->session_date > $startday)
                AND ($value->session_date > $endday))
                OR ( ($value->session_date_end < $startday)
                AND ($value->session_date_end < $endday) ))) {
                $errors['classroom'] = get_string('sessionenddate', 'format_classroom');
            }
        }

        if (empty($classroom)) {
            $errors['classroom'] = get_string('reselectlocationandclassroom', 'format_classroom');
        } else {
            // Validation for maxenrol.
            $getmaxenrol = $DB->get_record('format_classroom', array('id' => $classroom));
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
