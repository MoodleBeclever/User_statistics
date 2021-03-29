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
 * @copyright  2020 BeClever - Laura Crespo Carreto
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');
admin_externalpage_setup('user_statistics', '', null, '', array('pagelayout' => 'report'));
require_once('locallib.php');
require_once('form.php');
require_once('form_month.php');

$page    = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 30, PARAM_INT);    // How many per page.
$sort    = optional_param('sort', 'timemodified', PARAM_ALPHA);
$dir     = optional_param('dir', 'DESC', PARAM_ALPHA);
$download = optional_param('download', '', PARAM_ALPHA);

raise_memory_limit(MEMORY_EXTRA);
core_php_time_limit::raise();


$PAGE->set_title(get_string('pluginname', 'report_user_statistics'));


$table = new flexible_table('reprot_user_statistics_flextab');
$exportfilename = get_string('exportfilename', 'report_user_statistics');
if (!$table->is_downloading($download, $exportfilename)) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('plugindesc', 'report_user_statistics'));
}

$allchart = array();
$fechabuscada;
$mform = new search_form();
if (!$table->is_downloading()) {
    if ($fromform = $mform->get_data()) {
        // In this case you process validated data. $mform->get_data() returns data posted in form.
        $allchart = getting_chart_values($fromform->searchdate);
        $allchartfive = getting_chart_values($fromform->searchdate, true);
        $fechabuscada = new DateTime("@$fromform->searchdate");
        $fechabuscada->setTimezone(core_date::get_server_timezone_object());

        $mform->display();
    } else {
        // This branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
        // or on the first display of the form.
        $allchart = getting_chart_values();
        $allchartfive = getting_chart_values(null, true);

        // Set default data (if any).
        $mform->set_data($toform);
        // Displays the form.
        $mform->display();
    }
}
if ($fechabuscada == null) {
    $temp = new DateTime('now', core_date::get_server_timezone_object());
    $fechabuscada = new DateTime($temp->format('y-m-d'));
}
if (!$table->is_downloading()) {
    echo '<h3>'.$fechabuscada->format('d/m/y').'</h3>';
    if (class_exists('core\chart_line')) {
        $chart = new core\chart_line();
        $chartmin = array();
        $chartmax = array();
        $chartfivemin = array();
        $chartfivemax = array();
        $labels = array();
        $chartvalues = get_correct_chart_parameters($allchart);
        $chartvaluesfive = get_correct_chart_parameters($allchartfive);
        foreach ($chartvalues as $key => $count) {
            $keyparts = explode('-', $key);
            if ($keyparts[0] == 'max') {
                $labels[] = $keyparts[1];
                $chartmax[] = $count;
            } else {
                $chartmin[] = $count;
            }
        }
        foreach ($chartvaluesfive as $keyfive => $countfive) {
            $keyparts = explode('-', $keyfive);
            if ($keyparts[0] == 'max') {
                $chartfivemax[] = $countfive;
            } else {
                $chartfivemin[] = $countfive;
            }
        }
        $serie1 = new core\chart_series(get_string('labelmin1', 'report_user_statistics'), $chartmin);
        $serie2 = new core\chart_series(get_string('labelmax1', 'report_user_statistics'), $chartmax);
        $serie3 = new core\chart_series(get_string('labelmin5', 'report_user_statistics'), $chartfivemin);
        $serie4 = new core\chart_series(get_string('labelmax5', 'report_user_statistics'), $chartfivemax);

        $chart->set_smooth(true);
        $chart->add_series($serie1);
        $chart->add_series($serie2);
        $chart->add_series($serie3);
        $chart->add_series($serie4);
        $chart->set_labels($labels);
        echo $OUTPUT->render($chart);
    }
    echo '<h3>'.get_string('h3', 'report_user_statistics').'</h3>';
    $monthform = new month_form();
    $chartvalues = new stdClass();
    if ($fromform = $monthform->get_data()) {
	    // In this case you process validated data. $mform->get_data() returns data posted in form.
        $chartvalues->month = $fromform->month;
        if (property_exists($fromform, 'year')) {
            $chartvalues->year = $fromform->year;
        } else {
            $currentyear = new DateTime('now', core_date::get_server_timezone_object());
            $chartvalues->year = $currentyear->format('Y');
        }
        $monthform->display();

    } else {
        // This branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
        // or on the first display of the form.
        $currentmonth = new DateTime('now', core_date::get_server_timezone_object());
        $chartvalues->month = $currentmonth->format('m');
        $chartvalues->year = $currentmonth->format('Y');

        // Set default data (if any).
        $monthform->set_data($toform);
        // Displays the form.
        $monthform->display();
    }
    if (class_exists('core\chart_bar')) {
        $chartmonth = new core\chart_bar();
        $labels2 = get_month_days_cm($chartvalues);
        $chartbarmax = get_month_max($chartvalues, $labels2);
        $chartbarmin = get_month_min($chartvalues, $labels2);
        $chartlinemedia = get_month_media($chartvalues, $labels2);
        $serie5 = new core\chart_series(get_string('labelmin', 'report_user_statistics'), $chartbarmin);
        $serie6 = new core\chart_series(get_string('labelmax', 'report_user_statistics'), $chartbarmax);
        $serie7 = new core\chart_series(get_string('labelmid', 'report_user_statistics'), $chartlinemedia);
        $serie7->set_type(\core\chart_series::TYPE_LINE);


        $chartmonth->add_series($serie7);
        $chartmonth->add_series($serie6);
        $chartmonth->add_series($serie5);
        $chartmonth->set_labels($labels2);
        echo $OUTPUT->render($chartmonth);
    }

    echo '<h3>'.get_string('h3', 'report_user_statistics').'</h3>';
}

// Setting up table headers.

    $table->define_columns(array('date', 'connectedusers', 'connecteduserfive'));
    $table->define_headers(array(get_string('coldate', 'report_user_statistics'),
        get_string('colusers', 'report_user_statistics'), get_string('colfive', 'report_user_statistics')));
    $table->define_baseurl($CFG->wwwroot.'/report/user_statistics/index.php');
    $table->set_attribute('class', 'generaltable');
    $table->set_attribute('id', 'connectedusers_table');

    $table->pageable(true);
    $table->is_downloading($download, 'test', 'usuariosconectados');

    $table->setup();

    $users = getting_chart_values($fechabuscada->getTimestamp(), false, true);
    foreach ($users as $key => $value) {
        $date = new DateTime();
        $date = $fechabuscada->format('d/m/Y'). ' ' . $key;
        $row = array('date' => $date, 'connectedusers' => $value[1], 'connecteduserfive' => $value[5]);
        $table->add_data($row);
    }
    $table->finish_output();

    if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
    }
