<?php
class Kwc_AlternateHreflang_Component extends Kwc_Abstract implements Kwf_Util_Maintenance_JobProviderInterface
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['componentName'] = trlKwfStatic('Alternate Links');
        $ret['extConfig'] = 'Kwf_Component_Abstract_ExtConfig_Grid';
        $ret['flags']['hasHeaderIncludeCode'] = true;
        $ret['countries'] = array(
            'de-DE' => trlKwfStatic('Germany'),
        );
        $ret['emailReceiver'] = null;
        return $ret;
    }

    public static function validateSettings($settings, $componentClass)
    {
        parent::validateSettings($settings, $componentClass);
        if (!$settings['emailReceiver']) {
            throw new Kwf_Exception("emailReceiver has to be set");
        }
    }

    public function getIncludeCode()
    {
        return $this->getData();
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $ret['links'] = array();
        $s = new Kwf_Model_Select();
        $s->whereEquals('component_id', $this->getData()->componentId);
        $links = Kwf_Model_Abstract::getInstance('Box_AlternateLinks_Model')
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
