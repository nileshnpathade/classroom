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
$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_url('/../../../manage_location.php');
$PAGE->set_title(get_string('manage_location', 'format_classroom'));
$PAGE->set_heading(get_string('manage_location', 'format_classroom'));
$PAGE->set_pagelayout('course');
$PAGE->navbar->add('Site administration', new moodle_url('/admin/search.php'));
$PAGE->navbar->add('Plugins', new moodle_url('/admin/category.php?category=modules'));
$PAGE->navbar->add('Configure', new moodle_url('/admin/settings.php?section=formatsettingclassroom'));
$PAGE->navbar->add('Manage Location');
$PAGE->requires->jquery();
require_login();
if (!is_siteadmin()) {
    $sqlrole = "select * from {role_assignments} where userid = ".$USER->id." and (roleid != 1 OR roleid != 2)  and contextid = 1";
    $roleassignments = $DB->get_records_sql($sqlrole, array());
    if (empty($roleassignments)) {
        redirect($CFG->wwwroot);
    }
}
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$courseid = optional_param('cid', 0, PARAM_INT);
echo $OUTPUT->header();
$addurl = 'course/format/classroom/add_location.php';

$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/course/format/classroom/search.js'));

$out = '';
$out .= html_writer::empty_tag('input', array('type' => 'text',
'class' => 'form-control', 'name' => 'search', 'id' => 'search',
'placeholder' => 'Search', 'style' => 'max-width:20%;float:right;'));
echo $out."";

echo '<a class="btn btn-primary" href="'.$CFG->wwwroot.'/'.$addurl.'" title="Add Location" style="float:left;">'.
get_string('addlocation', 'format_classroom') .' </a><br/><br/><br/>';

$start = $page * $perpage;
$results1 = $DB->get_records_sql("select * from {classroom_location} where isdeleted != 0", array());
$results = $DB->get_records_sql("select * from {classroom_location} where isdeleted != 0 LIMIT $start, $perpage" ,  array());
echo "<style> td.cell.c3.lastcol{ padding-left:0px; } </style>";
$table = new html_table();
$table->id = 'myTable';
$table->head = array('Location Name', 'Address', 'Classroom', 'Actions');
$i = 1;$j = 0;
foreach ($results as $re) {
    $id = $i++;
    $cid = $re->id;
    $location = $re->location;
    $address = $re->address;
    $phoneno = $re->phoneno;
    $emailid = $re->emailid;
    $classroomdisplayname = $DB->get_records('classroom', array('location_id' => $cid, 'isdeleted' => 1));
    $addurl = $CFG->wwwroot.'/course/format/classroom/add_classroom.php?location_id='.$cid;
    if (empty($classroomdisplayname)) {
        $clurl = $CFG->wwwroot.'/course/format/classroom/manage_classroom.php?location_id='.$cid;
        $classroomlike = '<a href="'.$addurl.'"  title="Add Classroom">Add Classroom</a>';
    } else {
        $clurl2 = $CFG->wwwroot.'/course/format/classroom/manage_classroom.php?location_id='.$cid;
        $count = count($classroomdisplayname);
        $classroomlike = '<a href="'.$clurl2.'">'.$count.'</a>';
        $classroomlike .= '&nbsp;&nbsp;<a href="'.$addurl.'" title="Add Classroom"><i class="icon fa fa-desktop"></i></a>';
    }
    $link1 = $CFG->wwwroot.'/course/format/classroom/edit_location.php?cid='.$cid;
    $link2 = $CFG->wwwroot.'/course/format/classroom/delete_loc.php?cid='.$cid;
    $icon = '<i class="icon fa fa-cog fa-fw"></i>';
    $delecticon = '<i class="icon fa fa-trash fa-fw"></i>';
    $viewicon = '<i class="icon fa fa-eye fa-fw"></i>';
    $link = '<a href="'.$link1.'" title="Edit">'.$icon.'</a>&nbsp;';
    $link .= '<a href="'.$link2.'" title="Delete">'.
    $delecticon.'</a>';
    $link .= '<a href="#" data-toggle="modal" data-backdrop="static" data-target="#myModal'.$cid.'" title="View">'.
    $viewicon.'</a>';
    if ($j >= 0) {
        $table->data[] = array($location, $address, $classroomlike, $link);
    }
    $j++;

    $popupcontent = '<div class="modal fade" id="myModal'.$cid.'" role="dialog">';
    $popupcontent .= '<div class="modal-dialog" style="max-width:750px;">';
    $popupcontent .= '<div class="modal-content">';
    $popupcontent .= '<div class="modal-header">';
    $popupcontent .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
    $popupcontent .= '<h4 class="modal-title"> Location : '.$location.'</h4>';
    $popupcontent .= '</div> <div class="modal-body">';
    $popupcontent .= '<table style="margin-left: 10px;">';
    $popupcontent .= '<tr> <th>Address : </th> <td>'.$address.'</td> </tr>';
    $popupcontent .= '<tr> <th>Email ID  : </th> <td>'.$emailid.'</td> </tr>';
    $popupcontent .= '<tr> <th>Phone No : </th> <td>'.$phoneno.'</td> </tr>';
    $popupcontent .= '</table> </div> <div class="modal-footer">';
    $popupcontent .= '<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>';
    $popupcontent .= '</div> </div> </div> </div>';
    echo $popupcontent;
}
echo html_writer::table($table);

if ($j == 0) {
    echo "<b style='color:#3A3D3E'>".get_string('nodatatodisplay', 'format_classroom')."</b><br>";
}
$burl = '/course/format/classroom/manage_location.php';
$baseurl = new moodle_url($burl, array('sort' => 'location', 'dir' => 'ASC', 'perpage' => $perpage));
echo $OUTPUT->paging_bar(count($results1), $page, $perpage, $baseurl);
echo $OUTPUT->footer();