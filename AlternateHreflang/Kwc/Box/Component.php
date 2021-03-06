<?php
class AlternateHreflang_Kwc_Box_Component extends Kwc_Abstract implements Kwf_Util_Maintenance_JobProviderInterface
{
    public static function getSettings($param = null)
    {
        $ret = parent::getSettings($param);
        $ret['componentName'] = trlKwfStatic('Alternate Hreflang');
        $ret['extConfig'] = 'Kwf_Component_Abstract_ExtConfig_Grid';
        $ret['flags']['hasHeaderIncludeCode'] = true;
        $ret['flags']['hasInjectIntoRenderedHtml'] = true;
        return $ret;
    }

    public function getIncludeCode()
    {
        return $this->getData();
    }

    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer)
    {
        $ret = parent::getTemplateVars($renderer);
        $ret['links'] = array();
        $s = new Kwf_Model_Select();
        $s->whereEquals('component_id', $this->getData()->componentId);
        $links = Kwf_Model_Abstract::getInstance('AlternateHreflang_Kwc_Box_Model')
            ->getRows($s);
        foreach ($links as $link) {
            if ($link->url && $link->language) {
                $ret['links'][] = array(
                    'language' => $link->language,
                    'url' => $link->url
                );
            }
        }
        $ret['currentLink'] = $this->getData()->getPage()->getAbsoluteUrl();
        $language = $this->getData()->getBaseProperty('language');
        if (!strpos($language, '-') && $this->getData()->getBaseProperty('country')) {
            $language .= '-' . strtoupper($this->getData()->getBaseProperty('country'));
        }
        $ret['currentLanguage'] = $language;
        return $ret;
    }

    public static function getMaintenanceJobs()
    {
        return array(
            'AlternateHreflang_Kwc_Box_MaintenanceJob',
        );
    }

    public function injectIntoRenderedHtml($html)
    {
        $startPos = strpos($html, '<!-- alternateHreflang -->');
        $endPos = strpos($html, '<!-- /alternateHreflang -->') + 27;
        return substr($html, 0, $startPos) .
            $this->getData()->render() .
            substr($html, $endPos);
    }
}
