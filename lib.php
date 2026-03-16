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
    require_login($course, false, $cm);
    $itemid = array_shift($args);
    $filename = array_shift($args);
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_pdfsecure', $filearea, $itemid, '/', $filename);
    if (!$file) {
        return false;
    }
    send_stored_file($file, 0, 0, $forcedownload, $options);
}
