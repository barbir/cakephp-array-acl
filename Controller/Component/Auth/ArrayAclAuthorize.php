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
App::uses('BaseAuthorize', 'Controller/Component/Auth');

class ArrayAclAuthorize extends BaseAuthorize
{
	public function authorize($user, CakeRequest $request)
	{
		return $this->_Controller->ArrayAcl->authorize($this->_Controller);
	}
}
?>