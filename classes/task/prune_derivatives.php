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

namespace mod_pdfsecure\task;

/**
 * Deletes watermarked copies that have been sitting around for a while.
 *
 * One copy is generated per user per document, so the file area grows with
 * users x documents x time and has no natural ceiling. On a site with a few
 * hundred learners and a few dozen handbooks that reaches tens of gigabytes,
 * which is a disk-full outage for the whole platform rather than a problem
 * confined to this plugin.
 *
 * Pruning is by age rather than by last access on purpose. Moodle's file API
 * records creation and modification, not reads, so tracking access would mean a
 * database write on every single view. Regenerating a stamped copy costs
 * hundredths of a second, so pruning one that is still in use is cheaper than
 * the bookkeeping needed to avoid it - the reader never notices.
 *
 * @package    mod_pdfsecure
 * @copyright  2026 Aditek / Angel Aligner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class prune_derivatives extends \core\task\scheduled_task {

    /**
     * @return string
     */
    public function get_name(): string {
        return get_string('taskprune', 'mod_pdfsecure');
    }

    /**
     * Removes derivatives older than the configured retention.
     */
    public function execute(): void {
        global $DB;

        $days = (int)get_config('mod_pdfsecure', 'retentiondays');
        if ($days <= 0) {
            mtrace('pdfsecure prune: retention disabled, nothing to do');
            return;
        }

        $cutoff = time() - ($days * DAYSECS);
        $fs = get_file_storage();

        $rs = $DB->get_recordset_select('files',
            "component = :component AND filearea = :filearea
             AND filename <> '.' AND timecreated < :cutoff",
            ['component' => 'mod_pdfsecure', 'filearea' => 'watermarked', 'cutoff' => $cutoff],
            '', 'id');

        $count = 0;
        $bytes = 0;
        foreach ($rs as $record) {
            if ($file = $fs->get_file_by_id($record->id)) {
                $bytes += $file->get_filesize();
                $file->delete();
                $count++;
            }
        }
        $rs->close();

        mtrace("pdfsecure prune: removed {$count} stamped copy(ies) older than {$days} day(s), "
            . display_size($bytes) . " reclaimed");
    }
}
