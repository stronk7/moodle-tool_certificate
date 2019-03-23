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
 * Unit tests for code element.
 *
 * @package    certificateelement_code
 * @category   test
 * @copyright  2018 Daniel Neis Araujo <daniel@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for code element.
 *
 * @package    certificateelement_code
 * @group      tool_certificate
 * @copyright  2018 Daniel Neis Araujo <daniel@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_certificate_code_element_test_testcase extends advanced_testcase {

    /**
     * Test set up.
     */
    public function setUp() {
        $this->resetAfterTest();
    }

    /**
     * Get certificate generator
     * @return tool_certificate_generator
     */
    protected function get_generator() : tool_certificate_generator {
        return $this->getDataGenerator()->get_plugin_generator('tool_certificate');
    }

    /**
     * Test render_html
     */
    public function test_render_html_content() {
        $certificate1 = $this->get_generator()->create_template((object)['name' => 'Certificate 1']);
        $pageid = $certificate1->add_page();
        $e = $this->get_generator()->new_element($pageid, 'code');
        $this->assertFalse(empty($e->render_html()));
    }

    /**
     * Test save_unique_data
     */
    public function test_save_unique_data() {
        global $DB;
        $certificate1 = $this->get_generator()->create_template((object)['name' => 'Certificate 1']);
        $pageid = $certificate1->add_page();
        $e = $this->get_generator()->new_element($pageid, 'code');
        $newdata = (object)['display' => \certificateelement_code\element::DISPLAY_CODE];
        $expected = json_encode($newdata);
        $e->save($newdata);
        $el = $DB->get_record('tool_certificate_elements', ['id' => $e->get_id()]);
        $this->assertEquals($expected, $el->data);
    }
}
