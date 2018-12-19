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
 * Renderer for outputting the classroom course format.
 *
 * @package format_classroom
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.3
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/renderer.php');
$PAGE->requires->css( new moodle_url($CFG->wwwroot . '/course/format/classroom/css/style.css'));
/**
 * Basic renderer for classroom format.
 *
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_classroom_renderer extends format_section_renderer_base {

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);

        // Since format_classroom_renderer::section_edit_controls().
        // Only displays the 'Set current section' control when editing mode is on,
        // We need to be sure that the link 'Turn editing mode on' is available,
        // for a user who does not have any other managing capability.
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }

    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'classroom'));
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title() {
        return get_string('topicoutline');
    }

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section));
    }

    /**
     * Generate the section title to be displayed on the section page, without a link
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
    }

    /**
     * Generate the edit control items of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of edit control items
     */
    protected function section_edit_control_items($course, $section, $onsectionpage = false) {
        global $PAGE;

        if (!$PAGE->user_is_editing()) {
            return array();
        }

        $coursecontext = context_course::instance($course->id);

        if ($onsectionpage) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        $controls = array();
        if ($section->section && has_capability('moodle/course:setcurrentsection', $coursecontext)) {
            if ($course->marker == $section->section) {  // Show the "light globe" on/off.
                $url->param('marker', 0);
                $markedthistopic = get_string('markedthistopic');
                $highlightoff = get_string('highlightoff');
                $controls['highlight'] = array('url' => $url, "icon" => 'i/marked',
                                               'name' => $highlightoff,
                                               'pixattr' => array('class' => '', 'alt' => $markedthistopic),
                                               'attr' => array('class' => 'editing_highlight', 'title' => $markedthistopic,
                                                   'data-action' => 'removemarker'));
            } else {
                $url->param('marker', $section->section);
                $markthistopic = get_string('markthistopic');
                $highlight = get_string('highlight');
                $controls['highlight'] = array('url' => $url, "icon" => 'i/marker',
                                               'name' => $highlight,
                                               'pixattr' => array('class' => '', 'alt' => $markthistopic),
                                               'attr' => array('class' => 'editing_highlight', 'title' => $markthistopic,
                                                   'data-action' => 'setmarker'));
            }
        }

        $parentcontrols = parent::section_edit_control_items($course, $section, $onsectionpage);

        // If the edit key exists, we are going to insert our controls after it.
        if (array_key_exists("edit", $parentcontrols)) {
            $merged = array();
            // We can't use splice because we are using associative arrays.
            // Step through the array and merge the arrays.
            foreach ($parentcontrols as $key => $action) {
                $merged[$key] = $action;
                if ($key == "edit") {
                    // If we have come to the edit key, merge these controls here.
                    $merged = array_merge($merged, $controls);
                }
            }

            return $merged;
        } else {
            return array_merge($controls, $parentcontrols);
        }
    }

    /**
     * Output the html for a single section page .
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     * @param int $displaysection The section number in the course which is being displayed
     */
    public function print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection) {
        global $PAGE, $DB, $USER, $COURSE, $CFG;
        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();
        $PAGE->requires->js(
            new moodle_url('https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyA3RCnSbZgjqVKOcixGRKB3cAbF6WdPc5M'));
        $PAGE->requires->js( new moodle_url($CFG->wwwroot . '/course/format/classroom/viewmap.js'));
        // Title with completion help icon.
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');

        // SQL for enrol user for course.
        $sql = "SELECT * FROM {role_assignments} AS ra
        LEFT JOIN {user_enrolments} AS ue ON ra.userid = ue.userid
        LEFT JOIN {role} AS r ON ra.roleid = r.id
        LEFT JOIN {context} AS c ON c.id = ra.contextid
        LEFT JOIN {enrol} AS e ON e.courseid = c.instanceid
        AND ue.enrolid = e.id WHERE r.id = 5
        AND ue.userid = $USER->id AND e.courseid = $COURSE->id";
        $checkuserrole = $DB->get_record_sql($sql, array());
        if ($PAGE->user_is_editing()) {
            echo html_writer::start_tag('form', array('method' => 'post'));
            echo html_writer::empty_tag('input', array('type' => 'submit',
                'value' => get_string('editmenu', 'format_classroom') ,
                'class' => 'btn btn-primary mangesession'));
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'value' => 'true', 'name' => 'editmenumode'));
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'value' => $course->id, 'name' => 'id'));
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'value' => 'Nil', 'name' => 'token'));
            echo html_writer::end_tag('form');
        } else {
            if (is_siteadmin()) {
                echo html_writer::empty_tag('input', array('type' => 'submit',
                    'value' => get_string('editmenu', 'format_classroom') ,
                    'class' => 'btn mangesession', 'disabled' => 'disabled' ));
                echo "<br/>";
            }
        }
        echo '<br/>';
        // If user enrol for course.
        if (empty($checkuserrole)) {
            $sql = "select * from {format_classroom_session}  cs
            where cs.isdeleted !=0 and cs.courseid=?
            and FROM_UNIXTIME(session_date_end,'%Y-%m-%d %H:%i') >= NOW()";
            $getsessiondetails1 = $DB->get_records_sql($sql, array($course->id));
            $c = 0;

            foreach ($getsessiondetails1 as $key => $sessiondetails) {
                $in1 = '';
                $style = 'collapsed';
                if ($c == 0) {
                    $in1 = 'show in';
                    $style = '';
                }

                $sessionotherdetails = $sessiondetails->other_details;
                if (strlen($sessiondetails->other_details) > 100) {
                    $sessionotherdetails = strip_tags(substr($sessiondetails->other_details, 0, 80))."...
                    <a href='#' data-toggle='modal' data-target='#myModal".$c."'>Read More </a>";
                }

                echo "<div class='card-group' id='accordion'>";
                echo "<div class='card'>";

                echo "<a data-toggle='collapse' data-parent='#accordion'
                                href='#collapse$c' class='card-link ".$style."' aria-expanded='true'>
                                <div class='card-header'>
                                <strong class='card-title'>".
                                    strtoupper('Session : '.$sessiondetails->session).
                                "</strong><span class='toggleSymbol'></span>";
                echo "</div></a></div></div>";

                echo "<div id='collapse$c' class='collapse $in1' data-parent='#accordion'>";
                echo "<div class='card-body managebody'>";

                echo "<table width='100%' class='valign'><tr>";
                echo "<td><b>Subscription Date From</b></td>";
                echo "<td>".date('d-m-Y H:i', $sessiondetails->last_subscription_date_from)."</td>";

                echo "<td><b>Session Location</b></td>";
                echo "<td>";

                $getlocation = $DB->get_record('format_classroom_location', array('id' => $sessiondetails->location));
                $locationofclass = "<a href='#' data-toggle='modal' onclick='javascript:initAutocomplete()'
                data-target='#mylocation".$c."'>".strip_tags(substr($getlocation->location, 0, 15))."...</a>";
                echo $locationofclass;

                echo "</td>";
                echo "</tr>";

                echo "<tr>";

                echo "<td><b>Subscription Date To</b></td>";
                echo "<td>".date('d-m-Y H:i', $sessiondetails->last_subscription_date)."</td>";

                echo "<td><b>Session Classroom</b></td>";
                echo "<td>";
                $getclassroom = $DB->get_record('format_classroom', array('id' => $sessiondetails->classroom));
                $classroom = "<a href='#' data-toggle='modal' data-target='#myclassroom".$c."'
                title='View'>".$getclassroom->classroom."</a>";
                echo $classroom;

                $seats = $getclassroom->seats;
                $location = $getlocation->location;
                $hide1 = '<span id="hide" class="substr"> Hide </span>';
                $hide2 = '<span id="hide_equp" class="substr"> Hide </span>';
                $equipment = $getclassroom->equipment;
                $equipment1 = $getclassroom->equipment;
                $hideequipment = $getclassroom->equipment.$hide2;
                $details = $getclassroom->details;
                $details1 = $getclassroom->details;
                $hideeetails = $getclassroom->details.$hide1;
                if (strlen($getclassroom->equipment) > 100) {
                    $equipment = substr($getclassroom->equipment, 0, 50).'...';
                    $equipment1 = substr($getclassroom->equipment, 0, 50).'...<span id="equipment_black_only"
                    class="substr"> Read more </span>';
                }
                if (strlen($getclassroom->details) > 100) {
                    $details = substr($getclassroom->details, 0, 50);
                    $details1 = substr($getclassroom->details, 0, 50).'...<span id="black_only" class="substr"> Read more </span>';
                }
                $teacher = '';
                if ( !empty($sessiondetails->teacher) ) {
                    $teacheruser = get_complete_user_data('id', $sessiondetails->teacher);
                    $teacher = $teacheruser->firstname.' '.$teacheruser->lastname;
                }

                echo "</td>";
                echo "</tr>";

                echo "<tr>";
                echo "<td><b>Session End Date & Time</b></td>";
                echo "<td>".date('d-m-Y  H:i', $sessiondetails->session_date_end)."</td>";
                echo "<td><b>Session Start Date & Time</b></td>";
                echo "<td>".date('d-m-Y H:i', $sessiondetails->session_date)."</td>";
                echo "</tr>";
                echo "<tr>";
                echo "<td><b>".get_string('maxenrol', 'format_classroom')."</b></td>";
                echo "<td>".$sessiondetails->maxenrol."</td>";
                echo "<td><b>".get_string('teacher', 'format_classroom')."</b></td>";
                echo "<td>".$teacher."</td>";
                echo "</tr>";

                echo "<tr>";
                echo "<td colspan='1'><b>".get_string('otherdetails', 'format_classroom')."</b></td>";
                echo "<td width='100px' valign='top' colspan='3'>";
                echo $sessionotherdetails;
                echo "</td>";
                echo "</tr>";
                echo "</table>";
                echo "</div></div>";

                $popupcontent = '<div class="modal fade" id="myModal'.$c.'" role="dialog">';
                $popupcontent .= '<div class="modal-dialog">';
                $popupcontent .= '<div class="modal-content">';
                $popupcontent .= '<div class="modal-header">';
                $popupcontent .= '<h4 class="modal-title"> '.get_string('otherdetails', 'format_classroom').' </h4>';
                $popupcontent .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $popupcontent .= '</div> <div class="modal-body">';
                $popupcontent .= '<table class="popuptable">';
                $popupcontent .= '<tr id="hidethis"> <td> '.strip_tags($sessiondetails->other_details).' </td> </tr>';
                $popupcontent .= '</table>';
                $popupcontent .= '</div> <div class="modal-footer">';
                $popupcontent .= '<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>';
                $popupcontent .= '</div> </div> </div> </div>';
                echo $popupcontent;

                $popuplocat = '<div class="modal fade" id="mylocation'.$c.'" role="dialog">';
                $popuplocat .= '<div class="modal-dialog">';
                $popuplocat .= '<div class="modal-content">';
                $popuplocat .= '<div class="modal-header">';
                $popuplocat .= '<h4 class="modal-title"> Location : '.strip_tags($getlocation->location).'</h4>';
                $popuplocat .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $popuplocat .= '</div> <div class="modal-body">';
                $popuplocat .= '<input type="hidden" name="address" id="id_address" value="'.$getlocation->address.'" />';
                $popuplocat .= '<table style="margin-left: 10px;">';
                $popuplocat .= '<tr> <th>Address : </th> <td>'.$getlocation->address.'</td> </tr>';
                $popuplocat .= '<tr> <th>Email ID  : </th> <td>'.$getlocation->emailid.'</td> </tr>';
                $popuplocat .= '<tr> <th>Phone No : </th> <td>'.$getlocation->phoneno.'</td> </tr>';
                $popuplocat .= '<tr> <th>Map : </th> <td> <div id="map"></div> </td> </tr>';
                $popuplocat .= '</table> </div> <div class="modal-footer">';
                $popuplocat .= '<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>';
                $popuplocat .= '</div> </div> </div> </div>';
                echo $popuplocat;

                $popupclass = '<div class="modal fade" id="myclassroom'.$c.'" role="dialog">';
                $popupclass .= '<div class="modal-dialog">';
                $popupclass .= '<div class="modal-content">';
                $popupclass .= '<div class="modal-header">';
                $popupclass .= '<h4 class="modal-title"> Classroom : '.$getclassroom->classroom.'</h4>';
                $popupclass .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $popupclass .= '</div> <div class="modal-body">';
                $popupclass .= '<table style="margin-left: 10px;">';
                $popupclass .= '<tr> <th>Location : </th> <td> '.$location.'</td> </tr>';
                $popupclass .= '<tr> <th>Seats : </th> <td>'.$seats.'</td> </tr>';
                $popupclass .= '<tr id="hidethis"> <th>Details : </th> <td> '.$details1.'</td> </tr>';
                $popupclass .= '<tr id="hidethis1" class="hidden" valign="top" > <th>Details : </th>';
                $popupclass .= '<td> '.$hideeetails.'</td> </tr>';
                $popupclass .= '<tr id="equipment"> <th>Equipment : </th> <td> '.$equipment1.'</td> </tr>';
                $popupclass .= '<tr id="equipment1" class="hidden" valign="top"> <th>Equipment : </th>';
                $popupclass .= '<td> '.$hideequipment.'</td> </tr>';
                $popupclass .= '</table>';
                $popupclass .= '</div> <div class="modal-footer">';
                $popupclass .= '<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>';
                $popupclass .= '</div> </div> </div> </div>';
                echo $popupclass;

                $c = $c + 1;
            }
            echo "<br/>";
        } else {
            // If course not enrol.
            if ($PAGE->user_is_editing()) {
                // User is editing, Admin, Manager, and Teacher.
                $sql = "select * from {format_classroom_session}  cs
                where cs.isdeleted !=0 and cs.courseid=?
                and FROM_UNIXTIME(session_date_end,'%Y-%m-%d %H:%i') >= NOW()";
                $getsessiondetails2 = $DB->get_records_sql($sql, array($course->id));
            } else {
                // For Admin user.
                if (is_siteadmin()) {
                    $sql = "select * from {format_classroom_session}  cs
                    where cs.isdeleted !=0 and cs.courseid=?
                    and FROM_UNIXTIME(session_date_end,'%Y-%m-%d %H:%i') >= NOW()";
                } else {
                    // For student users.
                    $sql = "select * from {format_classroom_session} cs
                    INNER JOIN {format_classroom_assignuser} ca
                    ON cs.id=ca.session_id where cs.isdeleted !=0 and cs.courseid=?
                    and FROM_UNIXTIME(session_date_end,'%Y-%m-%d %H:%i') >= NOW() and ca.userid=?";
                }
                $getsessiondetails2 = $DB->get_records_sql($sql, array($course->id, $USER->id));
            }

            $c = 0;
            foreach ($getsessiondetails2 as $key => $sessiondetails) {
                $in1 = '';
                $style = 'collapsed';
                if ($c == 0) {
                    $in1 = 'show in';
                    $style = '';
                }
                $otherdetails = $sessiondetails->other_details;
                if (strlen($sessiondetails->other_details) > 100) {
                    $otherdetails = strip_tags(substr($sessiondetails->other_details, 0, 80))."... <a href='#'
                    data-toggle='modal' data-target='#myModal".$c."'> Read More </a>";
                }

                echo "<div class='card-group' id='accordion'>";
                echo "<div class='card'>";
                echo "<a data-toggle='collapse' data-parent='#accordion'
                                href='#collapse$c' class='card-link ".$style."' aria-expanded='true'>
                                <div class='card-header'>
                                <strong class='card-title'>".
                                    strtoupper('Session : '.$sessiondetails->session).
                                "</strong><span class='toggleSymbol'></span>";
                echo "</div></a>
                </div>
                </div>";
                echo "<div id='collapse$c' class='collapse $in1' data-parent='#accordion'>";
                echo "<div class='card-body managebody'>";
                echo "<table width='100%' class='valign'><tr>";
                echo "<td><b>Subscription Date From</b></td>";
                echo "<td>".date('d-m-Y H:i', $sessiondetails->last_subscription_date_from)."</td>";
                echo "<td><b>Session Location</b></td>";
                echo "<td>";
                $getlocation = $DB->get_record('format_classroom_location', array('id' => $sessiondetails->location));
                $location = "<a href='#' data-toggle='modal' onclick='javascript:initAutocomplete()'
                data-target='#mylocation".$c."'>".strip_tags(substr($getlocation->location, 0, 15))."...</a>";
                echo $location;
                echo "</td>";
                echo "</tr>";
                echo "<tr>";
                echo "<td><b>Subscription Date To</b></td>";
                echo "<td>".date('d-m-Y H:i', $sessiondetails->last_subscription_date)."</td>";
                echo "<td><b>Session Classroom</b></td>";
                echo "<td>";
                $getclassroom = $DB->get_record('format_classroom', array('id' => $sessiondetails->classroom));
                $classroom = "<a href='#' data-toggle='modal' data-target='#myclassroom".$c."'
                title='View'>".$getclassroom->classroom."</a>";
                echo $classroom;

                $seats = $getclassroom->seats;
                $location = $getlocation->location;
                $hidestr = '<span id="hide" class="substr"> Hide </span>';
                $hide2str = '<span id="hide_equp" class="substr"> Hide </span>';
                $equipment = $getclassroom->equipment;
                $equipment1 = $getclassroom->equipment;
                $hideequipmentval = $getclassroom->equipment.$hide2str;
                $details = $getclassroom->details;
                $details1 = $getclassroom->details;
                $hideeetails = $getclassroom->details.$hidestr;
                if (strlen($getclassroom->equipment) > 100) {
                    $equipment = substr($getclassroom->equipment, 0, 50).'...';
                    $equipment1 = substr($getclassroom->equipment, 0, 50).'...<span id="equipment_black_only"
                    class="substr"> Read more </span>';
                }
                if (strlen($getclassroom->details) > 100) {
                    $details = substr($getclassroom->details, 0, 50);
                    $details1 = substr($getclassroom->details, 0, 50).'...<span id="black_only" class="substr"> Read more </span>';
                }
                $teacherfullname = '';
                if ( !empty($sessiondetails->teacher) ) {
                    $teacheruser = get_complete_user_data('id', $sessiondetails->teacher);
                    $teacherfullname = $teacheruser->firstname.' '.$teacheruser->lastname;
                }
                // HTML for Table start here.
                echo "</td>";
                echo "</tr>";

                echo "<tr>";
                echo "<td><b>Session End Date & Time</b></td>";
                echo "<td>".date('d-m-Y  H:i', $sessiondetails->session_date_end)."</td>";
                echo "<td><b>Session Start Date & Time</b></td>";
                echo "<td>".date('d-m-Y H:i', $sessiondetails->session_date)."</td>";
                echo "</tr>";

                echo "<tr>";
                echo "<td><b>".get_string('maxenrol', 'format_classroom')."</b></td>";
                echo "<td>".$sessiondetails->maxenrol."</td>";
                echo "<td><b>".get_string('teacher', 'format_classroom')."</b></td>";
                echo "<td>".$teacherfullname."</td>";
                echo "</tr>";

                echo "<tr>";
                echo "<td colspan='1'><b>".get_string('otherdetails', 'format_classroom')."</b></td>";
                echo "<td width='100px' valign='top' colspan='3'>";
                echo $otherdetails;
                echo "</td>";

                echo "</tr>";
                echo "</table>";
                echo "</div></div>";
                // HTML for Table end here.

                // Popup content for Othere details.
                $popupcontentdetails = '<div class="modal fade" id="myModal'.$c.'" role="dialog">';
                $popupcontentdetails .= '<div class="modal-dialog">';
                $popupcontentdetails .= '<div class="modal-content"><div class="modal-header">';
                $popupcontentdetails .= '<h4 class="modal-title"> '. get_string('otherdetails', 'format_classroom') .' </h4>';
                $popupcontentdetails .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $popupcontentdetails .= '</div> <div class="modal-body">';
                $popupcontentdetails .= '<table class="popuptable">';
                $popupcontentdetails .= '<tr id="hidethis"> <td>  '.strip_tags($sessiondetails->other_details).'  </td> </tr>';
                $popupcontentdetails .= '</table>';
                $popupcontentdetails .= '</div> <div class="modal-footer">';
                $popupcontentdetails .= '<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>';
                $popupcontentdetails .= '</div> </div> </div> </div>';
                echo $popupcontentdetails;

                // Popup content for Location.
                $popuplocation = '<div class="modal fade" id="mylocation'.$c.'" role="dialog">';
                $popuplocation .= '<div class="modal-dialog">';
                $popuplocation .= '<div class="modal-content"><div class="modal-header">';
                $popuplocation .= '<h4 class="modal-title"> Location : '.strip_tags($getlocation->location).'</h4>';
                $popuplocation .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $popuplocation .= '</div> <div class="modal-body">';
                $popuplocation .= '<input type="hidden" name="address" id="id_address" value="'.$getlocation->address.'" />';

                $popuplocation .= '<table style="margin-left: 10px;">';
                $popuplocation .= '<tr> <th>Address : </th> <td>'.$getlocation->address.'</td> </tr>';
                $popuplocation .= '<tr> <th>Email ID  : </th> <td>'.$getlocation->emailid.'</td> </tr>';
                $popuplocation .= '<tr> <th>Phone No : </th> <td>'.$getlocation->phoneno.'</td> </tr>';
                $popuplocation .= '<tr> <th>Map : </th> <td> <div id="map"></div> </td> </tr>';
                $popuplocation .= '</table> </div> <div class="modal-footer">';

                $popuplocation .= '<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>';
                $popuplocation .= '</div> </div> </div> </div>';
                echo $popuplocation;

                // Popup content for Classroom.
                $popupclassroom = '<div class="modal fade" id="myclassroom'.$c.'" role="dialog">';
                $popupclassroom .= '<div class="modal-dialog">';
                $popupclassroom .= '<div class="modal-content"><div class="modal-header">';
                $popupclassroom .= '<h4 class="modal-title"> Classroom : '.$getclassroom->classroom.'</h4>';
                $popupclassroom .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $popupclassroom .= '</div> <div class="modal-body">';

                $popupclassroom .= '<table style="margin-left: 10px;">';
                $popupclassroom .= '<tr> <th>Location : </th> <td> '.$location.'</td> </tr>';
                $popupclassroom .= '<tr> <th>Seats : </th> <td>'.$seats.'</td> </tr>';
                $popupclassroom .= '<tr id="hidethis"> <th>Details : </th> <td> '.$details1.'</td> </tr>';
                $popupclassroom .= '<tr id="hidethis1" class="hidden" valign="top" > <th>Details : </th>';
                $popupclassroom .= '<td> '.$hideeetails.'</td> </tr>';
                $popupclassroom .= '<tr id="equipment"> <th>Equipment : </th> <td> '.$equipment1.'</td> </tr>';
                $popupclassroom .= '<tr id="equipment1" class="hidden" valign="top"> <th>Equipment : </th>';
                $popupclassroom .= '<td> '.$hideequipmentval.'</td> </tr>';
                $popupclassroom .= '</table>';

                $popupclassroom .= '</div> <div class="modal-footer">';
                $popupclassroom .= '<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>';
                $popupclassroom .= '</div> </div> </div> </div>';
                echo $popupclassroom;

                $c = $c + 1;
            }
            echo "<br/>";
        }

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, 0);

        // Now the list of sections..
        echo $this->start_section_list();

        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            if ($section == 0) {
                // 0-section is displayed a little different then the others.
                if ($thissection->summary or !empty($modinfo->sections[0]) or $PAGE->user_is_editing()) {
                    echo $this->section_header($thissection, $course, false, 0);
                    echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                    echo $this->courserenderer->course_section_add_cm_control($course, 0, 0);
                    echo $this->section_footer();
                }
                continue;
            }
        }
    }

    /**
     * Output the html for a edit mode page.
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections The course_sections entries from the DB
     * @param array $mods used for print_section()
     * @param array $modnames used for print_section()
     * @param array $modnamesused used for print_section()
     * @param int $displaysection The section number in the course which is being displayed
     */
    public function print_edition_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection) {
        global $PAGE, $CFG;
        $PAGE->requires->js( new moodle_url($CFG->wwwroot . '/course/format/classroom/jquery.min.js'));
        $PAGE->requires->js( new moodle_url($CFG->wwwroot . '/course/format/classroom/search.js'));
        if (!$PAGE->user_is_editing()) {
            $this->print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection);
            return;
        }

        echo html_writer::start_tag('form', array('method' => 'GET'));
        echo html_writer::empty_tag('input', array('type' => 'submit',
            'value' => get_string('editmenuend', 'format_classroom') ,
            'class' => 'btn btn-secondary', 'id' => 'buttoneditmenuend'));
        echo html_writer::empty_tag('input', array('type' => 'hidden', 'value' => $displaysection, 'name' => 'section'));
        echo html_writer::empty_tag('input', array('type' => 'hidden', 'value' => $course->id, 'name' => 'id'));
        echo html_writer::end_tag('form');

        $menuaction = optional_param('menuaction', 'config', PARAM_ALPHA);
        $options = array('sessionlist', 'assginusertosession', 'attendance', 'csstemplate');
        if (!in_array($menuaction, $options)) {
            $menuaction = 'sessionlist';
        }

        $courselink = new moodle_url($CFG->wwwroot.'/course/view.php',
                        array('id' => $course->id, 'editmenumode' => 'true', 'section' => $displaysection));
        echo "<br/>";

        $tabs = array();

        $tabs[] = new tabobject("tab_configmenu_sessionlist", $courselink . '&menuaction=sessionlist&token=1',
            '' . get_string('sessionlist', 'format_classroom') . '');

        $tabs[] = new tabobject("tab_configmenu_assginusertosession", $courselink . '&menuaction=assginusertosession&token=1',
                        '<div>' . get_string('assginuser', 'format_classroom') . "</div>",
                        get_string('assginuser', 'format_classroom'));
        print_tabs(array($tabs), "tab_configmenu_".$menuaction);

        // Start box container.
        echo html_writer::start_tag('div', array('class' => 'box generalbox'));
        $formatdata = new stdClass();
        $formatdata->course = $course->id;
        include($CFG->dirroot . '/course/format/classroom/form_' . $menuaction . '.php');
        // Close box container.
        echo html_writer::end_tag('div');
    }
}
