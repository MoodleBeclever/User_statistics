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
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/lib/completionlib.php');


// Gets all the registered user progresses.
function get_connected_users() {
    global $DB;
    $users = $DB->get_records('report_user_statistics');
    return $users;
}

// Generates correct row information for the display table.
function search_connected_users() {
    global $DB;
    $sql = "SELECT CURRENT_TIMESTAMP() AS fecha, COUNT(*) AS usuarios_conectados FROM {user}
        WHERE lastaccess >  UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MINUTE));";
    $connecteduser = $DB->get_records_sql($sql);
    foreach ($connecteduser as $connection) {
        $actualdate = new DateTime($connection->fecha);
        $timestamp = $actualdate->getTimestamp();
        $connection->fecha = $timestamp;
    }
    return $connecteduser;
}
// Generates correct row information for the display table.
function search_connected_usersfive() {
    global $DB;
    $sql = "SELECT COUNT(*) AS usuarios_conectados
        FROM {user}
        WHERE lastaccess >  UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 5 MINUTE));";
    $connecteduser = $DB->get_record_sql($sql);
    return $connecteduser->usuarios_conectados;
}
// Adds new connected users.
function add_newuser($user) {
    global $DB;
    $DB->insert_record('report_user_statistics', $user, false);
}
// Searchs the lower date.
function buscar_menor_fecha () {
    global $DB;
    $fecha = $DB->get_record('report_user_statistics', array('id' => '1'), 'date');
    return $fecha;
}
// Searchs de higher date.
function buscar_mayor_fecha () {
    global $DB;
    $sql = "SELECT date FROM {report_user_statistics} WHERE id=(SELECT max(id) FROM {report_user_statistics});";
    $fecha = $DB->get_record_sql($sql);
    return $fecha;
}
// Searches the date within the db.
function buscar_fecha ($fecha) {
    global $DB;
    $fechamin = buscar_menor_fecha();
    $fechamax = buscar_mayor_fecha();
    if ($fecha >= $fechamin->date && $fecha <= $fechamax->date) {
        return true;
    } else {
        return false;
    }
}
// Searches the connected users for the date given.
function get_connected_users_from_date($date) {
    global $DB;
    $sql = "SELECT * FROM {report_user_statistics} WHERE date >=?";
    return $DB->get_records_sql($sql, [$date]);
}
// Returns the chart values given the date.
function getting_chart_values($searcheddate = null, $fivemins = false, $table = false) {
    global $DB;
    $date;
    if ($searcheddate == null) {
        $temp = new DateTime('now', core_date::get_server_timezone_object());
        $date = new DateTime($temp->format('y-m-d'));
    } else {
        $date = new DateTime("@$searcheddate");
        $date->setTimezone(core_date::get_server_timezone_object());
    }

    $result = array();
    $all = get_connected_users_from_date($date->getTimestamp());
    foreach ($all as $row) {
        $checkdate = new DateTime ("@$row->date");
        $checkdate->setTimezone(core_date::get_server_timezone_object());
        if ($date->format('y') == $checkdate->format('y')) {
            if ($date->format('m') == $checkdate->format('m')) {
                if ($date->format('d') == $checkdate->format('d')) {
                    if ($table) {
                        $result[$checkdate->format('H:i')][1] = $row->connectedusers;
                        $result[$checkdate->format('H:i')][5] = $row->connecteduserfive;
                    } else {
                        if ($fivemins) {
                            $result[$checkdate->format('H:i')] = $row->connecteduserfive;
                        } else {
                            $result[$checkdate->format('H:i')] = $row->connectedusers;
                        }
                    }
                }
            }
        }
    }
    return $result;
}
// Provides the days of the current month.
function get_month_days_cm ($fecha) {
    $labels = array();
    $month = month_converter($fecha->month);
    $monthdays = cal_days_in_month(CAL_GREGORIAN, $fecha->month, $fecha->year);
    $i = 0;
    while ($i < $monthdays) {
        $i++;
        $labels[] = $i;
    }
    return $labels;
}
// Provides the days of the current month.
function get_month_days ($month) {
    $labels = array();
    $month = month_converter($month);
    $monthdays = cal_days_in_month(CAL_GREGORIAN, $month, $date->format('Y'));
    $day = $date->format('d');
    $i = 0;
    while ($i < 30) {
        if ($day <= $monthdays) {
            if (strlen($day) == 1) {
                $day = '0' . $day;
            }
            if (strlen($month) == 1) {
                $month = '0' . $month;
            }
            $labels[] = $day.'-'.$month;
            $day++;
            $i++;
        } else {
            $day = 1;
            $month++;
            $monthdays = cal_days_in_month(CAL_GREGORIAN, $month, $date->format('Y'));
        }
    }
    return $labels;
}
// Searches all timestamps on the db to find all the months.
function get_month_to_search() {
    global $DB;
    $sql = "SELECT date AS fecha FROM {report_user_statistics};";
    $dates = $DB->get_records_sql($sql);
    $result = array();
    foreach ($dates as $date) {
        $fecha = new DateTime("@$date->fecha");
        $result[$fecha->format('m')] = month_converter($fecha->format('m'));
    }
    return $result;
}
 // Searches all timestamps on the db to find all the years.
