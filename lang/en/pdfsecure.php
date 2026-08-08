<?php
$string['pluginname'] = 'PDF Secure';
$string['modulename'] = 'PDF Secure';
$string['modulename_help'] = 'The PDF Secure module stamps every page of a PDF with the name of the user who opened it, and never serves the unstamped original. A document that can be read can always be copied - what this guarantees is that any copy in circulation identifies the account it came from.';
$string['modulenameplural'] = 'PDF Secures';
$string['pluginadministration'] = 'PDF Secure Administration';
$string['pdfsecurename'] = 'PDF Name';
$string['pdfsecurename_help'] = 'This is the name of the link that students will see on the course page.';

// Strings for the form (Step 2)
$string['watermarktext'] = 'Watermark settings';
$string['enablewatermark'] = 'Enable watermark';
$string['enablewatermark_help'] = 'If enabled, the student\'s name and email will be stamped on the PDF pages.';
$string['contentheader'] = 'PDF Document';
$string['selectfile'] = 'Select the PDF file';
$string['cannotstamp'] = 'This document could not be prepared for viewing. Please report this to the site administrator.';

// Watermark appearance settings.
$string['settingtone'] = 'Watermark tone';
$string['settingtone_desc'] = 'How dark the diagonal watermark is. Darker is harder to remove from a screenshot but harder to read the document through; lighter reads better but survives a low-quality photo less well.';
$string['tonestrong'] = 'Strong (most visible)';
$string['tonemedium'] = 'Medium';
$string['tonelight'] = 'Light (recommended)';
$string['tonefaint'] = 'Faint (least intrusive)';
$string['settingfontsize'] = 'Watermark text size';
$string['settingfontsize_desc'] = 'Size of the repeated diagonal text. Larger text is more legible in a photo of the screen but covers more of the page.';
$string['settingincludedate'] = 'Include date in the diagonal watermark';
$string['settingincludedate_desc'] = 'Adds the date and time of access next to the name and email. The footer stamp always carries the date regardless of this setting.';
$string['settingshowfooter'] = 'Show footer stamp';
$string['settingshowfooter_desc'] = 'A small legible line at the bottom of every page with the full identity, user id, activity id and timestamp, on a white band so it stays readable over dark page designs. This is the stamp meant to be read by eye when tracing a leak.';

// Retention of generated copies.
$string['taskprune'] = 'Remove old watermarked copies';
$string['settingretention'] = 'Keep watermarked copies for';
$string['settingretention_desc'] = 'One copy is stored per user per document, so this file area grows with users x documents x time and has no natural ceiling. Old copies are deleted nightly and regenerate transparently on the next view, which costs hundredths of a second - so a short retention is close to free. Choose "Keep forever" only if disk space is not a concern.';
$string['retentionnever'] = 'Keep forever (no pruning)';
$string['retentiondays'] = '{$a} days';

// Global Search.
$string['search:activity'] = 'PDF Secure - activity and document text';
