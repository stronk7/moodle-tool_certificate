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
 * Code fragment to define the version of the certificate tool
 *
 * @package    tool_certificate
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

$plugin->version   = 2019030710;
$plugin->requires  = 2019012401;
$plugin->cron      = 0; // Period for cron to check this module (secs).
$plugin->component = 'tool_certificate';

$plugin->maturity  = MATURITY_ALPHA;
$plugin->release = '0.2.0';

$plugin->dependencies   = array(
    'tool_tenant' => '2019030700',
    'tool_wp' => '2019030700',
);
