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
$locationid = optional_param('location_id', 0, PARAM_INT);
$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_url('/../../../manage_classroom.php?location_id='.$locationid);
$PAGE->set_title(get_string('manage_classroom', 'format_classroom'));
$PAGE->set_heading(get_string('manage_classroom', 'format_classroom'));
$PAGE->set_pagelayout('course');
$PAGE->navbar->add('Site administration', new moodle_url('/admin/search.php'));
$PAGE->navbar->add('Plugins', new moodle_url('/admin/category.php?category=modules'));
$PAGE->navbar->add('Course formats', new moodle_url('/admin/category.php?category=formatsettings'));
$PAGE->navbar->add('Configure', new moodle_url('/admin/settings.php?section=formatsettingclassroom'));
$PAGE->navbar->add('Manage Location', new moodle_url('/course/format/classroom/manage_location.php'));
$PAGE->navbar->add('Manage Classroom');
$PAGE->requires->jquery();
require_login();
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);

echo $OUTPUT->header();
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/course/format/classroom/search.js'));

$out = '';
$out .= html_writer::empty_tag('input', array('type' => 'text',
'class' => 'form-control', 'name' => 'search', 'id' => 'search',
'placeholder' => 'Search', 'style' => 'max-width:20%;float:right;'));
echo $out;

$courseid = optional_param('cid', 0, PARAM_INT);
$addurl = 'course/format/classroom/add_classroom.php?location_id='.$locationid;
echo '<a class="btn btn-primary" href="'.$CFG->wwwroot.'/'.$addurl.'" title="Add Classroom" style="float:left;">';
echo ''.get_string('addclassroom', 'format_classroom').'';
echo '</a><br/><br/><br/>';
echo "<style> td.cell.c4.lastcol{ padding-left:0px; } </style>";

$start = $page * $perpage;
$sqlclass = "SELECT c.*, cl.location from {classroom} c INNER JOIN {classroom_location} cl ON c.location_id = cl.id WHERE c.isdeleted != 0 and c.location_id = ?";

$results1 = $DB->get_records_sql($sqlclass, array($locationid));
$sql = "SELECT c.*, cl.location from {classroom} c INNER JOIN {classroom_location} cl ON c.location_id = cl.id WHERE c.isdeleted != 0 and c.location_id = ? LIMIT $start, $perpage";

$results = $DB->get_records_sql($sql, array($locationid));

$table = new html_table();
$table->id = 'myTable';
$table->head = array('Classroom Name', 'Available Seats', 'Details', 'Equipment', 'Actions');
$i = 1;
$j = 0;
foreach ($results as $re) {
    $id = $i++;
    $cid = $re->id;
    $classroom = $re->classroom;
    $seats = $re->seats;
    $location = $re->location;
    $hide1 = '<span id="hide" style="cursor:pointer;font-weight:bold"> Hide </span>';
    $hide2 = '<span id="hide_equp" style="cursor:pointer;font-weight:bold"> Hide </span>';
    $equipment = $re->equipment;
    $equipment1 = $re->equipment;
    $hideequipment = $re->equipment.$hide2;
    $details = $re->details;
    $details1 = $re->details;
    $hideeetails = $re->details.$hide1;
    if (strlen($re->equipment) > 100) {
        $equipment = substr($re->equipment, 0, 50).'...';
        $equipment1 = substr($re->equipment, 0, 50).'...<span id="equipment_black_only" style="cursor:pointer;font-weight:bold"> Read more </span>';
    }
    if (strlen($re->details) > 100) {
        $details = substr($re->details, 0, 50);
        $details1 = substr($re->details, 0, 50).'...<span id="black_only" style="cursor:pointer;font-weight:bold"> Read more </span>';
    }
    $icon = '<i class="icon fa fa-cog fa-fw"></i>';
    $deleteicon = '<i class="icon fa fa-trash fa-fw "></i>';
    $viewicon = '<i class="icon fa fa-eye fa-fw"></i>';
    $linkurl1 = $CFG->wwwroot.'/course/format/classroom/edit_classroom.php?cid='.$cid.'&location_id='.$locationid;
    $linkurl2 = $CFG->wwwroot.'/course/format/classroom/delect_class.php?cid='.$cid.'&location_id='.$locationid;
    $link = '<a href='.$linkurl1.' title="Edit">'.$icon.'</a>&nbsp;';
    $link .= '<a href="'.$linkurl2.'" title="Delete">'.$deleteicon.'</a>';
    $link .= '<a href="#" data-toggle="modal" data-backdrop="static" data-target="#myModal'.$cid.'" title="View">'.
    $viewicon.'</a>';
    $viewicon.'</a>';
    if ($j >= 0) {
        $table->data[] = array($classroom, $seats, $details, $equipment, $link);
    }
    $j++;

    $popupcontent = '<div class="modal fade" id="myModal'.$cid.'" role="dialog">';
    $popupcontent .= '<div class="modal-dialog" style="max-width:650px;">';
    $popupcontent .= '<div class="modal-content">';
    $popupcontent .= '<div class="modal-header">';
    $popupcontent .= '<h4 class="modal-title"> Classroom : '.$classroom.'';
    $popupcontent .= '<button type="button" class="close" data-dismiss="modal">&times;</button></h4>';
    $popupcontent .= '</div> <div class="modal-body">';
    $popupcontent .= '<table style="margin-left: 10px;">';
    $popupcontent .= '<tr> <th>Location : </th> <td> '.$location.'</td> </tr>';
    $popupcontent .= '<tr> <th>Seats : </th> <td>'.$seats.'</td> </tr>';
    $popupcontent .= '<tr id="hidethis"> <th>Details : </th> <td> '.$details1.'</td> </tr>';
    $popupcontent .= '<tr id="hidethis1" class="hidden" valign="top"> <th>Details : </th> <td> '.$hideeetails.'</td> </tr>';
    $popupcontent .= '<tr id="equipment"> <th>Equipment : </th> <td> '.$equipment1.'</td> </tr>';
    $popupcontent .= '<tr id="equipment1" class="hidden" valign="top"> <th>Equipment : </th> <td> '.$hideequipment.'</td> </tr>';
    $popupcontent .= '</table>';
    $popupcontent .= '</div> <div class="modal-footer">';
    $popupcontent .= '<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>';
    $popupcontent .= '</div> </div> </div> </div>';
    echo $popupcontent;
}
echo "<style> .modal-body table th { width: 20%;}</style>";

echo html_writer::table($table);
echo html_writer::script('$(function(){$("#black_only, #hide").click(function(e){$(this).parents(".modal").find("#hidethis1, #hidethis").toggleClass("hidden");});$("#equipment_black_only, #hide_equp").click(function(e){$(this).parents(".modal").find("#equipment1, #equipment").toggleClass("hidden");});})');

if ($j == 0) {
    echo "<b style='color:#3A3D3E'>".get_string('nodatatodisplay', 'format_classroom')."</b><br>";
}
$burl = '/course/format/classroom/manage_classroom.php?location_id='.$locationid;
$baseurl = new moodle_url($burl, array('sort' => 'location', 'dir' => 'ASC', 'perpage' => $perpage));
echo $OUTPUT->paging_bar(count($results1), $page, $perpage, $baseurl);

if ($courseid != 0) {
    $curl = $CFG->wwwroot.'/my/';
    echo'<a class = "btn btn-primary" href = '.$curl.'style = "float:right;">'.
    get_string('backtothecourse', 'format_classroom') .' </a>';
}

echo $OUTPUT->footer();

