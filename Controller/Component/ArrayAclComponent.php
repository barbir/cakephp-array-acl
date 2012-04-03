<?php
/*
 * This file is part of CakePHP Array ACL Plugin.
 *
 * CakePHP Array ACL Plugin
 * Copyright (c) 2012, Miljenko Barbir (http://miljenkobarbir.com)
 * 
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
*/ 
class ArrayAclComponent extends Component
{
	// init the Auth component settings (optional)
	var $loginAction	= array('controller' => 'users', 'action' => 'login');
	var $logoutRedirect	= array('controller' => 'users', 'action' => 'login');
	var $loginRedirect	= '/';

	var $models = array('User');
	var $components = array('Auth', 'Session');

	var $acl = array('*' => array('*' => 'allow'));	// allow everything
	var $enable_logging = false;

	var $messages = array
	(
		'login-failed'		=> 'The username or password you entered is incorrect.',
		'login-message'		=> 'Hello %s.',
		'logout-message'	=> 'Bye, bye...',
		'access-denied'		=> 'You cannot access the requested resource.',
	);

	function initialize()
	{
	}

	function startup()
	{
		// init the main Auth's login action to the ArrayAcl's,
		// but... if this needs to be modified, modify it at the 
		// declaration/initialization within the ArrayAclComponent
		$this->Auth->loginAction = $this->loginAction;
	}

	function beforeRender()
	{
	}

	function shutdown()
	{
	}

	function authorize($controller, $use_auth_events = false)
	{
		if($this->_isAuthorized($controller))
		{
			if($this->enable_logging) $this->log('Result: allowed', 'array_acl');
			if($use_auth_events) $this->Auth->allow('*');
			return true;
		}
		else
		{
			if($this->enable_logging) $this->log('Result: denied', 'array_acl');
			$this->Session->setFlash($this->messages['access-denied']);
			if($use_auth_events) $this->Auth->deny('*');
			return false;
		}
	}

	function _isAuthorized($controller)
	{
		$params = $controller->params;

		// check ACL with this ARO and ACO
		$result = $this->_userAllowed
		(
			$this->acl,
			$controller->Auth->user(),
			array
			(
				'controller' => $params['controller'],
				'action' => $params['action']
			),
			'deny'
		);

		return $result;
    }

	function _userAllowed($acl, $aro, $thisAco, $default = 'allow')
	{
		// prepare different aco level ids
		$thisAcoRoot = '*';
		$thisAcoController = $thisAco['controller'] . '/*';
		$thisAcoAction = $thisAco['controller'] . '/' . $thisAco['action'];
		$thisAcoAllControllersAction = '*/' . $thisAco['action'];

		// prepare aro id (default '*')
		$aroId = '*';
		if(isset($aro))
		{
			$aroId = $aro['User']['group_id'];
		}

		if($this->enable_logging)	$this->log('ARO: ' . $aroId, 'array_acl');
		if($this->enable_logging)	$this->log('ACO: ' . $thisAco['controller'] . '/' . $thisAco['action'], 'array_acl');

		// find the latest matching rule, and apply it to the result
		$result = $default;
		foreach($acl as $aro => $rules)
		{
			if($aro == '*' || $aro == $aroId)
			{
				foreach($rules as $aco => $rule)
				{
					if
					(
						$aco == $thisAcoRoot
						||
						$aco == $thisAcoController
						||
						$aco == $thisAcoAction
						||
						$aco == $thisAcoAllControllersAction
					)
					{
						if($this->enable_logging)	$this->log('Test: ARO: ' . $aro . ' ACO: ' . $aco . ' => ' . $rule, 'array_acl');
						$result = $rule;
					}
				}
			}
		}

		// return true if the lates result is allow
		return ($result == 'allow');
	}


	function login($user)
	{
		$User = ClassRegistry::init('User');
		$userData = $User->find
		(
			'first', 
			array
			(
				'conditions' => array
				(
					'User.username' => $user['username'],
					'User.password' => $user['password']
				)
			)
		);

		if($userData)
		{
			$user = 'user';

			if(isset($userData['User']))
			{
				if(isset($userData['User']['username'])) $user = $userData['User']['username'];
				if(isset($userData['User']['name'])) $user = $userData['User']['name'];
			}

			$loginMessage = sprintf($this->messages['login-message'], $user);

			$this->Session->setFlash($loginMessage);
			$this->Auth->login($userData);
			return $this->loginRedirect;
		}
		else
		{
			$this->Session->setFlash($this->messages['login-failed']);
			return $this->loginAction;
		}
	}

	function logout()
	{
		$this->Session->setFlash($this->messages['logout-message']);
		$this->Auth->logout();
		return $this->logoutRedirect;
	}
}
?>