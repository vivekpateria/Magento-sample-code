<?php
/**
 * @category    Alw
 * @package     Alw_Salesagent
 */
class Alw_Salesagent_Block_Salesagent extends Mage_Core_Block_Template
{
    /*Get Sales registration form post action url*/
    public function getPostActionUrl()
    {
        return $this->getUrl('salesagent/index/registerpost');
    }
}
