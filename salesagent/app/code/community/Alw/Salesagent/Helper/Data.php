<?php
/**
 *
 * @category    Alw
 * @package     Alw_Salesagent
 */
class Alw_Salesagent_Helper_Data extends Mage_Core_Helper_Abstract
{
   /* Get salesagent registration Url */
    public function getAccountUrl()
    {
        return $this->_getUrl('salesagent/index');
    }
    
	/* Get auto generate password */
    public function generatePassword($length=7)
    {
        return substr(md5(uniqid(rand(), true)), 0, $length);
    }
}