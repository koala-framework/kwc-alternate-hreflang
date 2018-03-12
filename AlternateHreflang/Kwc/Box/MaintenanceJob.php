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
            if ($link->url == 'https://www.volkswagen.de/de.html') continue;
            if ($debug) {
                echo $link->url . "\n";
            }
            try {
                $x = 0;
                $finalLocation = null;
                $location = $link->url;
                while (!$finalLocation && $x < 5) {
                    $client = new Zend_Http_Client($location, array('timeout' => 5, 'maxredirects' => 0)); // Use loop and maxredirects because there's no way to access the last set Location-Header
                    $response = $client->request();
                    if ($response->isRedirect()){
                        $location = $response->getHeader('Location');
                        $link->url = $location;
                        $link->save();
                        if ($debug) {
                            echo '  Redirect to ' . $location . "\n";
                        }
                    } else {
                        $finalLocation = $location;
                        if ($response->isError()) {
                            $errors[] = $link;
                            if ($debug) {
                                echo "  Not found\n";
                            }
                        } else {
                            if ($debug) {
                                echo "  Found\n";
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                if ($debug) {
                    echo "  Error\n";
                }
                $errors[] = $link;
            }
        }
        if (count($errors)) {
            $text = "An error occurred while checking alternate hreflang urls:\n\n";
            $text .= "These urls were not reachable:\n";
            foreach ($errors as $link) {
                $c = Kwf_Component_Data_Root::getInstance()->getComponentByDbId($link->component_id);
                if ($c) {
                    $name = $c->getPage()->getTitle();
                    if (!$name) {
                        $name = $c->getPage()->name;
                    }
                    $text .= $name.": ".$link->url."\n";
                }
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
