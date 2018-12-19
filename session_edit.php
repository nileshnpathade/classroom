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
 * Editing Session form.
 *
 * @since 3.4.2
 * @package format_classroom
 * @copyright eNyota Learning Pvt Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once(dirname(__FILE__).'/session_edit_form.php');
global $CFG, $USER, $DB, $PAGE, $COURSE;

$courseid = optional_param('courseid', 0, PARAM_INT);
$sessionid = optional_param('cid', 0, PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);
$course = get_course($courseid);
$PAGE->set_url('/course/format/classroom/session_edit.php?cid='.$sessionid.'&courseid='.$courseid);
$PAGE->set_title(get_string('editsession', 'format_classroom'));
$PAGE->set_heading(get_string('editsession', 'format_classroom'));
$PAGE->set_pagelayout('course');
$PAGE->navbar->add(get_string('courses'), new moodle_url('/course/index.php'));
$PAGE->navbar->add($course->fullname, new moodle_url('/course/view.php?id='.$course->id));
$sesslisturl = '/course/view.php?id='.$course->id.'&editmenumode=true&menuaction=sessionlist&token=1';
$PAGE->navbar->add(get_string('sessionlist', 'format_classroom'), new moodle_url($sesslisturl));
require_login();
$PAGE->requires->jquery();
// If you are not user of editing course.
if (!$PAGE->user_is_editing()) {
    redirect($CFG->wwwroot);
}
$classroomid = 0;
$checkexits = $DB->get_record('format_classroom_session', array('id' => $sessionid));
if (!empty($checkexits)) {
    $classroomid = $checkexits->classroom;
}
$templatedata = new stdClass();
$args = array('courseid' => $courseid, 'session_id' => $sessionid);
$mform = new session_edit_form(null, $args);
$mform->set_data($args);

if ($mform->is_cancelled()) {
    $cancleurl = $CFG->wwwroot . "/course/view.php?id=".$courseid."&editmenumode=true&menuaction=sessionlist&token=1";
    redirect($cancleurl);
} else if ($fromform = $mform->get_data()) {
    $classroomsession = new stdClass();
    $classroomsession->id = (isset($fromform->session_id)) ? $fromform->session_id : '0';
    $classroomsession->session = (isset($fromform->session)) ? $fromform->session : '';
    $classroomsession->courseid = (isset($fromform->courseid)) ? $fromform->courseid : '';
    $classroomsession->session_date = (isset($fromform->session_date)) ? $fromform->session_date : time();
    $classroomsession->session_date_end = (isset($fromform->session_date_end))
    ? $fromform->session_date_end : time();
    $classroomsession->location = (isset($fromform->location)) ? $fromform->location : '';
    $classroomsession->classroom = (isset($_POST['classroom'])) ? $_POST['classroom'] : '';
    $classroomsession->teacher = (isset($fromform->teacher)) ? $fromform->teacher : '';
    $classroomsession->maxenrol = (isset($fromform->maxenrol)) ? $fromform->maxenrol : '';
    $classroomsession->last_subscription_date = (isset($fromform->last_subscription_date))
    ? $fromform->last_subscription_date : time();
    $classroomsession->last_subscription_date_from = (isset($fromform->last_subscription_date_from))
    ? $fromform->last_subscription_date_from : time();
    $classroomsession->other_details = (isset($fromform->other_details)) ? $fromform->other_details : '';
    $classroomsession->create_by = (isset($USER->id)) ? $USER->id : '';

    $updatedid = $DB->update_record('format_classroom_session', $classroomsession);
    if ($updatedid > 0) {
        $redirecturl = $CFG->wwwroot.'/course/view.php?id='.$fromform->courseid;
        $redirecturl .= '&editmenumode=true&menuaction=sessionlist&token=1';
        redirect($redirecturl, 'Session updated successfully', null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        echo 'Failed to insert record ';
    }
}
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
?>
<script>
function get_states(path, locationid, catid,classroomvalue) {
    var id = catid.slice(-1);
    $.ajax({
        url : path + "/course/format/classroom/getclassroom_name.php?locationid=" + locationid,
            beforeSend: function() {
            
            },
        success:function(result) {
            var output = '';
            $.each($.parseJSON(result), function(id, obj) {
                if(classroomvalue == id){
                    output += '<option value='+id+' selected="Selected">'+obj+'</option>';    
                } else {
                    output += '<option value='+id+'>'+obj+'</option>';
                }

            });
            $("#id_classroom").html(output);
        },

        cache: false,
        dataType: "html"
    });
}
$(document).ready(function(){
    var locationid = $('#id_location :selected').val();
    var path = "<?php echo $CFG->wwwroot; ?>";
    get_states(path, locationid, locationid,"<?php echo $classroomid; ?>");
});
</script>