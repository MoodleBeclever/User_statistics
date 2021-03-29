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
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once('locallib.php');

class month_form extends moodleform {
    // Add elements to form.
    public function definition() {
        global $CFG;

        $mform = $this->_form; // Don't forget the underscore!

        $months = get_month_to_search();
        $years = get_years_to_search();

        if (count($years) > 1) {
            $selectarray = array();
            $selectarray[] =& $mform->createElement('select', 'month', '', $months);
            $selectarray[] =& $mform->createElement('select', 'year', '', $years);
            $mform->addGroup($selectarray, 'datefilter', get_string('datefilter_month', 'report_user_statistics'), array(' '), false);
        } else {
            $mform->addElement('select', 'month', get_string('select_month', 'report_user_statistics'), $months);
        }

        $this->add_action_buttons(false, get_string('button_month', 'report_user_statistics'));

    }
    // Custom validation should be added here.
    function validation($data, $files) {
        global $CFG, $DB;
        $errors = array();

        return $errors;
    }
}