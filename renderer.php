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
        $context = context_course::instance($course->id);

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
                'class' => 'btn btn-primary'));
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'value' => 'true', 'name' => 'editmenumode'));
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'value' => $course->id, 'name' => 'id'));
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'value' => 'Nil', 'name' => 'token'));
            echo html_writer::end_tag('form');
        } else {
            if (is_siteadmin()) {
                echo html_writer::empty_tag('input', array('type' => 'submit',
                    'value' => get_string('editmenu', 'format_classroom') ,
                    'class' => 'btn', 'disabled' => 'disabled' , 'style' => 'color:#000'));
                echo "<br/>";
            }
        }
        echo '<br/>';
        // If user enrol for course.
        if (empty($checkuserrole)) {
            $sql = "select * from {classroom_session}  cs
            where cs.isdeleted !=0 and cs.courseid=?
            and FROM_UNIXTIME(session_date_end,'%Y-%m-%d %H:%i') >= NOW()";
            $getsessiondetails1 = $DB->get_records_sql($sql, array($course->id));
            $c = 0;

            foreach ($getsessiondetails1 as $key => $sessiondetails) {
                $in1 = '';
                $style = '';
                if ($c == 0) {
                    $in1 = 'show';
                }
                echo "<div class='card-group' id='accordion'>";
                echo "<div class='card'>";
                echo "<div class='card-header card-primary' style='background-color:#1177d1'>
                            <strong class='card-title'>
                                <a data-toggle='collapse' data-parent='#accordion'
                                href='#collapse$c' style='color: #FFF;'>".
                                strtoupper('Session : '.$sessiondetails->session).
                                "</a>
                            </strong>
                        </div>";
                echo "</div>
                </div>";
                echo "<div id='collapse$c' class='collapse $in1' data-parent='#accordion' $style;>";
                echo "<div class='card-body' style='padding: 10px 50px 10px 50px;''>";
                echo "<table width='100%'><tr>";
                echo "<td><b>Session Start Date & Time:</b></td>";
                echo "<td>".date('Y-m-d H:i', $sessiondetails->session_date)."</td>";
                echo "<td><b>Session Location:</b></td>";
                echo "<td>";
                $getlocation = $DB->get_record('classroom_location', array('id' => $sessiondetails->location));
                echo $getlocation->location;
                echo "</td>";
                echo "</tr>";
                echo "<tr>";
                echo "<td><b>Session End date & Time:</b></td>";
                echo "<td>".date('Y-m-d  H:i', $sessiondetails->session_date_end)."</td>";
                echo "<td><b>Session Classroom:</b></td>";
                echo "<td>";
                $getclassroom = $DB->get_record('classroom', array('id' => $sessiondetails->classroom));
                echo $getclassroom->classroom;
                echo "</td>";
                echo "</tr>";
                echo "<tr>";
                echo "<td><b>Last sub-scription date:</b></td>";
                echo "<td>".date('Y-m-d H:i', $sessiondetails->last_subscription_date)."</td>";
                echo "</tr>";
                echo "</table>";
                echo "</div></div>";
                $c = $c + 1;
            }
            echo "<br/>";
        } else {
            // If course not enrol.
            if ($PAGE->user_is_editing()) {
                // User is editing, Admin, Manager, and Teacher.
                $sql = "select * from {classroom_session}  cs
                where cs.isdeleted !=0 and cs.courseid=?
                and FROM_UNIXTIME(session_date_end,'%Y-%m-%d %H:%i') >= NOW()";
                $getsessiondetails2 = $DB->get_records_sql($sql, array($course->id));
            } else {
                // For Admin user.
                if (is_siteadmin()) {
                    $sql = "select * from {classroom_session}  cs
                    where cs.isdeleted !=0 and cs.courseid=?
                    and FROM_UNIXTIME(session_date_end,'%Y-%m-%d %H:%i') >= NOW()";
                } else {
                    // For student users.
                    $sql = "select * from {classroom_session} cs
                    INNER JOIN {classroom_assignuser} ca
                    ON cs.id=ca.session_id where cs.isdeleted !=0 and cs.courseid=?
                    and FROM_UNIXTIME(session_date_end,'%Y-%m-%d %H:%i') >= NOW() and ca.userid=?";
                }
                $getsessiondetails2 = $DB->get_records_sql($sql, array($course->id, $USER->id));
            }

            $c = 0;
            foreach ($getsessiondetails2 as $key => $sessiondetails) {
                 $in1 = '';
                $style = '';
                if ($c == 0) {
                    $in1 = 'show';
                }
                echo "<div class='card-group' id='accordion'>";
                echo "<div class='card'>";
                echo "<div class='card-header card-primary' style='background-color:#1177d1'>
                            <strong class='card-title'>
                                <a data-toggle='collapse' data-parent='#accordion'
                                href='#collapse$c' style='color: #FFF;'>".
                                strtoupper('Session : '.$sessiondetails->session).
                                "</a>
                            </strong>
                        </div>";
                echo "</div>
                </div>";
                echo "<div id='collapse$c' class='collapse $in1' data-parent='#accordion' $style;>";
                echo "<div class='card-body' style='padding: 10px 50px 10px 50px;''>";
                echo "<table width='100%'><tr>";
                echo "<td><b>Session Start Date & Time:</b></td>";
                echo "<td>".date('Y-m-d H:i', $sessiondetails->session_date)."</td>";
                echo "<td><b>Session Location:</b></td>";
                echo "<td>";
                $getlocation = $DB->get_record('classroom_location', array('id' => $sessiondetails->location));
                echo $getlocation->location;
                echo "</td>";
                echo "</tr>";
                echo "<tr>";
                echo "<td><b>Session End date & Time:</b></td>";
                echo "<td>".date('Y-m-d  H:i', $sessiondetails->session_date_end)."</td>";
                echo "<td><b>Session Classroom:</b></td>";
                echo "<td>";
                $getclassroom = $DB->get_record('classroom', array('id' => $sessiondetails->classroom));
                echo $getclassroom->classroom;
                echo "</td>";
                echo "</tr>";
                echo "<tr>";
                echo "<td><b>Last sub-scription date:</b></td>";
                echo "<td>".date('Y-m-d H:i', $sessiondetails->last_subscription_date)."</td>";
                echo "</tr>";
                echo "</table>";
                echo "</div></div>";
                $c = $c + 1;
            }
            echo "<br/>";
        }

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, 0);

        // Now the list of sections..
        echo $this->start_section_list();
        $numsections = course_get_format($course)->get_last_section_number();

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
        global $PAGE, $CFG, $DB, $OUTPUT, $USER;
        $PAGE->requires->js( new moodle_url($CFG->wwwroot . '/course/format/classroom/jquery.min.js'));
        $PAGE->requires->js( new moodle_url($CFG->wwwroot . '/course/format/classroom/search.js'));
        if (!$PAGE->user_is_editing()) {
            $this->print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection);
            return;
        }
        echo "<style type='text/css'> #buttoneditmenuend:hover { background-color:#FFF; } </style>";
        echo html_writer::start_tag('form', array('method' => 'GET'));
        echo "<style> .btn-secondary:active:hover { background-color:#FFF; } </style>";
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

        $coursecancellink = new moodle_url($CFG->wwwroot.'/course/view.php',
                                array('id' => $course->id, 'section' => $displaysection));

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