function get_years_to_search() {
    global $DB;
    $sql = "SELECT date AS fecha FROM {report_user_statistics};";
    $dates = $DB->get_records_sql($sql);
    $result = array();
    foreach ($dates as $date) {
        $fecha = new DateTime("@$date->fecha");
        $result[$fecha->format('Y')] = $fecha->format('Y');
    }
    return $result;
}
 // Searches the maximum users of the month day by day.
function get_month_max($date, $labels) {
    global $DB;

    $fechainicio = new DateTime("1-".$date->month."-".$date->year);
    $maxday = cal_days_in_month(CAL_GREGORIAN, $date->month, $date->year);
    $fechafin = new DateTime($maxday."-".$date->month."-".$date->year);
    $fechafin->setTime(23, 59, 59);
    $result = array();
    foreach ($labels as $label) {
        $result[] = 0;
    }

    $sql = "SELECT id, connectedusers, date FROM {report_user_statistics} WHERE date >= ? AND date <= ? ORDER BY date;";
    $todaslasconexiones = $DB->get_records_sql($sql, [$fechainicio->getTimestamp(), $fechafin->getTimestamp()]);
    $day = 0;
    $temp = array();
    $i = 1;
    foreach ($todaslasconexiones as $conexiones) {
        $fecha = new DateTime("@$conexiones->date");
        if (($fecha->format('m') == $date->month) && ($fecha->format('Y') == $date->year)) {
            if ($day == 0) {
                $day = $fecha->format('d');
                $temp[] = $conexiones->connectedusers;
                $i++;
            } else if ($day == $fecha->format('d')) {
                $temp[] = $conexiones->connectedusers;
                $i++;
            } else if ($day < $fecha->format('d')) {
                $key = array_search($day, $labels);
                $result[$key] = max($temp);
                $temp = array();
                $day = $fecha->format('d');
                $temp[] = $conexiones->connectedusers;
            }
            if (count($todaslasconexiones) == $i) {
                $key = array_search($day, $labels);
                $result[$key] = max($temp);
            } else if (count($temp) > 1) {
                $key = array_search($day, $labels);
                $result[$key] = max($temp);
            }
        }
    }
    return $result;
}

