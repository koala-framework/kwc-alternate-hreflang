<?php
class AlternateHreflang_Kwc_Box_MaintenanceJob extends Kwf_Util_Maintenance_Job_Abstract
{
    public function getFrequency()
    {
        return self::FREQUENCY_DAILY;
    }

    public function execute($debug)
    {
        $config = array(
            'adapter' => 'Zend_Http_Client_Adapter_Curl',
            'timeout' => 10,
        );
        if (Kwf_Config::getValue('http.proxy.host')) {
            $config['proxy_host'] = Kwf_Config::getValue('http.proxy.host');
            $config['proxy_port'] = Kwf_Config::getValue('http.proxy.port');
        }

        $failedLinks = array();
        $errors = array();

        $s = new Kwf_Model_Select();
        $links = Kwf_Model_Abstract::getInstance('AlternateHreflang_Kwc_Box_Model')->getRows($s);
        foreach ($links as $link) {

            $component = Kwf_Component_Data_Root::getInstance()->getComponentByDbId($link->component_id, array('ignoreVisible' => true));
            if (!$component || !$link->url) {
                $link->delete();
                continue;
            }

            $isSuccessful = false;
            try {
                if ($debug) echo $link->url;
                $location = $link->url;
                $client = new Zend_Http_Client($location, $config);
                $response = $client->request();
                if ($response->getStatus() != 200) {
                    if ($debug) echo "\n  Status {$response->getStatus()}";
                } else {
                    $isSuccessful = true;
                }
            } catch (Exception $e) {
                if ($debug) echo "\n  " . $e->getMessage();
            }
            if ($debug) echo "\n";

            if (!$isSuccessful) {
                $domainComponentId = $component->getDomainComponent()->componentId;
                if (!isset($failedLinks[$domainComponentId])) $failedLinks[$domainComponentId] = array();
                $failedLinks[$domainComponentId][] = $link;
            }
        }

        foreach ($failedLinks as $domainComponentId => $links) {
            $domain = Kwf_Component_Data_Root::getInstance()->getComponentByDbId($domainComponentId, array('ignoreVisible' => true));
            $recipient = $component->getBaseProperty('alternateHreflang.emailreceiver');
            if (!$recipient) {
                $errors[] = 'No "alternateHreflang.emailreceiver" set for ' . $domain->componentId;
                continue;
            }

            $subject = $domain->getDomain() . ' - alternate hreflang';
            $mailText = array();

            $mailText[] = "An error occurred while checking alternate hreflang urls.";
            $mailText[] = "These urls were not reachable:\n";
            foreach ($links as $link) {
                $component = Kwf_Component_Data_Root::getInstance()->getComponentByDbId($link->component_id, array('ignoreVisible' => true));
                $parts = array();
                $c = $component->getPage();
                while ($c) {
                    $parts[] = $c->name;
                    if (!$c->getParentPage()) {
                        $parts[] = $c->parent->name;
                    }
                    $c = $c->getParentPage();
                }
                $mailText[] = implode(' / ', array_reverse($parts)) . ' -> ' . $link->url;
            }
            $mailText[] = "\nPlease check the links by select \"Alternative Hreflang\" in the context menu of the page in the page tree admin panel.";

            $mail = new Kwf_Mail();
            $mail->setSubject($subject);
            $mail->addTo($recipient);
            $mail->setBodyText(implode($mailText, "\n"));
            $mail->send();
        }

        echo implode("\n", $errors);
    }
}
