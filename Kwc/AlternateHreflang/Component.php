<?php
class Kwc_AlternateHreflang_Component extends Kwc_Abstract implements Kwf_Util_Maintenance_JobProviderInterface
{
    public static function getSettings($param = null)
    {
        $ret = parent::getSettings($param);
        $ret['componentName'] = trlKwfStatic('Alternate Hreflang');
        $ret['extConfig'] = 'Kwf_Component_Abstract_ExtConfig_Grid';
        $ret['flags']['hasHeaderIncludeCode'] = true;
        $ret['countries'] = array(
            'de-DE' => trlKwfStatic('Germany'),
        );
        $ret['emailReceiver'] = null;
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
        $links = Kwf_Model_Abstract::getInstance('Kwc_AlternateHreflang_Model')
            ->getRows($s);
        foreach ($links as $link) {
            if ($link->url && $link->language) {
                $ret['links'][] = array(
                    'language' => $link->language,
                    'url' => $link->url
                );
            }
        }
        $prot = 'http://';
        if (Kwf_Util_Https::supportsHttps()) {
            $prot = 'https://';
        }
        $domain = Kwf_Config::getValue('server.domain');
        $ret['currentLink'] = $prot.$domain.$this->getData()->getPage()->url;
        return $ret;
    }

    public static function getMaintenanceJobs()
    {
        return array(
            'Kwc_AlternateHreflang_StartMaintenanceJob',
        );
    }
}
