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
 * Settings for format_classroom
 *
 * @package    format_classroom
 * @copyright  2018 eNyota Learning Pvt Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

$section = optional_param('section', array(), PARAM_TEXT);
if ($ADMIN->fulltree) {
    $link = '<div style="padding:4px;">
        <style>
            #dashboard_classroom a {
                padding:25px 55px 25px 55px;
            }
            #dashboard_classroom a:hover {
                color:#444;
            }
        </style>
        <table id="dashboard_classroom" style="width:100%;text-align:center;" border="1px">
            <tr>
                <td style="padding:25px;background-color:#CCC;">
                    <a href="'.$CFG->wwwroot.'/course/format/classroom/manage_location.php">
                        Manage Location
                    </a>
                </td>
            </tr>
        </table>
    </div>';
    $settings->add(new admin_setting_heading('format_classroom', '', $link));
}