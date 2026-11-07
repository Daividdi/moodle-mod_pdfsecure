<?php
require('../../config.php');
require_once('lib.php');
require_once($CFG->libdir . '/completionlib.php');

$id = required_param('id', PARAM_INT); // ID do Course Module

if (! $cm = get_coursemodule_from_id('pdfsecure', $id)) {
    throw new \moodle_exception('invalidcoursemodule');
}
if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
    throw new \moodle_exception('coursemisconf');
}
if (! $pdfsecure = $DB->get_record('pdfsecure', array('id' => $cm->instance))) {
    throw new \moodle_exception('invalidcoursemodule');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/pdfsecure:view', $context);

$pdfs = \mod_pdfsecure\local\content_files::get_pdfs($context);
$filekey = optional_param('file', '', PARAM_RAW);
if (!is_string($filekey)) {
    throw new \moodle_exception('errorfileinvalid', 'mod_pdfsecure');
}
$selectedfile = null;
if ($filekey !== '') {
    $selectedfile = \mod_pdfsecure\local\content_files::resolve($context, (string)$filekey);
} else if (count($pdfs) === 1) {
    $selectedfile = reset($pdfs);
}

$urlparams = ['id' => $cm->id];
if ($filekey !== '') {
    $urlparams['file'] = $filekey;
}
$PAGE->set_url('/mod/pdfsecure/view.php', $urlparams);
$PAGE->set_title(format_string($pdfsecure->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$event = \mod_pdfsecure\event\course_module_viewed::create([
    'objectid' => $pdfsecure->id,
    'context' => $context,
]);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('pdfsecure', $pdfsecure);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);
if ($selectedfile && !empty($pdfsecure->completionreadall)) {
    $PAGE->requires->js_call_amd('mod_pdfsecure/progress', 'init', [$cm->id, $selectedfile->get_filename()]);
}

echo $OUTPUT->header();

// The activity header already shows the name (Moodle 4+), so no second heading here.

if ($selectedfile) {
    // Points at the virtual `stamped` area - the only addressable area this plugin
    // has. Where the site stamps, that area regenerates the document on every request
    // with the reader's identity and the current time, and a document FPDI refuses is
    // an error rather than a fallback to the original. Where the site has stamping
    // turned off (endpoint watermarking already in place), the same area serves the
    // document unmarked behind the same checks.
    //
    // No itemid either way: the reader's identity comes from the session, so the URL
    // carries nothing worth editing.
    $fileurl = moodle_url::make_pluginfile_url($context->id, 'mod_pdfsecure',
        'stamped', 0, '/', $selectedfile->get_filename());
    // The stamped bytes contain the current minute, so the PDF URL must be unique
    // for each page view. Otherwise PDF.js or an intermediary can reuse the previous
    // response even though the pluginfile callback disables shared caching.
    // One-use viewer token (also unique per view, so nothing reuses a previous response).
    $fileurl->param('pdfsecureview', \mod_pdfsecure\local\view_token::issue((int)$cm->id, 'mod_pdfsecure',
        $selectedfile->get_filename(), pdfsecure_stamp_mode() === 'off' ? 50 : 1));
    $viewerpath = $CFG->wwwroot . '/mod/pdfsecure/pdfjs-drm/web/viewer.html';
    
    // Forçando o idioma para Inglês
    $viewerurl = $viewerpath . '?file=' . urlencode($fileurl->out(false)) . '&locale=en-US';
    ?>
    <style>
        @media print {
            body, html, #page, #page-wrapper {
                display: none !important;
                visibility: hidden !important;
            }
        }
    </style>

    <style>
        .pdfsecure-frame-wrap { position: relative; width: 100%; height: calc(100vh - 150px); min-height: 520px;
            max-height: 1400px; border: 1px solid #ccc; border-radius: .375rem; margin-top: .75rem; overflow: hidden;
            user-select: none; -webkit-user-select: none; background: #2a2a2e; scroll-margin-top: 90px; }
        .pdfsecure-frame-wrap:fullscreen { height: 100vh; max-height: none; border: 0; border-radius: 0; }
        .pdfsecure-toolbar { display: flex; justify-content: flex-end; gap: .5rem; margin-top: .75rem; }
        .pdfsecure-toolbar .btn { display: inline-flex; align-items: center; gap: .4rem; }
    </style>
    <div class="pdfsecure-toolbar">
        <button type="button" class="btn btn-secondary" id="pdfsecure-fullscreen">
            <span aria-hidden="true">&#x26F6;</span> <?php echo get_string('fullscreen', 'mod_pdfsecure'); ?>
        </button>
    </div>
    <div class="pdfsecure-frame-wrap" id="pdfsecure-frame-wrap">
        
        <iframe id="pdf-iframe" src="<?php echo s($viewerurl); ?>#toolbar=0"
                width="100%" height="100%"
                style="border:none; position: absolute; z-index: 1;"
                onload="secureIframe()"></iframe>
        
        <?php
        // O overlay CSS de marca d'agua foi REMOVIDO em 2026-08-07.
        //
        // A marca agora e queimada nos bytes do PDF (mod_pdfsecure\local\watermarker),
        // entao o overlay so duplicava a marca na tela: o usuario via duas, e o que
        // ele via nao era o que estava no arquivo. Pior, ele dava a impressao de ser
        // a protecao, quando sumia com um clique no DevTools.
        ?>

    </div>

    <script>
    // 2. ESCUDO GLOBAL DO MOODLE
    document.addEventListener('contextmenu', function(event) {
        event.preventDefault();
    });

    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && (e.key === 'p' || e.key === 's' || e.key === 'P' || e.key === 'S')) {
            e.preventDefault();
        }
    });

    // Tela cheia do bloco do visualizador (o modo apresentação do PDF.js continua escondido).
    (function() {
        var wrap = document.getElementById('pdfsecure-frame-wrap');
        var button = document.getElementById('pdfsecure-fullscreen');
        if (!wrap || !button || !wrap.requestFullscreen) {
            if (button) { button.hidden = true; }
            return;
        }
        button.addEventListener('click', function() {
            if (document.fullscreenElement) {
                document.exitFullscreen();
            } else {
                wrap.requestFullscreen().catch(function() {});
            }
        });
        document.addEventListener('fullscreenchange', function() {
            var app = document.getElementById('pdf-iframe').contentWindow.PDFViewerApplication;
            if (app && app.pdfViewer) {
                setTimeout(function() { app.pdfViewer.currentScaleValue = 'page-width'; }, 150);
            }
        });
    })();

    // 3. CORTINA DE FUMAÇA INTELIGENTE
    var iframeElement = document.getElementById('pdf-iframe');

    window.addEventListener('blur', function() {
        if (document.activeElement === iframeElement) {
            return;
        }
        if(iframeElement) iframeElement.style.opacity = '0'; 
    });

    window.addEventListener('focus', function() {
        if(iframeElement) iframeElement.style.opacity = '1';
    });

    // 4. LOOP INVASOR NO IFRAME DO PDF.JS
    function secureIframe() {
        var iframe = document.getElementById('pdf-iframe');
        
        var injectInterval = setInterval(function() {
            try {
                var innerWindow = iframe.contentWindow;
                var innerDoc = iframe.contentDocument || innerWindow.document;
                
                if (innerDoc) {
                    var style = innerDoc.createElement('style');
                    style.innerHTML = `
                        /* CORREÇÃO 2: Destruição dos botões de Tela Cheia (#presentationMode, #secondaryPresentationMode) */
                        #print, #download, #secondaryPrint, #secondaryDownload, #openFile,
                        #secondaryOpenFile, .print, .download, #presentationMode,
                        #secondaryPresentationMode, .presentationMode,
                        #editorModeButtons, #editorModeSeparator, #editorFreeText, #editorInk, #editorStamp,
                        #editorHighlight, #cursorTextTool { display: none !important; }
                        body, .textLayer {
                            user-select: none !important;
                            -webkit-user-select: none !important;
                            cursor: default !important;
                        }
                        @media print { body { display: none !important; } }
                    `;
                    innerDoc.head.appendChild(style);

                    innerDoc.addEventListener('keydown', function(e) {
                        // CORREÇÃO 3: Bloqueio adicional da tecla F11
                        if (e.key === 'F11' || (e.ctrlKey && (e.key === 'p' || e.key === 's' || e.key === 'P' || e.key === 'S'))) {
                            e.preventDefault();
                        }
                    });

                    innerDoc.addEventListener('contextmenu', function(event) {
                        event.preventDefault();
                    });

                    innerWindow.addEventListener('blur', function() {
                        iframe.style.opacity = '0';
                    });
                    
                    innerWindow.addEventListener('focus', function() {
                        iframe.style.opacity = '1';
                    });

                    clearInterval(injectInterval);
                }
            } catch (e) {
                // Silencia até que o Iframe termine de carregar
            }
        }, 500);
    }
    </script>
    <?php

} else if (count($pdfs) > 1) {
    global $USER;
    echo html_writer::tag('p', get_string('selectdocument', 'mod_pdfsecure'));
    echo html_writer::start_tag('div', ['class' => 'pdfsecure-document-list list-group mb-3']);
    foreach ($pdfs as $filename => $document) {
        $progress = $DB->get_record('pdfsecure_progress', [
            'pdfsecureid' => $pdfsecure->id,
            'userid' => $USER->id,
            'filekey' => $filename,
        ]);
        $seen = $progress && $progress->seen !== ''
            ? count(array_filter(explode(',', $progress->seen), static function($page) {
                return $page !== '';
            })) : 0;
        $total = \mod_pdfsecure\local\watermarker::page_count($document);
        if ($total === null && $progress && (int)$progress->pages > 0) {
            $total = (int)$progress->pages;
        }
        $pages = (object)['read' => $seen, 'total' => $total === null ? '?' : $total];
        $done = $progress && !empty($progress->timecompleted);
        $status = $done
            ? get_string('readstatecomplete', 'mod_pdfsecure')
            : get_string('readstateincomplete', 'mod_pdfsecure');
        $link = new moodle_url('/mod/pdfsecure/view.php', ['id' => $cm->id, 'file' => $filename]);
        $name = html_writer::tag('span', $OUTPUT->pix_icon('f/pdf', '') . ' ' . format_string($filename),
            ['class' => 'pdfsecure-document-name fw-semibold']);
        $meta = html_writer::tag('span', get_string('pagesread', 'mod_pdfsecure', $pages),
            ['class' => 'pdfsecure-document-pages text-muted small me-2']);
        $meta .= html_writer::tag('span', $status,
            ['class' => 'pdfsecure-document-state badge ' . ($done ? 'bg-success' : 'bg-secondary')]);
        $row = $name . html_writer::tag('span', $meta, ['class' => 'text-nowrap']);
        echo html_writer::link($link, $row, ['class' => 'pdfsecure-document-item list-group-item list-group-item-action'
            . ' d-flex flex-wrap justify-content-between align-items-center gap-2']);
    }
    echo html_writer::end_tag('div');
} else {
    echo $OUTPUT->notification(get_string('errorpagesnodocument', 'mod_pdfsecure'), 'notifyproblem');
}

echo $OUTPUT->footer();
