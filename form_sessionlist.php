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
 * List of session with action buttons.
 *
 * @since 3.4.2
 * @package format_classroom
 * @copyright eNyota Learning Pvt Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../config.php');
global $CFG, $USER, $DB, $PAGE, $COURSE;
$context = context_system::instance();
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
require_login();
$out = '';
$out .= html_writer::empty_tag('input', array('type' => 'text', 'class' => 'form-control',
'name' => 'search', 'id' => 'search', 'placeholder' => 'Search'));
echo $out;

echo'<a class = "btn btn-primary addbtn"
href = '.$CFG->wwwroot.'/course/format/classroom/session.php?courseid='.$COURSE->id.'
title="'.get_string('addsession', 'format_classroom').'"> '. get_string('addsession', 'format_classroom') .' </a><br/><br/><br/>';
echo "<style> td.cell.c5.lastcol{ padding-left:5px; } </style>";
$start = $page * $perpage;
$results1 = $DB->get_records_sql("SELECT * FROM {classroom_session}
WHERE isdeleted != 0 and courseid = ?", array($COURSE->id));
$results = $DB->get_records_sql("SELECT * FROM {classroom_session}
	WHERE isdeleted != 0 and courseid = ? LIMIT $start,$perpage" , array($COURSE->id));
$table = new html_table();
$table->id = 'myTable';
$table->head = array('Session Name',
    'Session Start Date',
    'Session End Date',
    'Last Subscription Date',
    'Duration',
    'Location',
    'Actions');

$i = 1;
$j = 0;

foreach ($results as $re) {
    $id = $i++;
    $cid = $re->id;
    $session = $re->session;
    $sessiondate = $re->session_date;
    $sessiondateend = $re->session_date_end;
    $lastsubscriptiondate = $re->last_subscription_date;
    $location = $re->location;
    $classroom = $re->classroom;
    $maxenrol = $re->maxenrol;
    $getlocation = $DB->get_record('classroom_location', array('id' => $location));
    $getclassroom = $DB->get_record('classroom', array('id' => $classroom));
    $linkurl2 = 'course/format/classroom/delete_sess.php?cid='.$cid.'&courseid='.$COURSE->id;
    $linkurl1 = 'course/format/classroom/session_edit.php?cid='.$cid.'&courseid='.$COURSE->id;
    $iconedit = '<i class="icon fa fa-cog fa-fw"></i>';
    $icondelete = '<i class="icon fa fa-trash fa-fw "></i>';
    $viewicon = '<i class="icon fa fa-eye fa-fw"></i>';
    $link = '';
    $dataatt = 'data-toggle="modal" data-backdrop="static"';
    if ($sessiondateend >= time()) {
        $link = '<a href = '.$CFG->wwwroot.'/'.$linkurl1.' title="Edit" >'.$iconedit.'</a>&nbsp;';
        $link .= '<a  href="'.$CFG->wwwroot.'/'.$linkurl2.'" title="Delete">'.$icondelete.'</a>';
        $link .= '<a href="#" '.$dataatt.' data-target="#myModal'.$cid.'" title="View">'.$viewicon.'</a>';
    } else {
        $link .= '<a href="#" '.$dataatt.' data-target="#myModal'.$cid.'" title="View">'. $viewicon.'</a>';
        $link .= '<a  href="'.$CFG->wwwroot.'/'.$linkurl2.'" title="Delete">'.$icondelete.'</a>';
        $link .= "<span class='tag tag-info noactive' title='No Users assign'>Not active</span>";
    }

    $datetime1 = new DateTime(date('d-m-Y H:i', $sessiondateend));
    $datetime2 = new DateTime(date('d-m-Y H:i', $sessiondate));
    $interval = $datetime1->diff($datetime2);
    $duration = $interval->format('%h')." Hrs ".$interval->format('%i')." Min";
    if (!empty($interval->days)) {
        $duration = $interval->days." Day ".$interval->format('%h')." Hrs ".$interval->format('%i')." Min";
    }
    if ($j >= 0) {
        $table->data[] = array($session, date('d-m-Y H:i', $sessiondate),
        date('d-m-Y H:i', $sessiondateend), date('d-m-Y H:i', $lastsubscriptiondate) , $duration, $getlocation->location, $link);
    }
    $j++;
    $hide1 = '<span id="hide" class="substr"> Hide </span>';
    $details1 = $re->other_details;
    $hideeetails = $re->other_details.$hide1;
    if (strlen($re->other_details) > 100) {
        $details = substr($re->other_details, 0, 40);
        $details1 = substr($re->other_details, 0, 40).'...<span id="black_only" class="substr"> Read more </span>';
    }
    $popupcontent = '<div class="modal fade" id="myModal'.$cid.'" role="dialog">';
    $popupcontent .= '<div class="modal-dialog">';
    $popupcontent .= '<div class="modal-content">';
    $popupcontent .= '<div class="modal-header">';
    $popupcontent .= '<h4 class="modal-title"> Session : '.$session.'</h4>';
    $popupcontent .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
    $popupcontent .= '</div> <div class="modal-body custombody">';
    $popupcontent .= '<table style="margin-left: 10px;">';
    $popupcontent .= '<tr> <th>'.get_string("location", "format_classroom").' : </th> <td>'.$getlocation->location.'</td> </tr>';
    $popupcontent .= '<tr> <th>'.get_string("classroom", "format_classroom").' : </th> <td>'.$getclassroom->classroom.'</td> </tr>';
    $popupcontent .= '<tr> <th>'.get_string("maxenrol", "format_classroom").' :&nbsp; &nbsp; </th> <td>'.$maxenrol.'</td> </tr>';
    $popupcontent .= '<tr id="hidethis"> <th>'.get_string("otherdetails", "format_classroom").' : &nbsp; </th> <td>'.$details1.'</td> </tr>';
    $popupcontent .= '<tr id="hidethis1" class="hidden" valign="top"> <th>'.get_string("otherdetails", "format_classroom").' : &nbsp; </th> <td>'.$hideeetails.'</td> </tr>';
    $popupcontent .= '</table> </div> <div class="modal-footer">';
    $popupcontent .= '<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>';
    $popupcontent .= '</div> </div> </div> </div>';
    echo $popupcontent;
}
echo html_writer::table($table);
// Script JS file include to show/hide details of classroom.
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/course/format/classroom/script.js') );
echo "<div class='nodata'><b class='nodatatodisplay'>".get_string('nodatatodisplay', 'format_classroom')."</b><br></div>";
if ($j == 0) {
    echo "<div class='nodata1'><b class='nodatatodisplay'>".get_string('nodatatodisplay', 'format_classroom')."</b></div><br>";
}
$baseurl = new moodle_url($CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'&editmenumode=true&menuaction=sessionlist&token=1',
array('sort' => 'location', 'dir' => 'ASC', 'perpage' => $perpage));
echo $OUTPUT->paging_bar(count($results1), $page, $perpage, $baseurl);