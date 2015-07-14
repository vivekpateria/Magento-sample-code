<?php
/**
 * IndexController for Sales agent registration
 *
 * @category    Alw
 * @package     Alw_Salesagent
 */
class Alw_Salesagent_IndexController extends Mage_Core_Controller_Front_Action
{
    /* Render the layout and display registration form */
    public function indexAction() 
    {
        $this->loadLayout();
        $this->renderLayout();
        Mage::getSingleton('core/session')->setPostValue('');
    }
    
    /* Prepare session to add message in session */
    protected function _getSession()
    {
        return Mage::getSingleton('core/session');
    }
    
    /* Save the sales agent registration records in database and send notifications to admin and registered user */
    public function registerpostAction() 
    {
        if ($this->getRequest()->isPost()) {
            $helper = Mage::helper('adminhtml');
            $formData = $this->getRequest()->getPost();
            $session = $this->_getSession();
            $agentCommission = Mage::getStoreConfig('salesagent/general/salesagent_commission');
            try {
                $email = $helper->stripTags(trim($formData['email']));
                /* create new agent */
                $user = Mage::getModel('admin/user');
                $user->setData(array('username' =>  $formData['user_name'],
                                    'firstname' =>  $formData['first_name'],
                                    'lastname'  =>   $formData['last_name'],
                                    'email'     => $email,
                                    'password'  => trim($formData['password']),
                                    'agent_commission' => $agentCommission,
                                    'dob'  => '',
                                    'is_active' => 0))->save();
                Mage::getSingleton('core/session')->setPostValue('');
               
                $emailTemplate  = Mage::getModel('core/email_template')->loadDefault('agent_registration_email');
                /* Create an array of variables to assign to template */
                $emailTemplateVariables = array();
                $emailTemplateVariables['rec_first_name'] = ucwords($formData['first_name']);
                $emailTemplateVariables['rec_last_name'] = ucwords($formData['last_name']);
                $emailTemplateVariables['sender_name'] = Mage::getStoreConfig('trans_email/ident_general/name');
                $emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name'));
                $emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
                $emailTemplate->setTemplateSubject('Confirmation');
                try {
                    $emailTemplate->send($email,$formData['first_name'], $emailTemplateVariables);
                } catch (Mage_Core_Exception $e) {
                    $this->_redirectError(Mage::getUrl('*/*/index', array('_secure' => true)));
                } catch (Exception $e) {
                    $session->addError($e->getMessage());
                    $this->_redirectError(Mage::getUrl('*/*/index', array('_secure' => true)));    
                }
              
                $emailAdminTemplate = Mage::getModel('core/email_template')->loadDefault('admin_agent_registration');
                /*Create an array of variables to assign to template*/
                $emailAdminTemplateVariables = array();
                $emailAdminTemplateVariables['user_first_name'] = ucwords($formData['first_name']);
                $emailAdminTemplateVariables['user_last_name'] = ucwords($formData['last_name']);
                $emailAdminTemplateVariables['user_email'] = ucwords($formData['email']);
                $fullname = ucwords($formData['first_name'])." ".ucwords($formData['last_name']);
                $emailAdminTemplate->setSenderName($fullname);
                $emailAdminTemplate->setSenderEmail($email);
                $emailAdminTemplate->setTemplateSubject('Notification');
                try {
                    $emailAdminTemplate->send(Mage::getStoreConfig('trans_email/ident_general/email'), Mage::getStoreConfig('trans_email/ident_general/name'), $emailAdminTemplateVariables);
                    $this->_redirectSuccess(Mage::getUrl('*/*/success', array('_secure'=>true)));    
                } catch (Mage_Core_Exception $e) {
                    $this->_redirectError(Mage::getUrl('*/*/index', array('_secure' => true)));
                } catch (Exception $e) {
                    $session->addError($e->getMessage());
                    $this->_redirectError(Mage::getUrl('*/*/index', array('_secure' => true)));    
                }

                try {
                $roleId = Mage::getModel('salesagent/salesagent')->getAgents();
                    /*assign user to role*/
                    $user->setRoleIds(array($roleId))
                        ->setRoleUserId($user->getUserId())
                        ->saveRelations();
                } catch (Exception $e) {
                    Mage::getSingleton('core/session')->setPostValue($this->getRequest()->getPost());
                    $session->addError($e->getMessage());
                    $this->_redirectError(Mage::getUrl('*/*/index', array('_secure' => true)));
                } 
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->setPostValue($this->getRequest()->getPost());
                $session->addError($e->getMessage());
                $this->_redirectError(Mage::getUrl('*/*/index', array('_secure' => true)));
            }
        }
    }
}