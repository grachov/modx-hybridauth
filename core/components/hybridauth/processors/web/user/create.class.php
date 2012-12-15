<?php
require MODX_CORE_PATH . 'model/modx/processors/security/user/create.class.php';

class haUserCreateProcessor extends modUserCreateProcessor {
	public $classKey = 'haUser';
	public $languageTopics = array('core:default','core:user');
	public $permission = '';
	public $objectType = 'hauser';
	public $beforeSaveEvent = 'OnBeforeUserFormSave';
	public $afterSaveEvent = 'OnUserFormSave';


	/**
	 * Override in your derivative class to do functionality before the fields are set on the object
	 * @return boolean
	 */
	public function beforeSet() {
		$this->setProperty('passwordnotifymethod', 's');

		if (!$this->getProperty('username')) {
			$this->addFieldError('username', $this->modx->lexicon('field_required'));
		}
		if (!$this->getProperty('email')) {
			$this->setProperty('email', ' ');
		}

		return parent::beforeSet();
	}


	/**
	 * Add User Group memberships to the User
	 * @return array
	 */
	public function setUserGroups() {
		$memberships = array();
		$groups = $this->getProperty('groups',null);
		if ($groups !== null) {
			$groups = is_array($groups) ? $groups : $this->modx->fromJSON($groups);
			$groupsAdded = array();
			$idx = 0;
			foreach ($groups as $group) {
				if (in_array($group['usergroup'],$groupsAdded)) continue;
				/** @var modUserGroupMember $membership */
				$membership = $this->modx->newObject('modUserGroupMember');
				$membership->set('user_group',$group['usergroup']);
				$membership->set('role',$group['role']);
				$membership->set('member',$this->object->get('id'));
				$membership->set('rank',isset($group['rank']) ? $group['rank'] : $idx);
				$membership->save();
				$memberships[] = $membership;
				$groupsAdded[] = $group['usergroup'];
				$idx++;
			}
		}
		return $memberships;
	}

}

return 'haUserCreateProcessor';