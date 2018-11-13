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
 * Attendance form.
 * Display attendance status.
 *
 * @package   format_classroom
 * @copyright 2018 eNyota Learning Pvt Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../config.php');
global $CFG, $USER, $DB, $PAGE, $COURSE;

$context = context_system::instance();
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$seesionid = optional_param('seesionid', 0, PARAM_INT);
$sesseid = optional_param('sess_id', 0, PARAM_INT);
require_login();
$start = $page * $perpage;
$out = '';
$acurl = 'course/format/classroom/attendance.php?sess_id='.$sesseid.'&courseid='.$COURSE->id.'&token=1';
$out .= html_writer::start_tag('form', array('name' => 'attendanceform',
    'id' => 'attendanceform', 'action' => $CFG->wwwroot.'/'.$acurl,
    'method' => 'post'));

$getsession = $DB->get_records('classroom_session', array('isdeleted' => 1, 'courseid' => $COURSE->id));
$key = array();

$k = 0;
foreach ($getsession as $classr) {
    $key[$classr->id] = $classr->session;
    $k++;
}

if ($sesseid != 0) {
    $out .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'session_name', 'value' => $sesseid));
}

$out .= html_writer::start_div('sesstion_table', array('class' => 'emailnotification', 'id' => 'session_table'));
$table = new html_table();
$table->head = array('Sr.No', 'Username', 'Users Email-ID', 'Attendance', 'Comments');

if ($sesseid != 0) {
    $sql1 = "SELECT u.id as userid, c.fullname, u.username, u.firstname, u.lastname, u.email
    FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
    WHERE ra.userid = u.id
    AND ra.contextid = cxt.id
    AND cxt.contextlevel = 50
    AND cxt.instanceid = c.id
    AND c.id = '$COURSE->id'
    AND u.id != 1 AND u.id != 2
    AND roleid = 5 AND  u.id  IN (SELECT ca.userid FROM {classroom_assignuser} as ca
    WHERE ca.session_id = $sesseid)";
    $getenrolusers1 = $DB->get_records_sql($sql1, array());

    $sql = "SELECT u.id as userid, c.fullname, u.username, u.firstname, u.lastname, u.email
    FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
    WHERE ra.userid = u.id
    AND ra.contextid = cxt.id
    AND cxt.contextlevel = 50
    AND cxt.instanceid = c.id
    AND c.id = '$COURSE->id'
    AND u.id != 1 AND u.id != 2
    AND roleid = 5 AND  u.id  IN (SELECT ca.userid FROM {classroom_assignuser} as ca
    WHERE ca.session_id = $sesseid) LIMIT $start,$perpage";
    $getenrolusers = $DB->get_records_sql($sql, array());
} else {
    $sql = "SELECT u.id as userid, c.fullname, u.username, u.firstname, u.lastname, u.email
    FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
    WHERE ra.userid = u.id
    AND ra.contextid = cxt.id
    AND cxt.contextlevel = 50
    AND cxt.instanceid = c.id
    AND c.id = '$COURSE->id'
    AND u.id != 1 AND u.id != 2
    AND roleid = 5 LIMIT $start, $perpage";
    $getenrolusers = $DB->get_records_sql($sql, array());
}

$j = 0;$i = 1;
foreach ($getenrolusers as $re) {
    $userid = $re->userid;
    $username = $re->username;
    $email = $re->email;
    if ($sesseid != 0) {
        $getattendancsdetails = $DB->get_record('classroom_attendance',
            array('sessionid' => $sesseid, 'courseid' => $COURSE->id,
            'userid' => $userid));
        $attendance = isset($getattendancsdetails->attendance) ? $getattendancsdetails->attendance : 'A';
        $comments = isset($getattendancsdetails->comment) ? $getattendancsdetails->comment : '';
        $attendanceid = isset($getattendancsdetails->id) ? $getattendancsdetails->id : '';
        $checked = '';
        $defultcheck = '';
        if ($attendance == 'P') {
            $checked = 'checked';
        } else {
            $defultcheck = 'checked';
        }
        $status = "<input type='hidden' name='userid[$i]' value='$userid' />
        <input type='radio' name='status[$i]' value='P' $checked />
        <span style='color:green;font-weight:bold;'>Present</span>
        <br/><input type='radio' name='status[$i]' value='A' $defultcheck /><span style='color:red;font-weight:bold;'> Absent ";
        $comment = "<textarea rows='3' cols='25' name='comment[$i]'>$comments</textarea>";
    } else {
        $status = "<input type='hidden' name='userid[$i]' value='$userid' />
        <input type='radio' name='status[$i]' value='P' $checked /> Present <br/>
        <input type='radio' name='status[$i]' value='A' $defultcheck /> Absent ";
        $comment = "<textarea rows='3' cols='25' name='comment[$i]'> </textarea>";
    }

    if ($j >= 0) {
        $table->data[] = array($i, $username, $email, $status, $comment);
    }
    $j++;
    $i++;
}
$out .= html_writer::table($table);
$out .= html_writer::end_div();

echo $out;
$burl = 'course/view.php?id='.$COURSE->id.'&editmenumode=true&section=0&menuaction=attendance&token=1&sesseid='.$sesseid;
$baseurl = new moodle_url($CFG->wwwroot.'/'.$burl, array('sort' => 'location', 'dir' => 'ASC', 'perpage' => $perpage));
echo $OUTPUT->paging_bar(count($getenrolusers1), $page, $perpage, $baseurl);

$getsessiondate = $DB->get_record('classroom_session', array('id' => $sesseid));
$out1 = '';
echo "<div class='nodata'><b class='nodatatodisplay'>".get_string('nodatatodisplay', 'format_classroom')."</b><br></div>";
if ($j == 0) {
    echo "<div class='nodata1'><b class='nodatatodisplay'>".get_string('nodatatodisplay', 'format_classroom')."</b></div><br>";
}
if ($getsessiondate->session_date <= time()) {
    if ($j != 0) {
        $out1 .= html_writer::empty_tag('input', array('type' => 'submit',
        'class' => 'btn btn-primary', 'name' => 'submit', 'value' => 'Submit'));
    }
}
$out1 .= html_writer::end_tag('form');
echo $out1;