<?php
class AlternateHreflang_Kwc_Box_MaintenanceJob extends Kwf_Util_Maintenance_Job_Abstract
{
    public function getFrequency()
    {
        return self::FREQUENCY_DAILY;
    }

    public function execute($debug)
    {
        $s = new Kwf_Model_Select();
        $s->whereNotEquals('url', '');
        $links = Kwf_Model_Abstract::getInstance('AlternateHreflang_Kwc_Box_Model')->getRows($s);

        $domains = array();
        foreach (Kwf_Component_Data_Root::getInstance()->getDomainComponents() as $domain) {
            $domains[$domain->componentId] = array(
                'component' => $domain,
                'failedLinks' => array()
            );
        }

        foreach ($links as $link) {
            $c = Kwf_Component_Data_Root::getInstance()->getComponentByDbId($link->component_id, array('ignoreVisible' => true));
            $debugMessage = '';
            $link = array(
                'url' => $link->url,
                'name' => $c->getPage()->getTitle()
            );

            $client = new Zend_Http_Client($link['url']);
            try {
                $debugMessage .= "\n{$link['url']}: ";
                $response = $client->request();
                if ($response->getStatus() != 200) {
                    $debugMessage .= "status {$response->getStatus()}";
                    $domains[$c->getDomainComponent()->componentId]['failedLinks'][] = $link;
                } else if ($debug) {
                    $debugMessage .= 'success';
                }
            } catch (Exception $e) {
                $debugMessage .= 'error';
                $domains[$c->getDomainComponent()->componentId]['failedLinks'][] = $link;
            }

            if ($debug) {
                echo $debugMessage;
            }
        }

        if ($debug) {
            echo "\n\n";
        }

        foreach ($domains as $domain) {
            if (count($domain['failedLinks'])) {
                $recipient = $domain['component']->getBaseProperty('alternateHreflang.emailreceiver');
                if (!strlen($recipient)) {
                    throw new Kwf_Exception("No \"alternateHreflang.emailreceiver\" set for {$domain['component']->componentId}.");
                }

                $subject = $domain['component']->getDomain() . ' - alternate hreflang';
                $mailText = array();

                $mailText[] = "An error occurred while checking alternate hreflang urls.";
                $mailText[] = "These urls were not reachable:\n";

                foreach ($domain['failedLinks'] as $link) {
                    $mailText[] = $link['name'] . ': ' . $link['url'];
                }

                $mailText[] = "\nPlease check the links.";

                $mail = new Kwf_Mail();
                $mail->setSubject($subject);
                $mail->addTo($recipient);
                $mail->setBodyText(implode($mailText, "\n"));
                $mail->send();
            }
        }
    }
}
