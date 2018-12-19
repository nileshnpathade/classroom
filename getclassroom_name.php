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
 * Get classroom name.
 *
 * @package   format_classroom
 * @copyright 2018 eNyota Learning Pvt Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
$locationid = optional_param('locationid', 0, PARAM_INT);
global $DB;
require_login();
$getclassroom = $DB->get_records_sql('select id, classroom from {format_classroom}
    where location_id = ? AND isdeleted != ?', array($locationid, 0));
$arr = array(0 => 'Select Classroom');
foreach ($getclassroom as $key => $value) {
    $arr[$value->id] = $value->classroom;
}
echo json_encode($arr);