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

namespace report_user_statistics\task;

defined('MOODLE_INTERNAL') || die();

class user_statistics extends \core\task\scheduled_task
{

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('task', 'report_user_statistics');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/report/user_statistics/locallib.php');
        $connectedusers = search_connected_users();
        $connectedusersfive = search_connected_usersfive();
        foreach ($connectedusers as $connections) {
            $newconnections = new \stdClass();
            $newconnections->date = $connections->fecha;
            $newconnections->connectedusers = $connections->usuarios_conectados;
            $newconnections->connecteduserfive = $connectedusersfive;
            add_newuser($newconnections);
        }
    }
}
