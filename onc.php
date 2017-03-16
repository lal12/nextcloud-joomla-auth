<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.onc
 *
 * @copyright   Copyright (C) 2017 Luca Lindhorst All rights reserved.
 * @license     MIT License
 */

use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Owncloud Nextcloud Authentication Plugin
 *
 * @since  3.3
 */
class PlgAuthenticationOnc extends JPlugin
{
	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @param   array   $credentials  Array holding the user credentials
	 * @param   array   $options      Array of extra options
	 * @param   object  &$response    Authentication response object
	 *
	 * @return  boolean
	 *
	 * @since   3.3
	 */
	public function onUserAuthenticate($credentials, $options, &$response)
	{
		$this->loadLanguage();
		// No backend authentication
		if (JFactory::getApplication()->isAdmin() && !$this->params->get('backendLogin', 0)){
			return;
		}
		$success = false;

		//Configure CURL
		$options = new \Joomla\Registry\Registry;
		$options->set('transport.curl', array(
			CURLOPT_SSL_VERIFYPEER => false,// $this->params->get('verifypeer', 0),
			CURLOPT_SSL_VERIFYHOST => false
		));
		try{
			$http = JHttpFactory::getHttp($options);
		}catch (RuntimeException $e){
			$response->status        = JAuthentication::STATUS_FAILURE;
			$response->type          = 'ONC';
			$response->error_message = JText::sprintf('JGLOBAL_AUTH_FAILED', JText::_('JGLOBAL_AUTH_CURL_NOT_INSTALLED'));
			return;
		}

		// Check if we have a username and password
		if (strlen($credentials['username']) == 0 || strlen($credentials['password']) == 0){
			$response->type          = 'ONC';
			$response->status        = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::sprintf('JGLOBAL_AUTH_FAILED', JText::_('JGLOBAL_AUTH_USER_BLACKLISTED'));
			return;
		}

        // Check if the username isn't blacklisted
		$blacklist = explode(',', $this->params->get('user_blacklist', ''));
		if (in_array($credentials['username'], $blacklist)){
			$response->type          = 'ONC-Auth';
			$response->status        = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::sprintf('JGLOBAL_AUTH_FAILED', JText::_('JGLOBAL_AUTH_USER_BLACKLISTED'));
			return;
		}

        //API Routes on github Nextcloud: server/apps/provisioning_api/appinfo/routes.php
        $headers = array(
			'Authorization' => 'Basic ' . base64_encode($credentials['username'] . ':' . $credentials['password']),
            'OCS-APIRequest' => 'true'
		);
        try{
            $result = $http->get($this->params->get('url').'/ocs/v1.php/cloud/user?format=json', $headers);
        }catch (Exception $e){
            // If there was an error in the request then create a 'false' dummy response.
            $result = new JHttpResponse;
            $result->code = false;
			trigger_error($e->getMessage());
        }
        $data = null;
        if($result->code == 200){
            try{
                $data = json_decode($result->body);
                if($data->ocs->meta->statuscode == 100){
                    $success = true;
                }
            }catch(Exception $e){
                // Failure
            }
        }else{
            $message = JText::_('JGLOBAL_AUTH_ACCESS_DENIED');
        }
        $response->type = 'ONC';

        if (!$success){
            $response->status        = JAuthentication::STATUS_FAILURE;
            $response->error_message = JText::sprintf('JGLOBAL_AUTH_FAILED', $message);
            return;
        }
        $email = $data->ocs->data->email;
        $display = $data->ocs->data->{'display-name'};
		$response->status        = JAuthentication::STATUS_SUCCESS;
		$response->error_message = '';
		if($email)
			$response->email         = $email;
		$response->username = $credentials['username'];
		$response->fullname = $display;
	}
}
