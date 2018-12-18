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
 * All Delect/Unassign user from session.
 *
 * @package format_classroom
 * @copyright 2018 eNyota Learning Pvt Ltd.
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
$delete = optional_param('delete', '', PARAM_ALPHANUM); // Location name hash to confirm.
$token = optional_param('token', 0, PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$sessionid = required_param('session_id', PARAM_INT);
if ($token == 0) {
    $id = required_param('id', PARAM_INT);
    $getassignusers = $DB->get_record('classroom_assignuser', array('id' => $id));
}
global $OUTPUT, $PAGE, $DB, $COURSE;
$getsession = $DB->get_record('classroom_session', array('id' => $sessionid));
require_login();
$PAGE->set_url('/course/format/classroom/delete_assigneduser.php',
    array('session_id' => $sessionid, 'courseid' => $courseid, 'token' => 1));
$course = get_course($courseid);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_title(get_string('unassign_users', 'format_classroom'));
$PAGE->set_heading(get_string('unassign_users', 'format_classroom'));
$PAGE->set_pagelayout('course');
$PAGE->navbar->add(get_string('courses'), new moodle_url('/course/index.php'));
$PAGE->navbar->add($course->fullname, new moodle_url('/course/view.php?id='.$course->id));
$urlsessionlist = '/course/view.php?id='.$course->id.'&editmenumode=true&menuaction=sessionlist&token=1';
$PAGE->navbar->add(get_string('sessionlist', 'format_classroom'), new moodle_url($urlsessionlist));
$urlassginuser = '/course/view.php?id='.$course->id.'&editmenumode=true&menuaction=assginusertosession&token=1';
$PAGE->navbar->add(get_string('assginuser', 'format_classroom'), new moodle_url($urlassginuser));
$categoryurl = new moodle_url('/course/view.php?id='.$courseid.'&editmenumode=true&menuaction=assginusertosession&token=1',
    array());
if ($delete === md5($getsession->session)) {
    if ($token == 0) {
        $success = $DB->delete_records('classroom_assignuser', array('id' => $id));
    } else {
        $getallassignusers = $DB->get_records('classroom_assignuser', array('session_id' => $sessionid));
        // Unassign Mail.
        foreach ($getallassignusers as $key => $userids) {
            $userto = $DB->get_record('user', array('id' => $userids->userid));
            $getsessiondetails  = $DB->get_record('classroom_session', array('id' => $sessionid));
            $messagehtml = "Dear $userto->firstname,<br/><br/>
                You have unassign for session $getsessiondetails->session<br/><br/>
                Regards,<br/>
                $SITE->fullname.
            ";
            $success = $DB->delete_records('classroom_assignuser', array('session_id' => $sessionid));
            email_to_user($userto, $USER, 'Unassign the session', 'Unassign the session for you', $messagehtml);
        }
    }
    if ($success) {
        redirect($categoryurl, 'Successfully unassigned all users.', null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

$message = get_string('unassignusersall', 'format_classroom', $getsession->session);
$continueurl = new moodle_url('/course/format/classroom/delete_assigneduser.php',
    array('cid' => $id, 'session_id' => $sessionid, 'courseid' => $courseid, 'token' => 1, 'delete' => md5($getsession->session)));
$continuebutton = new single_button($continueurl, get_string('delete'), 'post');
$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();
echo $OUTPUT->confirm($message, $continuebutton, $categoryurl);
echo $OUTPUT->footer();
exit;
