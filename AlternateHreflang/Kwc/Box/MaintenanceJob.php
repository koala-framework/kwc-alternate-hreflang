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
        $errors = array();
        foreach ($links as $link) {
            $client = new Zend_Http_Client($link->url);
            try {
                $response = $client->request();
                if ($response->getStatus() != 200) {
                    $errors[] = $link;
                }
            } catch (Exception $e) {
                $errors[] = $link;
            }
        }
        if (count($errors)) {
            $text = "An error occurred while checking alternate hreflang urls:\n\n";
            $text .= "These urls were not reachable:\n";
            foreach ($errors as $link) {
                $c = Kwf_Component_Data_Root::getInstance()->getComponentByDbId($link->component_id);
                $name = $c->getPage()->getTitle();
                if (!$name) {
                    $name = $c->getPage()->name;
                }
                $text .= $name.": ".$link->url."\n";
            }
            $text .= "\nPlease check the links.";
            $mail = new Kwf_Mail();
            $mail->setSubject($c->getDomain() . ' - alternate hreflang');
            $mail->addTo($c->getBaseProperty('alternateHreflang.emailreceiver'));
            $mail->setBodyText($text);
            $mail->send();
        }
    }
}
