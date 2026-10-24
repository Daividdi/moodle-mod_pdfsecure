<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * PDF Secure activity settings form.
 */
class mod_pdfsecure_mod_form extends moodleform_mod {

    /**
     * Adds the custom read-to-end completion rule.
     *
     * @return array Form element names added by this method.
     */
    public function add_completion_rules() {
        $mform = $this->_form;
        $fieldname = 'completionreadall' . $this->get_suffix();
        $mform->addElement('checkbox', $fieldname, '', get_string('completionreadall', 'mod_pdfsecure'));
        $mform->setDefault($fieldname, 0);
        $mform->addHelpButton($fieldname, 'completionreadall', 'mod_pdfsecure');

        return [$fieldname];
    }

    /**
     * Checks whether the custom read-to-end completion rule is enabled.
     *
     * @param array $data Form data.
     * @return bool Whether the read-to-end rule is enabled.
     */
    public function completion_rule_enabled($data) {
        $fieldname = 'completionreadall' . $this->get_suffix();
        return !empty($data[$fieldname]);
    }

    /**
     * Sets the custom rule value when editing an existing activity.
     *
     * @param array $defaultvalues Form default values.
     */
    public function data_preprocessing(&$defaultvalues) {
        parent::data_preprocessing($defaultvalues);
        $fieldname = 'completionreadall' . $this->get_suffix();
        if (!array_key_exists($fieldname, $defaultvalues)) {
            $defaultvalues[$fieldname] = !empty($defaultvalues['completionreadall']) ? 1 : 0;
        }
    }

    /**
     * Defines the PDF Secure activity settings form.
     */
    public function definition() {
        global $CFG;
        $mform = $this->_form;

        // Cabeçalho Geral
        $mform->addElement('header', 'general', get_string('general', 'core'));

        // Nome da Atividade
        $mform->addElement('text', 'name', get_string('pdfsecurename', 'mod_pdfsecure'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Descrição Padrão
        $this->standard_intro_elements();

        // Área de Upload do PDF
        $mform->addElement('header', 'content', get_string('contentheader', 'mod_pdfsecure'));
        
        $mform->addElement('filemanager', 'pdf_file', get_string('selectfile', 'mod_pdfsecure'), null,
            array('subdirs' => 0, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => 1,
                  'accepted_types' => array('.pdf'))
        );
        $mform->addRule('pdf_file', null, 'required', null, 'client');

        // Configurações Padrão do Moodle e Botões
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
}
