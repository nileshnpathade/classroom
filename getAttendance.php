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
 * List of users and comment with attendance status.
 *
 * @since 3.4.2
 * @package format_classroom
 * @copyright eNyota Learning Pvt Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
global $PAGE, $CFG, $DB, $OUTPUT, $COURSE;

$sessionid  = optional_param('sessionid', 0, PARAM_INT);
$courseid  = optional_param('courseid', 0, PARAM_INT);
require_login();
$out = '';
$table = new html_table();
$table->head = array('Sr.No', 'Username', 'Users Email-ID', 'Attendance', 'Comments');
if ($sessionid == 0) {
    $sql = "SELECT u.id as userid, c.fullname, u.username, u.firstname, u.lastname, u.email
    FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
    WHERE ra.userid = u.id
    AND ra.contextid = cxt.id
    AND cxt.contextlevel = 50
    AND cxt.instanceid = c.id
    AND c.id = '$courseid'
    AND u.id != 1 AND u.id !=2";
} else {
    $sql = "SELECT u.id as userid, c.fullname, u.username, u.firstname, u.lastname, u.email
    FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
    WHERE ra.userid = u.id
    AND ra.contextid = cxt.id
    AND cxt.contextlevel = 50
    AND cxt.instanceid = c.id
    AND c.id = '$courseid'
    AND u.id != 1 AND u.id != 2
    AND roleid = 5 AND roleid = 5 AND  u.id  IN (SELECT ca.userid from {format_classroom_assignuser} as ca
    where ca.session_id = $sessionid)";
}

$getenrolusers = $DB->get_records_sql($sql, array());
$j = 0;
$i = 1;
foreach ($getenrolusers as $re) {
    $userid = $re->userid;
    $username = $re->username;
    $email = $re->email;
    $getattendancsdetails = $DB->get_record('format_classroom_attendance',
    array('sessionid' => $sessionid, 'courseid' => $courseid, 'userid' => $userid));
    $attendance = isset($getattendancsdetails->attendance) ? $getattendancsdetails->attendance : 'A';
    $comment = isset($getattendancsdetails->comment) ? $getattendancsdetails->comment : '';
    $checked = '';
    if ($attendance == 'P') {
        $checked = 'checked';
    }
    $status = "<input type = 'hidden' name = 'userid[$i]' value = '$userid'/>
    <input type = 'checkbox' name = 'status[$i]' $checked />";
    $comment = "<input type = 'textarea' name = 'comment[$i]' value = '$comment'/>";

    if ($j >= 0) {
        $table->data[] = array($i, $username, $email, $status, $comment);
    }
    $j++;
    $i++;
}
$out .= html_writer::table($table);
echo $out;