<?php
class AlternateHreflang_Kwc_Box_Events extends Kwc_Abstract_Events
{
    public function getListeners()
    {
        $ret = array();
        $ret[] = array(
            'class' => 'AlternateHreflang_Kwc_Box_Model',
            'event' => 'Kwf_Events_Event_Row_Updated',
            'callback' => 'onRowChange'
        );
        $ret[] = array(
            'class' => 'AlternateHreflang_Kwc_Box_Model',
            'event' => 'Kwf_Events_Event_Row_Inserted',
            'callback' => 'onRowChange'
        );
        $ret[] = array(
            'class' => 'AlternateHreflang_Kwc_Box_Model',
            'event' => 'Kwf_Events_Event_Row_Deleted',
            'callback' => 'onRowChange'
        );
        return $ret;
    }

    public function onRowChange(Kwf_Events_Event_Row_Abstract $event)
    {
        $components = Kwf_Component_Data_Root::getInstance()
            ->getComponentsByDbId($event->row->component_id);
        foreach ($components as $c) {
            $this->fireEvent(new Kwf_Component_Event_Component_ContentChanged($c->componentClass, $c));
        }
    }
}
