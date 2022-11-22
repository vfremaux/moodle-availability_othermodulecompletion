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
 * Days condition.
 *
 * @package     availability_othermodulecompletion
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2016 Valery Fremaux (http://www.mylearingfactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_othermodulecompletion;

use StdClass;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * days from course start condition.
 *
 * @package availability_othermodulecompletion
 * @copyright 2014 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {

    /** @var int Time (Unix epoch seconds) for condition. */
    private $daysfromstart;

    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct($structure) {

        // Get days.
        if (isset($structure->cmid)) {
            $this->cmidnumber = $structure->cmid;
        } else {
            $this->cmidnumber = '';
        }
    }

    /**
     * Saves the condition attributes.
     */
    public function save() {
        return (object)array('type' => 'cmidnumber', 'cmid' => $this->cmidnumber);
    }

    /**
     * Checks the target is available
     * @param bool $not
     * @param \core_availability\info $info
     * @param bool $grabthelot
     * @param int $userid
     * @return boolean
     */
    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        return $this->is_available_for_all($not);
    }

    /**
     * Checks the target is globally available
     * @param bool $not
     * @return boolean
     */
    public function is_available_for_all($not = false) {
        global $DB, $USER;

        // Check condition.
        if (!is_numeric($this->cmidnumber)) {
            $cm = $DB->get_record('course_modules', ['idnumber' => $this->cmidnumber]);
        } else {
            $cm = $DB->get_record('course_modules', ['id' => $this->cmidnumber]);
        }

        $allow = true;
        if ($cm) {
            if ($cpl = $DB->get_record('course_modules_completion', ['userid' => $USER->id, 'coursemoduleid' => $cm->id])) {
                $allow = $cpl->completionstate;
            } else {
                $allow = false;
            }
        }

        if ($not) {
            $allow = !$allow;
        }

        return $allow;
    }

    /**
     * Gets a condition description for printing
     * @param bool $full
     * @param bool $not
     * @param \core_availability\info $info
     * @return boolean
     */
    public function get_description($full, $not, \core_availability\info $info) {
        return $this->get_either_description($not, false);
    }

    /**
     * Gets a condition description for printing
     * @param bool $full
     * @param bool $not
     * @param \core_availability\info $info
     * @return string
     */
    public function get_standalone_description($full, $not, \core_availability\info $info) {
        return $this->get_either_description($not, true);
    }

    /**
     * Shows the description using the different lang strings for the standalone
     * version or the full one.
     *
     * @param bool $not True if NOT is in force
     * @param bool $standalone True to use standalone lang strings
     * @return string
     */
    protected function get_either_description($not, $standalone) {
        global $DB;

        $satag = $standalone ? 'short_' : 'full_';
        $a = new StdClass;
        if (is_numeric($this->cmidnumber)) {
            $cm = $DB->get_record('course_modules', ['id' => $this->cmidnumber]);
        } else {
            $cm = $DB->get_record('course_modules', ['idnumber' => $this->cmidnumber]);
        }
        if ($cm) {
            $coursename = $DB->get_field('course', str_replace('_', '', $satag).'name', ['id' => $cm->course]);
            $courseurl = new moodle_url('/course/view.php?', ['id' => $cm->course]);
            $a->coursetag = '<a href="'.$courseurl.'">'.$coursename.'</a>';
            $a->cmname = ($cm->idnumber) ? $cm->idnumber : "Module {$cm->id}";
            $desc = get_string($satag . 'moduleidnumber', 'availability_othermodulecompletion', $a);
        } else {
            $desc = get_string('misnamedmodule', 'availability_othermodulecompletion');
        }

        return $desc;
    }

    /**
     * @return int
     */
    protected function get_debug_string() {
        return $this->cmidnumber;
    }

    /**
     * What needs to be done after course restore.
     *
     * @param int $restoreid
     * @param int $courseid
     * @param \base_logger $logger
     * @param string $name
     * @return boolean
     */
    public function update_after_restore($restoreid, $courseid, \base_logger $logger, $name) {
        return true;
    }
}
