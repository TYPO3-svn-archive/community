<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Frank Nägler <typo3@naegler.net>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once($GLOBALS['PATH_community'] . 'controller/class.tx_community_controller_groupprofileapplication.php');
require_once($GLOBALS['PATH_community'] . 'model/class.tx_community_model_groupgateway.php');
require_once($GLOBALS['PATH_community'] . 'view/editgroup/class.tx_community_view_editgroup_index.php');

/**
 * Edit Group Application Controller
 *
 * @author	Frank Naegler <typo3@naegler.net>
 * @package TYPO3
 * @subpackage community
 */
class tx_community_controller_EditGroupApplication extends tx_community_controller_GroupProfileApplication implements tx_community_acl_AclResource {

	protected $messageAPILoaded = false;
	protected $accessManager    = null;

	/**
	 * constructor for class tx_community_controller_GroupProfileApplication
	 */
	public function __construct() {
		parent::__construct();

		$this->prefixId = 'tx_community_controller_EditGroupApplication';
		$this->scriptRelPath = 'controller/class.tx_community_controller_editgroupapplication.php';
		$this->name = 'editGroup';

		if (t3lib_extMgm::isLoaded('community_messages')) {
			require_once(t3lib_extMgm::extPath('community_messages').'classes/class.tx_communitymessages_api.php');
			$this->messageAPILoaded = true;
		}

		$this->accessManager = tx_community_AccessManager::getInstance();
		$this->getRequestedGroup();
	}

	/**
	 * does an initial access check
	 *
	 * @return	void
	 * @author	Ingo Renner <ingo@typo3.org>
	 */
	protected function checkAccess() {
			// TODO should be moved to some central place, should be made extendable
		if (is_null($this->requestedGroup)) {
				// @TODO throw Exception
			die('no group id given');
		}

		if ($this->getRequestingUser()->getUid() === 0) {
				// @TODO throw Exception
			die('no user logged in');
		}

		if (!$this->requestedGroup->isAdmin($this->getRequestingUser())) {
				// @TODO throw Exception
			die('not an admin of this group');
		}
	}

		// TODO refactor this method
	public function indexAction() {
		$this->checkAccess();

		$view = t3lib_div::makeInstance('tx_community_view_editGroup_Index');
		/* @var $view tx_community_view_editGroup_Index */
		$view->setTemplateFile($this->configuration['applications.']['editGroup.']['templateFile']);
		$view->setLanguageKey($this->LLkey);

		$formAction = $this->pi_getPageLink(
			$GLOBALS['TSFE']->id,
			'',
			array(
				'tx_community' => array(
					'editGroupAction' => 'saveData'
				)
			)
		);
		$view->setFormAction($formAction);

		$imgConf = $this->configuration['applications.']['editGroup.']['previewImage.'];

		$imagePath = (strlen($this->requestedGroup->getImage())) ? $this->requestedGroup->getImage() : $this->configuration['applications.']['editGroup.']['defaultIcon'];
		$imgConf['file'] = $imagePath;
		$cObj = t3lib_div::makeInstance('tslib_cObj');
		$view->setImage($cObj->cObjGetSingle($this->configuration['applications.']['editGroup.']['previewImage'], $imgConf));

		// make actions
		$actions = $this->configuration['applications.']['editGroup.']['memberlist.']['actions.'];
#debug($actions);
		$adminActions = array();
		$tmpMemberActions = array();
		$otherActions = array();
		foreach ($actions['admins.'] as $k => $v) {
			switch ($v) {
				case 'TEXT' :
				case 'HTML' :
				case 'IMAGE' :
					$adminActions[] = $this->cObj->cObjGetSingle($actions['admins.'][$k], $actions['admins.'][$k.'.']);
				break;
			}
		}
		foreach ($actions['tmpMembers.'] as $k => $v) {
			switch ($v) {
				case 'TEXT' :
				case 'HTML' :
				case 'IMAGE' :
					$tmpMemberActions[] = $this->cObj->cObjGetSingle($actions['tmpMembers.'][$k], $actions['tmpMembers.'][$k.'.']);
				break;
			}
		}
		foreach ($actions['other.'] as $k => $v) {
			switch ($v) {
				case 'TEXT' :
				case 'HTML' :
				case 'IMAGE' :
					$otherActions[] = $this->cObj->cObjGetSingle($actions['other.'][$k], $actions['other.'][$k.'.']);
				break;
			}
		}
		$view->setAdminActions($adminActions);
		$view->setTmpMembersActions($tmpMemberActions);
		$view->setOtherActions($otherActions);

		return $view->render();
	}

