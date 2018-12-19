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
 * Assign and Unassign users for session.
 *
 * @package    format_classroom
 * @copyright  2018 eNyota Learning Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
$seesionid = required_param('seesionid',  PARAM_INT);
$courseid = required_param('courseid',  PARAM_INT);

global $USER, $DB;

if (!empty($seesionid)) {
    $getsessiondetails  = $DB->get_record('format_classroom_session', array('id' => $seesionid));
}

$context = context_system::instance();
$PAGE->set_context($context);

$course = get_course($courseid);
$PAGE->set_url('/course/format/classroom/adduserforsession.php?seesionid='.$seesionid.'&courseid='.$courseid);
$PAGE->set_title($course->fullname);
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('course');
$PAGE->navbar->add(get_string('courses'), new moodle_url('/course/index.php'));
$PAGE->navbar->add($course->fullname, new moodle_url('/course/view.php?id='.$course->id));
$urlsessionlist = '/course/view.php?id='.$course->id.'&editmenumode=true&menuaction=sessionlist&token=1';
$PAGE->navbar->add(get_string('sessionlist', 'format_classroom'), new moodle_url($urlsessionlist));
$urlassginuser = '/course/view.php?id='.$course->id.'&editmenumode=true&menuaction=assginusertosession&token=1';
$PAGE->navbar->add(get_string('assginuser', 'format_classroom'), new moodle_url($urlassginuser));
require_login();

// If you are not user of editing course.
if (!$PAGE->user_is_editing()) {
    redirect($CFG->wwwroot);
}

if (optional_param('add', false, PARAM_BOOL) and confirm_sesskey()) {
    $addselect = optional_param_array('addselect', null, PARAM_RAW);
    foreach ($addselect as $userid) {
        $checkexits = $DB->get_records('format_classroom_assignuser', array('session_id' => $seesionid, 'userid' => $userid));
        $countofenroll = $DB->get_records('format_classroom_assignuser', array('session_id' => $seesionid));
        if (count($countofenroll) >= $getsessiondetails->maxenrol) {
            $baseurl = '/course/format/classroom/adduserforsession.php';
            $urlredirct = $CFG->wwwroot.$baseurl.'?seesionid='.$seesionid.'&courseid='.$courseid;
            redirect($urlredirct, get_string('maxenrolmorethanseats', 'format_classroom'),
                null, \core\output\notification::NOTIFY_ERROR);
            exit();
        }
        if (empty($checkexits)) {
            $classroomassignuser = new stdClass();
            $classroomassignuser->session_id = $seesionid;
            $classroomassignuser->userid = $userid;
            $classroomassignuser->assign_by = $USER->id;
            $insertedid = $DB->insert_record('format_classroom_assignuser', $classroomassignuser);
            // Assign Mail.
            $userto = $DB->get_record('user', array('id' => $userid));
            $messagehtml = "Dear $userto->firstname,<br/><br/>
                You have assign for session $getsessiondetails->session<br/><br/>
                Regards,<br/>
                $SITE->fullname.
            ";

            email_to_user($userto, $USER, 'Assign the session', 'Assign the session for you', $messagehtml);
        }
    }
} else if (optional_param('remove', false, PARAM_BOOL) and confirm_sesskey()) {
    $removeselect = optional_param('removeselect', 0, PARAM_INT);
    foreach ($removeselect as $userid) {
        $scc = $DB->delete_records('format_classroom_assignuser', array('userid' => $userid, 'session_id' => $seesionid));

        // Unassign Mail.
        $userto = $DB->get_record('user', array('id' => $userid));
        $messagehtml = "Dear $userto->firstname,<br/><br/>
            You have unassign for session $getsessiondetails->session<br/><br/>
            Regards,<br/>
            $SITE->fullname.
        ";
        email_to_user($userto, $USER, 'Unassign the session', 'Unassign the session for you', $messagehtml);

    }
}

echo $OUTPUT->header();
?>
<div id="addadmisform">

    <form id="assignform" method="post" action="<?php echo $PAGE->url ?>">
    <div>
    <input type="hidden" name="sesskey" value="<?php p(sesskey()); ?>" />

        <table class="generaltable generalbox groupmanagementtable boxaligncenter" summary="">
            <tr>
              <td id='existingcell'>
                  <p>
                    <label for="removeselect">
                    <?php echo get_string('assignforsession', 'format_classroom', $getsessiondetails->session);?>
                    </label>
                  </p>
                  <select name="removeselect[]" id="removeselect[]" size="20" class="form-control" multiple="multiple">
                    <?php
                                $sql = "SELECT u.id as userid, c.fullname, u.username, u.firstname, u.lastname, u.email
                                FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
                                WHERE ra.userid = u.id
                                AND ra.contextid = cxt.id
                                AND cxt.contextlevel = 50
                                AND cxt.instanceid = c.id
                                AND c.id = '$courseid'
                                AND u.id != 1 AND u.id != 2
                                AND roleid = 5
                                AND  u.id  IN (SELECT ca.userid FROM {format_classroom_assignuser} as ca
                                WHERE ca.session_id = $seesionid)";
                            $getassinguser = $DB->get_records_sql($sql, array());
                            $assignusers = array('' => 'Assign users');
                    foreach ($getassinguser as $key => $user) { ?>
                                <option value="<?php echo $user->userid; ?>">
                                    <?php echo $user->username." (".$user->email." )"; ?>
                                </option> 
                        <?php
                    }  ?>
                    </select>
                </td>
              <td id="buttonscell">
                <p class="arrow_button">
                    <input name="add" id="add" type="submit" 
                    value="<?php echo $OUTPUT->larrow().'&nbsp;'.get_string('add', 'format_classroom'); ?>" 
                    title="<?php print_string('add', 'format_classroom'); ?>"
                    class="btn btn-secondary"/><br />
                    <input name="remove" id="remove" type="submit"
                    value="<?php echo get_string('remove', 'format_classroom').'&nbsp;'.$OUTPUT->rarrow(); ?>"
                    title="<?php print_string('remove', 'format_classroom'); ?>" class="btn btn-secondary"/><br />
                </p>
              </td>
              <td id="potentialcell">
                  <p>
                    <label for="addselect"><?php print_string('enrolusersforcourse', 'format_classroom'); ?></label>
                  </p>
                    <?php
                    $sql = "SELECT u.id as userid, c.fullname, u.username, u.firstname, u.lastname, u.email
                    FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
                    WHERE ra.userid = u.id
                    AND ra.contextid = cxt.id
                    AND cxt.contextlevel = 50
                    AND cxt.instanceid = c.id
                    AND c.id = '$courseid'
                    AND u.id != 1 AND u.id != 2
                    AND roleid = 5
                    AND  u.id NOT IN (SELECT ca.userid FROM {format_classroom_assignuser} as ca WHERE ca.session_id = $seesionid)";

                    $getassinguser = $DB->get_records_sql($sql, array());
                    $ausers = array('' => 'Unassign users');
                    ?>
                    <select name = "addselect[]" id = "addselect[]" size = "20" class = "form-control" multiple = "multiple">
                        <?php foreach ($getassinguser as $key => $user) {?>
                            <option value="<?php echo $user->userid; ?>">
                                <?php echo $user->username." (".$user->email." )"; ?>
                            </option>
                        <?php } ?>
                    </select>
              </td>
            </tr>
        </table>
    </div>
    </form>
</div>
<?php
echo $OUTPUT->footer();
