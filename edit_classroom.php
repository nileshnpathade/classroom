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

require_once('../../../config.php');
require_once(dirname(__FILE__).'/edit_classroom_form.php');
global $CFG, $USER, $DB, $PAGE, $COURSE;
$cid = required_param('cid', PARAM_INT);
$locationid = optional_param('location_id', 0, PARAM_INT);
$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_url('/course/format/classroom/edit_classroom.php?cid='.$cid, array());
$PAGE->set_title(get_string('update_location', 'format_classroom'));
$PAGE->set_heading(get_string('update_location', 'format_classroom'));
$PAGE->set_pagelayout('course');
$PAGE->navbar->add('Site administration', new moodle_url('/admin/search.php'));
$PAGE->navbar->add('Plugins', new moodle_url('/admin/category.php?category=modules'));
$PAGE->navbar->add('Course formats', new moodle_url('/admin/category.php?category=formatsettings'));
$PAGE->navbar->add('Configure', new moodle_url('/admin/settings.php?section=formatsettingclassroom'));
$PAGE->navbar->add('Manage Location', new moodle_url('/course/format/classroom/manage_location.php'));
$PAGE->navbar->add('Manage Classroom', new moodle_url('/course/format/classroom/manage_classroom.php?location_id='.$locationid));
$PAGE->navbar->add('Update Classroom');

require_login();

if ($userdata = $DB->get_record('classroom', array('id' => $cid))) {
    $cid = $userdata->id;
    $classroom = $userdata->classroom;
    $details = $userdata->details;
    $seats = $userdata->seats;
    $equipment = $userdata->equipment;
}
$args = array();
$args = array(
    'id' => $cid,
    'classroom' => $classroom,
    'details' => $details,
    'seats' => $seats,
    'equipment' => $equipment,
    'location_id' => $locationid
);

$mform = new classroom_edit_form(null, $args);
$mform->set_data($args);
if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . "/course/format/classroom/manage_classroom.php?location_id=".$locationid);
} else if ($fromform = $mform->get_data()) {
    $studentrecord = new stdClass();
    $studentrecord->id  = $cid;
    $studentrecord->classroom  = (isset($fromform->classroom)) ? $fromform->classroom : '';
    $studentrecord->details  = (isset($fromform->details)) ? $fromform->details : '';
    $studentrecord->seats  = (isset($fromform->seats)) ? $fromform->seats : '';
    $studentrecord->equipment  = (isset($fromform->equipment)) ? $fromform->equipment : '';

    $updateid = $DB->update_record('classroom', $studentrecord);
    $redirecturl = $CFG->wwwroot.'/course/format/classroom/manage_classroom.php?location_id='.$locationid;
    redirect($redirecturl, 'Classroom update successfully', null, \core\output\notification::NOTIFY_SUCCESS);
}
$PAGE->set_title(get_string('update_location', 'format_classroom'));

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();