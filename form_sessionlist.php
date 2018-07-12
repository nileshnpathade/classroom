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

require_once('../config.php');
global $CFG, $USER, $DB, $PAGE, $COURSE;
$context = context_system::instance();
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);

require_login();

$out = '';
$out .= html_writer::empty_tag('input', array('type' => 'text', 'class' => 'form-control',
'name' => 'search', 'id' => 'search', 'placeholder' => 'Search',
'style' => 'max-width:20%;float:right;'));
echo $out;

echo'<a class = "btn btn-primary"
href = '.$CFG->wwwroot.'/course/format/classroom/session.php?courseid='.$COURSE->id.'
style = "float:left;" title="'.get_string('addsession', 'format_classroom').'"> '. get_string('addsession', 'format_classroom') .' </a><br/><br/><br/>';
echo "<style> td.cell.c5.lastcol{ padding-left:5px; } </style>";
$start = $page * $perpage;
$results1 = $DB->get_records_sql("select * from {classroom_session}
where isdeleted != 0 and courseid = ?", array($COURSE->id));
$results = $DB->get_records_sql("select * from {classroom_session}
	where isdeleted != 0 and courseid = ? LIMIT $start,$perpage" , array($COURSE->id));
$table = new html_table();
$table->id = 'myTable';
$table->head = array('Session Name',
    'Session Start Date',
    'Session End Date',
    'Last subscription date',
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
        $link .= "<span class='tag tag-info' style='background-color:gray;padding:5px;' title='No Users assign'>Not active</span>";
    }

    $datetime1 = new DateTime(date('Y-m-d H:i', $sessiondateend));
    $datetime2 = new DateTime(date('Y-m-d H:i', $sessiondate));
    $interval = $datetime1->diff($datetime2);
    $duration = $interval->format('%h')." Hrs ".$interval->format('%i')." Mint";
    if (!empty($interval->days)) {
        $duration = $interval->format('%d')." Day ".$interval->format('%h')." Hrs ".$interval->format('%i')." Mint";
    }
    if ($j >= 0) {
        $table->data[] = array($session, date('Y-m-d H:i', $sessiondate),
        date('Y-m-d H:i', $sessiondateend), date('Y-m-d H:i', $lastsubscriptiondate) , $duration, $getlocation->location, $link);
    }
    $j++;
    $popupcontent = '<div class="modal fade" id="myModal'.$cid.'" role="dialog">';
    $popupcontent .= '<div class="modal-dialog" style="max-width:400px;">';
    $popupcontent .= '<div class="modal-content">';
    $popupcontent .= '<div class="modal-header">';
    $popupcontent .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
    $popupcontent .= '<h4 class="modal-title"> Session : '.$session.'</h4>';
    $popupcontent .= '</div> <div class="modal-body">';
    $popupcontent .= '<table style="margin-left: 10px;">';
    $popupcontent .= '<tr> <th>'.get_string("location", "format_classroom").' : </th> <td>'.$getlocation->location.'</td> </tr>';
    $popupcontent .= '<tr> <th>'.get_string("classroom", "format_classroom").' : </th> <td>'.$getclassroom->classroom.'</td> </tr>';
    $popupcontent .= '<tr> <th>'.get_string("maxenrol", "format_classroom").' : </th> <td>'.$maxenrol.'</td> </tr>';
    $popupcontent .= '</table> </div> <div class="modal-footer">';
    $popupcontent .= '<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>';
    $popupcontent .= '</div> </div> </div> </div>';
    echo $popupcontent;
}
echo html_writer::table($table);
if ($j == 0) {
    echo "<b style='color:#3A3D3E'>".get_string('nodatatodisplay', 'format_classroom')."</b><br>";
}

$baseurl = new moodle_url($CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'&editmenumode=true&menuaction=sessionlist&token=1',
array('sort' => 'location', 'dir' => 'ASC', 'perpage' => $perpage));
echo $OUTPUT->paging_bar(count($results1), $page, $perpage, $baseurl);