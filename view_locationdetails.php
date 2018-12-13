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
 * View location details.
 *
 * @package   format_classroom
 * @copyright 2018 eNyota Learning Pvt Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
global $CFG, $USER, $DB, $PAGE, $COURSE;
$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_url('/../../../manage_location.php');
$PAGE->set_title(get_string('manage_location', 'format_classroom'));
$PAGE->set_heading(get_string('manage_location', 'format_classroom'));
$PAGE->set_pagelayout('course');
$PAGE->navbar->add('Site administration', new moodle_url('/admin/search.php'));
$PAGE->navbar->add('Plugins', new moodle_url('/admin/category.php?category=modules'));
$PAGE->navbar->add('Classroom format', new moodle_url('/admin/settings.php?section=formatsettingclassroom'));
$PAGE->navbar->add('Manage Location');
$cid = optional_param('cid', 0, PARAM_INT);
require_login();

// If you are not user of editing course.
if (!$PAGE->user_is_editing()) {
    redirect($CFG->wwwroot);
}

$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/course/format/classroom/viewdetails.css'));
echo $OUTPUT->header();

// Get all records for locaiton.
$getrecords = $DB->get_record('format_classroom_location', array('id' => $cid));

$out = '';
$out .= html_writer::start_tag('div', array('class' => 'box generalbox modal modal-dialog modal-in-page show p-y-1'));
$out .= html_writer::start_tag('div', array('class' => 'box modal-content p-y-1', 'id' => 'modal-content'));

$out .= html_writer::tag('div', "<h4> Session $getrecords->location </h4>",
    array('class' => 'box modal-header p-x-1 p-y-1', 'id' => 'modal-header'));

$out .= html_writer::tag('div', "<br/><div> <sapn><b>Address :</b></span> <sapn>$getrecords->address</span></div>
	<div> <sapn><b>Contact No :</b></span> <sapn>$getrecords->phoneno</span></div>
	<div> <sapn><b>E-mail Address :</b></span> <sapn>$getrecords->emailid</span></div><br/>",
    array('class' => 'box modal-body p-x-1 p-y-1', 'id' => 'modal-body'));
$out .= html_writer::tag('div', '<a href="manage_location.php" class="btn btn-secondary"> Close </a>',
    array('class' => 'box modal-footer p-x-1 p-y-1', 'id' => 'modal-footer'));
echo $out;
echo $OUTPUT->footer();