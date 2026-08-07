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
 * Site-wide watermark appearance.
 *
 * These were hardcoded constants. They are settings because the right balance
 * between "clearly marked" and "still readable" depends on the documents, and
 * nobody should have to edit PHP to retune it. Changing any of them invalidates
 * the cached derivatives automatically - the settings are part of the cache key.
 *
 * @package    mod_pdfsecure
 * @copyright  2026 Aditek / Angel Aligner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configselect(
        'mod_pdfsecure/tone',
        get_string('settingtone', 'mod_pdfsecure'),
        get_string('settingtone_desc', 'mod_pdfsecure'),
        232,
        [
            190 => get_string('tonestrong', 'mod_pdfsecure'),
            210 => get_string('tonemedium', 'mod_pdfsecure'),
            232 => get_string('tonelight', 'mod_pdfsecure'),
            243 => get_string('tonefaint', 'mod_pdfsecure'),
        ]
    ));

    $settings->add(new admin_setting_configselect(
        'mod_pdfsecure/fontsize',
        get_string('settingfontsize', 'mod_pdfsecure'),
        get_string('settingfontsize_desc', 'mod_pdfsecure'),
        11,
        [8 => '8 pt', 10 => '10 pt', 11 => '11 pt', 14 => '14 pt', 18 => '18 pt']
    ));

    $settings->add(new admin_setting_configcheckbox(
        'mod_pdfsecure/includedate',
        get_string('settingincludedate', 'mod_pdfsecure'),
        get_string('settingincludedate_desc', 'mod_pdfsecure'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'mod_pdfsecure/showfooter',
        get_string('settingshowfooter', 'mod_pdfsecure'),
        get_string('settingshowfooter_desc', 'mod_pdfsecure'),
        1
    ));
}