// Searches de minimum users of the month day by day (excludes 0).
function get_month_min($date, $labels) {
    global $DB;

    $fechainicio = new DateTime("1-".$date->month."-".$date->year);
    $maxday = cal_days_in_month(CAL_GREGORIAN, $date->month, $date->year);
    $fechafin = new DateTime($maxday."-".$date->month."-".$date->year);
    $fechafin->setTime(23, 59, 59);
    $result = array();
    foreach ($labels as $label) {
        $result[] = 0;
    }

    $sql = "SELECT id, connectedusers, date FROM {report_user_statistics} WHERE date >= ? AND date <= ? ORDER BY date;";
    $todaslasconexiones = $DB->get_records_sql($sql, [$fechainicio->getTimestamp(), $fechafin->getTimestamp()]);
    $day = 0;
    $temp = array();
    $i = 1;
    foreach ($todaslasconexiones as $conexiones) {
        $fecha = new DateTime("@$conexiones->date");
        if (($fecha->format('m') == $date->month) && ($fecha->format('Y') == $date->year)) {
            if ($day == 0) {
                $day = $fecha->format('d');
                $temp[] = $conexiones->connectedusers;
            } else if ($day == $fecha->format('d')) {
                $temp[] = $conexiones->connectedusers;
            } else if ($day < $fecha->format('d')) {
                foreach ($temp as $tempkey => $tempvalor) {
                    if ($tempvalor == 0) {
                        unset($temp[$tempkey]);
                    }
                }
                $key = array_search($day, $labels);
                $result[$key] = min($temp);
                $temp = array();
                $day = $fecha->format('d');
                $temp[] = $conexiones->connectedusers;
            }
            if (count($todaslasconexiones) == $i) {
                foreach ($temp as $tempkey => $tempvalor) {
                    if ($tempvalor == 0) {
                        unset($temp[$tempkey]);
                    }
                }
                $key = array_search($day, $labels);
                $result[$key] = min($temp);
            } else if (count($temp) > 1) {
                foreach ($temp as $tempkey => $tempvalor) {
                    if ($tempvalor == 0) {
                        unset($temp[$tempkey]);
                    }
                }
                $key = array_search($day, $labels);
                $result[$key] = min($temp);
            }
            $i++;
        }
    }
    return $result;
}
// Converts month number to name.
function month_converter($m) {
    $months = array('01' => get_string('month1', 'report_user_statistics'), '02' => get_string('month2', 'report_user_statistics'), '03' => get_string('month3', 'report_user_statistics'),
        '04' => get_string('month4', 'report_user_statistics'), '05' => get_string('month5', 'report_user_statistics'), '06' => get_string('month6', 'report_user_statistics'),
        '07' => get_string('month7', 'report_user_statistics'), '08' => get_string('month8', 'report_user_statistics'), '09' => get_string('month9', 'report_user_statistics'),
        '10' => get_string('month10', 'report_user_statistics'), '11' => get_string('month11', 'report_user_statistics'), '12' => get_string('month12', 'report_user_statistics'));
    if (is_numeric($m)) {
        return $months[$m];
    } else if (is_string($m)) {
        return array_search($m, $months);
    }
}
// Formats the chart values.
function get_correct_chart_parameters($all) {
    $actualhour;
    $tempvalues = array();
    $tempvalues30 = array();
    $result = array();
    $i = 1;
    foreach ($all as $time => $value) {
        $parts = explode(':', $time);
        if ($actualhour == null) {
            $actualhour = $parts[0];
        }
        if ($i != count($all)) {
            if ($parts[0] == $actualhour) {
                if ($parts[1] < 30) {
                    $tempvalues[] = $value;
                } else {
                    $tempvalues30[] = $value;
                }
            } else {
                $keymax = 'max-' . $actualhour;
                $keymin = 'min-' . $actualhour;
                if (count($tempvalues) > 0) {
                    $result[$keymax . ':00'] = max($tempvalues);
                    $result[$keymin . ':00'] = min($tempvalues);
                    unset($tempvalues);
                    $tempvalues = array();
                }
                if (count($tempvalues30) > 0) {
                    $result[$keymax . ':30'] = max($tempvalues30);
                    $result[$keymin . ':30'] = min($tempvalues30);
                    unset($tempvalues30);
                    $tempvalues30 = array();
                }
                if ($parts[1] < 30) {
                    $tempvalues[] = $value;
                } else {
                    $tempvalues30[] = $value;
                }
                $actualhour = $parts[0];
            }
            $i++;
        } else {
            $keyend;
            if ($parts[1] < 30) {
                $keyend = ':00';
            } else {
                $keyend = ':30';
            }
            if ($parts[0] == $actualhour) {

                $tempvalues[] = $value;
                $keymax = 'max-' . $actualhour . $keyend;
                $keymin = 'min-' . $actualhour . $keyend;
                $result[$keymax] = max($tempvalues);
                $result[$keymin] = min($tempvalues);
            } else {
                $keymax = 'max-' . $parts[0] . $keyend;
                $keymin = 'min-' . $parts[0] . $keyend;
                $result[$keymax] = $value;
                $result[$keymin] = $value;
            }
        }
    }
    return $result;
}
// Calculates the media day by day of the month.
function get_month_media($date, $labels) {
    global $DB;

    $fechainicio = new DateTime("1-".$date->month."-".$date->year);
    $maxday = cal_days_in_month(CAL_GREGORIAN, $date->month, $date->year);
    $fechafin = new DateTime($maxday."-".$date->month."-".$date->year);
    $fechafin->setTime(23, 59, 59);
    $result = array();
    foreach ($labels as $label) {
        $result[] = 0;
    }

    $sql = "SELECT id, connectedusers, date FROM {report_user_statistics} WHERE date >= ? AND date <= ? ORDER BY date;";
    $todaslasconexiones = $DB->get_records_sql($sql, [$fechainicio->getTimestamp(), $fechafin->getTimestamp()]);
    $day = 0;
    $temp = array();
    $i = 1;
    foreach ($todaslasconexiones as $conexiones) {
        $fecha = new DateTime("@$conexiones->date");
        if (($fecha->format('m') == $date->month) && ($fecha->format('Y') == $date->year)) {
            if ($day == 0) {
                $day = $fecha->format('d');
                $temp[] = $conexiones->connectedusers;
            } else if ($day == $fecha->format('d')) {
                $temp[] = $conexiones->connectedusers;
            } else if ($day < $fecha->format('d')) {
                $key = array_search($day, $labels);
                $result[$key] = array_sum($temp) / count($temp);
                $temp = array();
                $day = $fecha->format('d');
                $temp[] = $conexiones->connectedusers;
            }
            if (count($todaslasconexiones) == $i) {
                $key = array_search($day, $labels);
                $result[$key] = array_sum($temp) / count($temp);
            } else if (count($temp) > 1) {
                $key = array_search($day, $labels);
                $result[$key] = array_sum($temp) / count($temp);
            }
            $i++;
        }
    }
    return $result;
}
