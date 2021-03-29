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
 * TODO: File description
 *
 * @package    report
 * @subpackage user_statistics
 * @copyright  2021 BeClever - Laura Crespo Carreto
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_report_user_statistics_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2020050600) {

        // Define field connecteduserfive to be added to local_connected_users.
        $table = new xmldb_table('local_connected_users');
        $field = new xmldb_field('connecteduserfive', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'connectedusers');

        // Conditionally launch add field connecteduserfive.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // User_statistics savepoint reached.
        upgrade_plugin_savepoint(true, 2020050600, 'report', 'user_statistics');
    }
    if ($oldversion < 2021030800) {

        // Define table local_connected_users to be renamed to report_user_statistics.
        $table = new xmldb_table('local_connected_users');

        // Launch rename table for local_connected_users.
        $dbman->rename_table($table, 'report_user_statistics');

        // User_statistics savepoint reached.
        upgrade_plugin_savepoint(true, 2021030800, 'report', 'user_statistics');
    }
    return true;
}