<?php
// This file is part of the tool_certificate for Moodle - http://moodle.org/
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
 * This file contains the form for loading certificate templates.
 *
 * @package    tool_certificate
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_certificate;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir . '/formslib.php');

/**
 * The form for loading certificate templates.
 *
 * @package    tool_certificate
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class load_template_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        global $DB;

        $mform =& $this->_form;

        $mform->addElement('header', 'loadtemplateheader', get_string('loadtemplate', 'tool_certificate'));

        $templates = $DB->get_records_menu('tool_certificate_templates',
            array('contextid' => \context_system::instance()->id), 'name ASC', 'id, name');
        if ($templates) {
            $group = array();
            $group[] = $mform->createElement('select', 'ltid', '', $templates);
            $group[] = $mform->createElement('submit', 'loadtemplatesubmit', get_string('load', 'tool_certificate'));
            $mform->addElement('group', 'loadtemplategroup', '', $group, '', false);
            $mform->setType('ltid', PARAM_INT);
        } else {
            $msg = \html_writer::tag('div', get_string('notemplates', 'tool_certificate'), array('class' => 'alert'));
            $mform->addElement('static', 'notemplates', '', $msg);
        }
    }
}
