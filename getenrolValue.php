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
global $PAGE, $CFG, $DB, $OUTPUT;
$classroomid  = optional_param('classroom_id', 0, PARAM_INT);
require_login();
$classrooms = $DB->get_record_sql('select * from {classroom} where id = ? AND isdeleted != ?', array($classroomid, 0));

echo '<div class = "form-group row  fitem">
<div class = "col-md-3">
    <span class = "pull-xs-right text-nowrap">
    </span>
    <span class = "col-form-label d-inline-block ">
        Maximum Enrollments
    </span>
</div>
<div class = "col-md-9 form-inline felement" data-fieldtype = "static">
    <div class = "form-control-static">';
    echo $classrooms->seats;
    echo '</div>
    <div class = "form-control-feedback" id = "id_error_maxenrol_val" style = "display: none;"></div>
</div>
</div>';