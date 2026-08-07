<?php
defined('MOODLE_INTERNAL') || die();

function pdfsecure_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO: return true;
        case FEATURE_SHOW_DESCRIPTION: return true;
        case FEATURE_BACKUP_MOODLE2: return true;
        default: return null;
    }
}

function pdfsecure_add_instance($pdfsecure, $mform = null) {
    global $DB;
    $pdfsecure->timecreated = time();
    $pdfsecure->timemodified = time();
    $pdfsecure->id = $DB->insert_record('pdfsecure', $pdfsecure);

    if ($mform) {
        $context = context_module::instance($pdfsecure->coursemodule);
        file_save_draft_area_files($pdfsecure->pdf_file, $context->id, 'mod_pdfsecure', 'content', 0, array('subdirs' => 0, 'maxfiles' => 1));
    }
    return $pdfsecure->id;
}

function pdfsecure_update_instance($pdfsecure, $mform = null) {
    global $DB;
    $pdfsecure->timemodified = time();
    $pdfsecure->id = $pdfsecure->instance;
    $DB->update_record('pdfsecure', $pdfsecure);

    if ($mform) {
        $context = context_module::instance($pdfsecure->coursemodule);
        file_save_draft_area_files($pdfsecure->pdf_file, $context->id, 'mod_pdfsecure', 'content', 0, array('subdirs' => 0, 'maxfiles' => 1));
    }
    return true;
}

function pdfsecure_delete_instance($id) {
    global $DB;
    if (!$pdfsecure = $DB->get_record('pdfsecure', array('id' => $id))) {
        return false;
    }
    $DB->delete_records('pdfsecure', array('id' => $pdfsecure->id));
    return true;
}

function pdfsecure_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $USER;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    // The uploaded original is NEVER served. Only the per-user stamped derivative
    // leaves this plugin.
    //
    // This single check is what makes the watermark meaningful. Previously this
    // function served the 'content' area directly, so the raw unstamped PDF stayed
    // addressable by anyone who read the page source - every front-end control in
    // view.php was decoration on top of an open door.
    if ($filearea !== \mod_pdfsecure\local\watermarker::AREA) {
        return false;
    }

    require_login($course, false, $cm);
    require_capability('mod/pdfsecure:view', $context);

    $itemid = (int)array_shift($args);
    $filename = array_shift($args);

    // itemid IS the user id. Without this, any enrolled user could fetch the copy
    // stamped with somebody else's name simply by editing the number in the URL -
    // which would let a leaker frame a colleague.
    if ($itemid !== (int)$USER->id) {
        return false;
    }

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_pdfsecure', $filearea, $itemid, '/', $filename);
    if (!$file) {
        return false;
    }

    // Lifetime 0 and private cacheability: the response body differs per user, so it
    // must never be held in a shared cache. forcedownload is pinned to false so
    // appending ?forcedownload=1 cannot turn the view into an attachment.
    $options['cacheability'] = 'private';
    send_stored_file($file, 0, 0, false, $options);
}
