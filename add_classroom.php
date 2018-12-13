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
 * classroom course format. Display the whole course as "classroom" made of modules.
 *
 * @package format_classroom
 * @copyright 2018 eNyota Learing Pvt Ltd
 * @author eNyota Learing Pvt Ltd
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once(dirname(__FILE__).'/add_classroom_form.php');

global $CFG, $DB, $PAGE;
$PAGE->requires->css( new moodle_url($CFG->wwwroot . '/course/format/classroom/css/style.css'));
$locationid  = optional_param('location_id', 0, PARAM_INT);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/../../../add_classroom.php?location_id='.$locationid);
$PAGE->set_title(get_string('addclassroom', 'format_classroom'));
$PAGE->set_heading(get_string('addclassroom', 'format_classroom'));
$PAGE->set_pagelayout('course');
$PAGE->navbar->add('Site administration', new moodle_url('/admin/search.php'));
$PAGE->navbar->add('Plugins', new moodle_url('/admin/category.php?category=modules'));
$PAGE->navbar->add('Course formats', new moodle_url('/admin/category.php?category=formatsettings'));
$PAGE->navbar->add('Classroom format', new moodle_url('/admin/settings.php?section=formatsettingclassroom'));
$PAGE->navbar->add('Manage Location', new moodle_url('/course/format/classroom/manage_location.php'));
$PAGE->navbar->add('Manage Classroom', new moodle_url('/course/format/classroom/manage_classroom.php?location_id='.$locationid));
$PAGE->navbar->add('Add Classroom');

$templatedata = new stdClass();
$args = array('location_id' => $locationid);

require_login();
$mform = new addclassroom_form(null, $args);
$mform->set_data($templatedata);

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . "/course/format/classroom/manage_classroom.php?location_id=".$locationid);
} else if ($fromform = $mform->get_data()) {
    $classroom = new stdClass();
    $classroom->classroom = (isset($fromform->classroom)) ? $fromform->classroom : '';
    $classroom->location_id = (isset($fromform->location_id)) ? $fromform->location_id : '';
    $classroom->details = (isset($fromform->details)) ? $fromform->details : '';
    $classroom->emailid = (isset($fromform->emailid)) ? $fromform->emailid : '';
    $classroom->phoneno = (isset($fromform->phoneno)) ? $fromform->phoneno : '';
    $classroom->seats = (isset($fromform->seats)) ? $fromform->seats : '';
    $classroom->equipment = (isset($fromform->equipment)) ? $fromform->equipment : '';
    $classroom->isdeleted = 1;
    $insertedid = $DB->insert_record('format_classroom', $classroom);
    if ($insertedid > 0) {
        $redirecturl = $CFG->wwwroot.'/course/format/classroom/manage_classroom.php?location_id='.$locationid;
        redirect($redirecturl, 'Classroom added successfully', null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        echo 'Failed to insert record';
    }
}
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();