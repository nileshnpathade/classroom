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
 * @since 3.4.2
 * @package format_classroom
 * @copyright eNyota Learning Pvt Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
class config_assignuser_form extends moodleform {
    public function definition() {
        global $DB;
        $mform =& $this->_form;
        $seesionid = $this->_customdata['session_id'];
        $courseid = $this->_customdata['courseid'];
        $mform->addElement('header', 'assginuser', get_string('assginuser', 'format_classroom'));

        $mform->addElement('hidden', 'seesionid', $seesionid);
        $mform->setType('seesionid', PARAM_INT);

        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);
        $objs = array();

        $sql = "SELECT u.id as userid, c.fullname, u.username, u.firstname, u.lastname, u.email
            FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
            WHERE ra.userid = u.id
            AND ra.contextid = cxt.id
            AND cxt.contextlevel = 50
            AND cxt.instanceid = c.id
            AND c.id = '$courseid'
            AND u.id != 1 AND u.id != 2
            AND roleid = 5
            AND  u.id  IN (SELECT ca.userid from {classroom_assignuser} as ca
            where ca.session_id = $seesionid)";

        $getassinguser = $DB->get_records_sql($sql, array());
        $assignusers = array('' => 'Assign users');
        foreach ($getassinguser as $key => $user) {
            $assignusers[$user->userid] = $user->username;
        }

        $objs[0] =& $mform->createElement('select', 'assignusers',
        get_string('users', 'format_classroom'), $assignusers, 'size="15"');
        $objs[0]->setMultiple(true);

        $sql = "SELECT u.id as userid, c.fullname, u.username, u.firstname, u.lastname, u.email
            FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
            WHERE ra.userid = u.id
            AND ra.contextid = cxt.id
            AND cxt.contextlevel = 50
            AND cxt.instanceid = c.id
            AND c.id = '$courseid'
            AND u.id != 1 AND u.id != 2
            AND roleid = 5
            AND  u.id NOT IN (SELECT ca.userid from {classroom_assignuser} as ca
            where ca.session_id = $seesionid)";

        $getassinguser = $DB->get_records_sql($sql, array());
        $ausers = array('' => 'Unassign users');
        foreach ($getassinguser as $key => $user) {
            $ausers[$user->userid] = $user->username;
        }

        $objs[1] =& $mform->createElement('select', 'ausers',
        get_string('users', 'format_classroom'), $ausers, 'size="15"');
        $objs[1]->setMultiple(true);

        $grp =& $mform->addElement('group', 'usersgrp',
        get_string('users', 'format_classroom'), $objs, ' ', false);
        $this->add_action_buttons(true);
    }
}
