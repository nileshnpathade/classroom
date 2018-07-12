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

global $CFG, $USER, $DB, $PAGE, $COURSE;
$context = context_system::instance();
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$sesseid = required_param('sess_id',  PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
require_login();
$PAGE->set_context($context);
$PAGE->set_url('/course/format/classroom/attendance.php?courseid='.$courseid.'&sess_id='.$sesseid.'&token=1', array());
$PAGE->set_title(get_string('update_location', 'format_classroom'));
$PAGE->set_heading(get_string('update_location', 'format_classroom'));
$PAGE->set_pagelayout('course');

if (isset($_POST['submit'])) {
    $sessid = isset($_POST['session_name']) ? $_POST['session_name'] : 0;
    $checksession = $DB->get_records('classroom_attendance', array('sessionid' => $sessid));
    if (empty($checksession)) {
        if (!empty($_POST['session_name'])) {
            $users = isset($_POST['userid']) ? $_POST['userid'] : '0';
            for ($v = 1; $v <= count($users); $v++) {
                $userid = isset($_POST['userid'][$v]) ? $_POST['userid'][$v] : '0';
                $status = isset($_POST['status'][$v]) ? $_POST['status'][$v] : 'A';
                $comment = isset($_POST['comment'][$v]) ? $_POST['comment'][$v] : '';
                $attendance = 'A';
                if ($status == 'P') {
                    $attendance = 'P';
                }
                $classroomattendance = new stdClass();
                $classroomattendance->userid = $userid;
                $classroomattendance->attendance = $attendance;
                $classroomattendance->sessionid = $_POST['session_name'];
                $classroomattendance->courseid = $courseid;
                $classroomattendance->comment = $comment;
                $insertedid = $DB->insert_record('classroom_attendance', $classroomattendance);

            }
            if (isset($insertedid)) {
                $redirecturl = 'course/view.php?id='.$courseid.'&editmenumode=true&menuaction=assginusertosession&token=1';
                redirect($CFG->wwwroot.'/'.$redirecturl, 'You have done attendance',
                    null, \core\output\notification::NOTIFY_SUCCESS);
            }
        } else {
            $redirecturl = 'course/view.php?id='.$courseid.'&editmenumode=true&menuaction=assginusertosession&token=1';
            redirect($CFG->wwwroot.'/'.$redirecturl,
            'Select Session', null, \core\output\notification::NOTIFY_ERROR);
        }
    } else {
        if (!empty($_POST['session_name'])) {
            $users = isset($_POST['userid']) ? $_POST['userid'] : '0';
            $v = 1;
            foreach ($_POST['userid'] as $key => $value) {
                $getattendanceid = $DB->get_record('classroom_attendance',
                array('sessionid' => $sessid, 'userid' => $value));
                $userid = isset($_POST['userid'][$v]) ? $_POST['userid'][$v] : '0';
                $status = isset($_POST['status'][$v]) ? $_POST['status'][$v] : 'A';
                $comment = isset($_POST['comment'][$v]) ? $_POST['comment'][$v] : '';
                $attendance = 'A';
                if ($status == 'P') {
                    $attendance = 'P';
                }
                if (isset($getattendanceid->id)) {
                    $classroomattendanceupdate = new stdClass();
                    $classroomattendanceupdate->id = $getattendanceid->id;
                    $classroomattendanceupdate->userid = $userid;
                    $classroomattendanceupdate->attendance = $attendance;
                    $classroomattendanceupdate->sessionid = $_POST['session_name'];
                    $classroomattendanceupdate->courseid = $courseid;
                    $classroomattendanceupdate->comment = $comment;
                    $updateid = $DB->update_record('classroom_attendance', $classroomattendanceupdate);
                } else {
                    $classroomattendance2 = new stdClass();
                    $classroomattendance2->userid = $userid;
                    $classroomattendance2->attendance = $attendance;
                    $classroomattendance2->sessionid = $_POST['session_name'];
                    $classroomattendance2->courseid = $courseid;
                    $classroomattendance2->comment = $comment;
                    $insertedid = $DB->insert_record('classroom_attendance', $classroomattendance2);
                }
                $v++;
            }
            $fredirect = $CFG->wwwroot.'/course/view.php?id='.$courseid.'&editmenumode=true&menuaction=assginusertosession&token=1';
            redirect($fredirect, 'You have update attendance',
                null, \core\output\notification::NOTIFY_SUCCESS);
        }
    }
}