		// TODO refactor this method
	public function saveDataAction() {
		// @TODO: localize all messages
		$communityRequest = t3lib_div::GParrayMerged('tx_community');
		$groupGateway = t3lib_div::makeInstance('tx_community_model_GroupGateway');
		$userGateway = t3lib_div::makeInstance('tx_community_model_UserGateway');
		/**
		 * @var tx_community_model_Group
		 */
		$group = $groupGateway->findRequestedGroup();
		$user  = $userGateway->findCurrentlyLoggedInUser();

		$ajaxAction = $communityRequest['ajaxAction'];
		switch ($ajaxAction) {
			case 'saveGeneral':
				if ($this->saveGeneral()) {
					$result = "{'status': 'success', 'msg': 'saved'}";
				} else {
					$result = "{'status': 'error', 'msg': 'not saved'}";
				}
			break;
			case 'saveImage':
				if ($group->isAdmin($user)) {
					$fileFunc = t3lib_div::makeInstance('t3lib_basicFileFunctions');
					$upPath = $this->configuration['applications.']['editGroup.']['uploadPath'];
					$fileName = $_FILES['tx_community']['name']['imageFile'];
					$tmpFile  = $_FILES['tx_community']['tmp_name']['imageFile'];
					$pathInfo = pathinfo($fileName);
					$dir = t3lib_div::getFileAbsFileName($upPath);
					$newName = md5($fileName) .'.'. $pathInfo['extension'];
					if (move_uploaded_file($tmpFile, $dir.$newName)) {
						t3lib_div::fixPermissions($dir.$newName);
						$group->setImage($newName);
						if ($group->save()) {
							$imgConf = $this->configuration['applications.']['editGroup.']['previewImage.'];
							$imgConf['file'] = $upPath.$newName;
							$cObj = t3lib_div::makeInstance('tslib_cObj');
							$genImage = $cObj->cObjGetSingle('IMG_RESOURCE', $imgConf);
							list($width,$height) = getimagesize($genImage);
							$result = "{'status': 'success', 'msg': 'image uploaded', 'newImage': '{$genImage}', 'newWidth': '{$width}', 'newHeight': '{$height}'}";
						} else {
							$result = "{'status': 'error', 'msg': 'error while save'}";
						}
					} else {
						$result = "{'status': 'error', 'msg': 'can't upload file'}";
					}
				} else {
					$result = "{'status': 'error', 'msg': 'not admin'}";
				}
			break;
			case 'changeMemberStatus':
				if ($group->isAdmin($user)) {
					switch($communityRequest['do']) {
						case 'makeAdmin':
							$newAdmin = $userGateway->findById($communityRequest['memberUid']);
							if ($newAdmin instanceof tx_community_model_User) {
								$group->addAdmin($newAdmin);
								$group->removeAdmin($user);
								if ($group->save()) {
									$result = "{'status': 'success', 'msg': 'saved'}";
								} else {
									$result = "{'status': 'error', 'msg': 'not saved'}";
								}
							}
						break;
						case 'confirmRequest':
							$newMember = $userGateway->findById($communityRequest['memberUid']);
							if ($newMember instanceof tx_community_model_User) {
								if ($group->confirmMember($newMember)) {
									$result = "{'status': 'success', 'msg': 'saved'}";
								} else {
									$result = "{'status': 'error', 'msg': 'not saved'}";
								}
							}
						break;
						case 'rejectRequest':
							$newMember = $userGateway->findById($communityRequest['memberUid']);
							if ($newMember instanceof tx_community_model_User) {
								if ($group->rejectMember($newMember)) {
									$result = "{'status': 'success', 'msg': 'saved'}";
								} else {
									$result = "{'status': 'error', 'msg': 'not saved'}";
								}
							}
						break;
						case 'removeMember':
							$member = $userGateway->findById($communityRequest['memberUid']);
							if ($member instanceof tx_community_model_User) {
								if ($group->isMember($member)) {
									$group->removeMember($member);
									if ($group->save()) {
										$result = "{'status': 'success', 'msg': 'saved'}";
									} else {
										$result = "{'status': 'error', 'msg': 'not saved'}";
									}
								} else {
									$result = "{'status': 'error', 'msg': 'not a memeber of this group'}";
								}
							}
						break;
					}
				} else {
					$result = "{'status': 'error', 'msg': 'not admin'}";
				}
			break;
			case 'inviteMember':
				switch($communityRequest['do']) {
					case 'invite':
						$requestedGroup = $groupGateway->findRequestedGroup();
						if (is_null($requestedGroup)) {
							// @TODO: throw exception
							die('no group in request');
						}

						$status = 'success';
						$uidsToInvite = t3lib_div::trimExplode(';', $communityRequest['inviteUids']);
						foreach ($uidsToInvite as $uid) {
							$inviteUser = $this->userGateway->findById($uid);
							if (is_null($inviteUser)) {
								$status = 'error';
								$message = 'unknown user';
								break;
							}
							if ($this->accessManager->isFriendOfCurrentlyLoggedInUser($inviteUser)) {
								$recipients[] = $inviteUser;
								$message = 'users invited';
							} else {
								$status = 'error';
								$message = 'is not a friend';
								break;
							}
						}
						if ($status == 'success') {
							$inviteUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$this->pi_getPageLink(
								$this->configuration['pages.']['groupProfile'],
								'',
								array(
									'tx_community' => array(
										'group' => $requestedGroup->getUid(),
										'profileAction' => 'joinGroup'
									)
								)
							);
							$subject = 'invite for group';
							$bodytext = "
								Einladung zur Gruppe.<br/>
								<a href=\"{$inviteUrl}\">Einladung annehmen</a>
							";
							if ($this->messageAPILoaded) {
								tx_communitymessages_API::sendSystemMessage($subject, $bodytext, $recipients);
							}
						}
						$result = "{'status': '{$status}', 'msg': '{$message}'}";
					break;
					case 'search':
					default:
						$searchTerm = t3lib_div::_GP('q');
						$friends = $this->userGateway->findFriends();
						$returnData = array();
						if (count($friends)) {
							foreach ($friends as $friend) {
								if (strpos(strtolower($friend->getNickname()), strtolower($searchTerm)) !== false) {
									$returnData[] = $friend->getNickname().'|'.$friend->getUid();
								}
							}
						}
						if (count($returnData)) {
							echo implode("\n", $returnData) . "\n";
						} else {
							echo '';
						}
						die();
					break;
				}
			break;
			default:
				$result = "{'status': 'error', 'msg': 'no ajax action'}";
			break;
		}
		echo $result;
		die();
	}

		// TODO refactor this method
	protected function saveGeneral() {
		$communityRequest = t3lib_div::GParrayMerged('tx_community');
		$groupGateway = t3lib_div::makeInstance('tx_community_model_GroupGateway');
		$userGateway = t3lib_div::makeInstance('tx_community_model_UserGateway');
		/**
		 * @var tx_community_model_Group
		 */
		$group = $groupGateway->findRequestedGroup();
		$user  = $userGateway->findCurrentlyLoggedInUser();

		if ($group->isAdmin($user)) {
			$group->setName($communityRequest['groupName']);
			$group->setDescription($communityRequest['groupDescription']);
			$group->setGrouptype($communityRequest['groupType']);
			if ($group->save()) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * returns the Resource identifier
	 *
	 * @return string
	 */
	public function getResourceId() {
		return $this->name . '_update_' . $this->getRequestedGroup()->getUid();
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/community/controller/class.tx_community_controller_editgroupapplication.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/community/controller/class.tx_community_controller_editgroupapplication.php']);
}

?>