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

    // Only the virtual `stamped` area is addressable. The real `content` area, which
    // holds the untouched upload, has no route out of here at all - not for any user,
    // not with any URL. Everything served from this plugin passes through the
    // watermarker below, so there is no branch where the original can escape.
    if ($filearea !== 'stamped') {
        return false;
    }

    require_login($course, false, $cm);
    require_capability('mod/pdfsecure:view', $context);

    // The filename identifies WHICH document; WHO is reading it comes from the
    // session, never from the URL. There is therefore nothing in the address to
    // tamper with in order to obtain a copy stamped with somebody else's name.
    // Guarded rather than assumed: a request for the area root leaves $args empty,
    // and array_pop() on that returns null, which clean_param() is not required to
    // survive on PHP 8.
    array_shift($args);                 // itemid placeholder, always 0
    if (empty($args)) {
        return false;
    }
    $filename = clean_param((string)array_pop($args), PARAM_FILE);
    if ($filename === '') {
        return false;
    }

    $fs = get_file_storage();
    $source = $fs->get_file($context->id, 'mod_pdfsecure', 'content', 0, '/', $filename);
    if (!$source) {
        return false;
    }

    // The whole document is held in memory while it is rendered, and now that
    // happens on every view rather than once. An oversized upload would therefore
    // exhaust the PHP memory limit on each read instead of failing once, so refuse
    // it up front and leave a trace explaining why.
    $maxbytes = (int)(get_config('mod_pdfsecure', 'maxstampbytes') ?: 41943040);
    if ($source->get_filesize() > $maxbytes) {
        debugging('pdfsecure: refusing to stamp ' . $source->get_filename() . ' ('
            . display_size($source->get_filesize()) . ' exceeds the '
            . display_size($maxbytes) . ' limit)', DEBUG_DEVELOPER);
        send_file_not_found();
    }

    try {
        $content = \mod_pdfsecure\local\watermarker::render_for($source, $USER, (int)$cm->id);
    } catch (\Throwable $e) {
        // Fail closed. Serving the original "just this once" is precisely how an
        // unstamped copy escapes, and FPDI legitimately refuses some sources.
        debugging('pdfsecure: stamping failed for file ' . $source->get_id() . ': '
            . $e->getMessage(), DEBUG_DEVELOPER);
        send_file_not_found();
    }

    // Accept-Ranges: none is load-bearing, not tidiness. Each request regenerates the
    // document, and FPDI output is not byte-identical between runs, so a browser
    // fetching byte ranges would stitch pieces of different generations into one
    // corrupt file. Refusing ranges makes the viewer fetch it whole, once.
    header('Content-Type: application/pdf');
    // RFC 6266: the plain form stays readable for ASCII names, and filename* carries
    // the real one for anything else. rawurlencode() alone would show the reader a
    // percent-encoded name in the save dialog.
    header('Content-Disposition: inline; filename="'
        . str_replace(['"', "\r", "\n"], '', $filename) . '"; filename*=UTF-8\'\''
        . rawurlencode($filename));
    header('Content-Length: ' . strlen($content));
    header('Accept-Ranges: none');
    // Per-user and per-view: it must not be held by a shared cache, and the browser
    // must not reuse it either, or the timestamp stops reflecting the actual read.
    header('Cache-Control: private, no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('X-Content-Type-Options: nosniff');

    echo $content;
    die;
}
