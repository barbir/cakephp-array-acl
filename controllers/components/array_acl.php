<?php
/*
 * This file is part of CakePHP Array ACL Plugin.
 *
 * CakePHP Array ACL Plugin
 * Copyright (c) 2010, Miljenko Barbir (http://miljenkobarbir.com)
 * 
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
*/ 
class ArrayAclComponent extends Object
{
	var $acl = array('*' => array('*' => 'allow'));	// allow everything
	var $enable_logging = false;

	function authorize($controller)
	{
		if($this->_isAuthorized($controller))
		{
			if($this->enable_logging)	$this->log('Result: allowed', 'array_acl');
			$controller->Auth->allow('*');
			return true;
		}
		else
		{
			if($this->enable_logging)	$this->log('Result: denied', 'array_acl');
			$controller->Auth->deny('*');
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
}
?>