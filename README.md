CakePHP Array ACL v2.0.0
================================================================================================

CakePHP Array ACL is a plugin for a CakePHP application used for creating Access Control Lists,
and using them to manage user access (AROs) to different parts of the application (ACOs). Its
design is based on the CakePHP built-in ACL, but simplified to ease the process of adding ACLs
to your application. If you wish to know more, read on...

Installation
------------------------------------------------------------------------------------------------

First you need to install the plugin like so:
 * Download the CakePHP Array ACL plugin from github 
   (http://github.com/barbir/cakephp-array-acl/archives/master)
 * Unpack the contents of the archive to the plugins folder of the CakePHP project and rename
   the first level folder in the archive (eg: "barbir-cakephp-array-acl-xxxxxxx") to
   "array_acl", and you will have a structure like this "myproject/plugins/array_acl/".
 * It's installed... Contents of the "myproject/app/Plugin/ArrayAcl/" folder should look 
   like this:
    * Controller
    * README.md

If you don't have users and groups tables and/or login and logout actions for your users
------------------------------------------------------------------------------------------------

If you have users and groups tables compatible with the plugin, skip this part. If you don't,
execute the below scripts to create both tables.

Users:

	create table `users`
	(
		`id`		int(10)		unsigned		not null auto_increment,
		`group_id`	int(10)		unsigned		not null,
		`username`	varchar(50)	collate utf8_unicode_ci	not null,
		`password`	varchar(50)	collate utf8_unicode_ci	not null,
		`name`		varchar(255)	collate utf8_unicode_ci	not null,
		primary key (`id`)
	) default charset=utf8 collate=utf8_unicode_ci;

Groups:

	create table `groups`
	(
		`id`	int(10)		unsigned		not null auto_increment,
		`name`	varchar(255)	collate utf8_unicode_ci	not null,
		primary key (`id`)
	) default charset=utf8 collate=utf8_unicode_ci;

Now open your CakePHP console, and bake the MVC files for both tables (prefererbly with "some 
basic class methods").

Now when that's freshly baked, add login and logout actions to your users controller:

	function login()
	{
		if(isset($this->data['User']))
		{
			$this->redirect($this->ArrayAcl->login($this->data['User']));
		}
	}

	function logout()
	{
		$this->redirect($this->ArrayAcl->logout());
	}

And create the login view for your users controller (yourapproot/app/views/users/login.ctp):

	<h2>Login</h2>
	<?php
		echo $this->Form->create('User', array('url' => array('controller' => 'users', 'action' =>'login')));
		echo $this->Form->input('User.username');
		echo $this->Form->input('User.password');
		echo $this->Form->end('Login');
	?>

Usage
------------------------------------------------------------------------------------------------

When you install the plugin, you are ready to use it. In order to use the CakePHP Array ACL 
plugin, you need to:
 * activate plugin in your bootstrap (or somewhere else)
 * include the Array ACL component
 * initialize the ACL list object
 * execute the ArrayAcl authorize

Activate plugin
---------------

Open the bootstrap.php (app\Config\bootstrap.php) and add the following line:

	CakePlugin::load('ArrayAcl');

Include the Array ACL component
-------------------------------

Include the Array ACL component in your controller, by adding 'ArrayAcl.ArrayAcl' to your 
$components array. Besides this, include the Auth componenet.

	var $components = array(... 'Auth', 'ArrayAcl.ArrayAcl');

Initialize the ACL list object and the Auth object
--------------------------------------------------

In the app controller's beforeFilter action, add the following code:

	// 1. init the Auth component authorize method
	$this->Auth->authorize = array('ArrayAcl.ArrayAcl');

	// 2. init the ArrayAcl components ACL array
	$this->ArrayAcl->acl = array
	(
		// everyone
		'*' => array
		(
			'*'		=> 'deny',		// deny everything
			'users/login'	=> 'allow',		// allow login to everyone
			'pages/*'	=> 'allow',		// allow static pages
		),

		// admins
		'1' => array
		(
			
			'*'		=> 'allow',		// allow everything
			'categories/*'	=> 'deny',		// deny everything
		),

		// users
		'2' => array
		(
			'*'		=> 'allow',		// allow everything

			// deny users controller, except login, logout, personal_details
			'users/*'	=> 'deny',
			'users/login'	=> 'allow',
			'users/logout'	=> 'allow',
		)
	);

	// 3. if the user is not logged in, the authorization object will not be used,
	//    so execute it manually to allow users see public stuff...
	if($this->Auth->user() == null)
	{
		$this->ArrayAcl->authorize($this, true);
	}

Part "1. init the Auth component authorize method" tells the application that the authorization
will be done by the ArrayAcl component from the ArrayAcl plugin.

Part "2. init the ArrayAcl components ACL array" defines the ACL which is used for access 
control. In first level array key values are user's group id values or '*' values that
represent all users (logged in or not).
Each of the values in the first level array has an array containing access control list
for the current user group (defined by the key).
Access control list array has ACO and allow/deny key-value pairs defining access to that ACO,
to the ARO (defined in the first level array key).
Basically the validation process goes top to bottom, and the last matching rule is applied 
as the final result.

Part "3. ..." just what the comment says...

Licence
------------------------------------------------------------------------------------------------
CakePHP Array ACL Plugin
Copyright (c) 2012, Miljenko Barbir (http://miljenkobarbir.com)

Licensed under The MIT License
Redistributions of files must retain the above copyright notice. 
