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

require_once('../../../config.php');
require_once(dirname(__FILE__).'/edit_location_form.php');
global $CFG, $USER, $DB, $PAGE, $COURSE;
$cid = required_param('cid', PARAM_INT);
$context = context_system::instance();
$PAGE->requires->css( new moodle_url($CFG->wwwroot . '/course/format/classroom/css/style.css'));
$PAGE->set_context($context);
$PAGE->set_url('/course/format/classroom/edit_location.php?cid='.$cid, array());
$PAGE->set_title(get_string('update_location', 'format_classroom'));
$PAGE->set_heading(get_string('update_location', 'format_classroom'));
$PAGE->set_pagelayout('course');
$PAGE->navbar->add('Site administration', new moodle_url('/admin/search.php'));
$PAGE->navbar->add('Plugins', new moodle_url('/admin/category.php?category=modules'));
$PAGE->navbar->add('Course formats', new moodle_url('/admin/category.php?category=formatsettings'));
$PAGE->navbar->add('Classroom format', new moodle_url('/admin/settings.php?section=formatsettingclassroom'));
$PAGE->navbar->add('Manage Location', new moodle_url('/course/format/classroom/manage_location.php'));
$PAGE->navbar->add('Update Location');

require_login();
if ($userdata = $DB->get_record('classroom_location', array('id' => $cid))) {
    $cid = $userdata->id;
    $location = $userdata->location;
    $address = $userdata->address;
    $phoneno = $userdata->phoneno;
    $emailid = $userdata->emailid;
}

$args = array();
$args = array('id' => $cid, 'location' => $location,
'address' => $address,
'phoneno' => $phoneno,
'emailid' => $emailid
);
$mform = new location_edit_form(null, $args);
$mform->set_data($args);
if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . "/course/format/classroom/manage_location.php");
} else if ($fromform = $mform->get_data()) {
    $studentrecord = new stdClass();
    $studentrecord->id  = $cid;
    $studentrecord->location  = (isset($fromform->location)) ? $fromform->location : '';
    $studentrecord->address  = (isset($fromform->address)) ? $fromform->address : '';
    $studentrecord->phoneno  = (isset($fromform->phoneno)) ? $fromform->phoneno : '';
    $studentrecord->emailid  = (isset($fromform->emailid)) ? $fromform->emailid : '';
    $updateid = $DB->update_record('classroom_location', $studentrecord);
    $redirecturl = $CFG->wwwroot.'/course/format/classroom/manage_location.php';
    redirect($redirecturl, 'Location update successfully', null, \core\output\notification::NOTIFY_SUCCESS);
}
$PAGE->set_title(get_string('update_location', 'format_classroom'));
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
