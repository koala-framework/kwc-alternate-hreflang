<?php
class Kwc_AlternateHreflang_Controller extends Kwf_Controller_Action_Auto_Kwc_Grid
{
    protected $_model = 'Kwc_AlternateHreflang_Model';

    protected function _initColumns()
    {
        parent::_initColumns();
        $component = Kwf_Component_Data_Root::getInstance()->getComponentById($this->_getParam('componentId'));
        $languages = $component->getBaseProperty('alternatehreflang.languages');
        $editor = new Kwf_Form_Field_Select();
        $editor->setValues($languages);
        $editor->setAllowBlank(false);
        $this->_columns->add(new Kwf_Grid_Column('language', trlKwf('Language'), 150))
            ->setEditor($editor);
        $editor = new Kwf_Form_Field_UrlField();
        $editor->setAllowBlank(false);
        $this->_columns->add(new Kwf_Grid_Column('url', trlKwf('Url'), 400))
            ->setEditor($editor);
    }
}
