<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$PATH_community = t3lib_extMgm::extPath('community');

	// this is all here instead of in TS so that it is available in both, BE and FE
$TX_COMMUNITY = array(
	'applications' => array(
		'userProfile' => array(
			'classReference' => 'EXT:community/controller/class.tx_community_controller_userprofileapplication.php:tx_community_controller_UserProfileApplication',
			'label' => 'LLL:EXT:community/lang/locallang_applications.xml:userProfile',
			'accessControl' => array(
				'read' => 'LLL:EXT:community/lang/locallang_privacy.xml:privacy_userProfile_read'
			),
			'widgets' => array(
				'image' => array(
					'classReference' => 'EXT:community/controller/userprofile/class.tx_community_controller_userprofile_imagewidget.php:tx_community_controller_userprofile_ImageWidget',
					'label' => 'LLL:EXT:community/lang/locallang_applications.xml:userProfile_image',
					'actions' => array(), // TODO move execute() stuff to at least indexAction()
					'accessControl' => array(
						'read' => 'LLL:EXT:community/lang/locallang_privacy.xml:privacy_userProfile_imageWidget_read',
//						'addComment' => 'LLL:EXT:community/lang/locallang_privacy.xml:privacy_userProfile_imageWidget_addComment'
					),
					'actions' => array(
						'index'
					),
					'defaultAction' => 'index'
				),
				'personalInformation' => array(
					'classReference' => 'EXT:community/controller/userprofile/class.tx_community_controller_userprofile_personalinformationwidget.php:tx_community_controller_userprofile_PersonalInformationWidget',
					'label' => 'LLL:EXT:community/lang/locallang_applications.xml:userProfile_personalInformation',
					'actions' => array(), // TODO move execute() stuff to at least indexAction()
					'accessControl' => array(
						'read' => 'LLL:EXT:community/lang/locallang_privacy.xml:privacy_userProfile_personalInformationWidget_read'
					)
				),
				'profileActions' => array(
					'classReference' => 'EXT:community/controller/userprofile/class.tx_community_controller_userprofile_profileactionswidget.php:tx_community_controller_userprofile_ProfileActionsWidget',
					'label' => 'LLL:EXT:community/lang/locallang_applications.xml:userProfile_profileActions',
					'actions' => array( // those are not the actual profile actions, but controller actions
						'index',
						'addAsFriend',
						'editRelationship',
						'setRelationships',
						'removeAsFriend'
					),
					'defaultAction' => 'index',
					'accessControl' => false
				)
			)
		),
		'groupProfile' => array(
			'classReference' => 'EXT:community/controller/class.tx_community_controller_groupprofileapplication.php:tx_community_controller_GroupProfileApplication',
			'label' => 'LLL:EXT:community/lang/locallang_applications.xml:groupProfile',
			'widgets' => array(
				'profileActions' => array(
					'classReference' => 'EXT:community/controller/groupprofile/class.tx_community_controller_groupprofile_profileactionswidget.php:tx_community_controller_groupprofile_ProfileActionsWidget',
					'label' => 'LLL:EXT:community/lang/locallang_applications.xml:groupProfile_profileActions',
					'actions' => array( // those are not the actual profile actions, but controller actions
						'index',
						'joinGroup',
						'leaveGroup'
					),
					'defaultAction' => 'index',
					'accessControl' => false
				)
			)
		),
		'search' => array(
			'classReference' => 'EXT:community/controller/class.tx_community_controller_searchapplication.php:tx_community_controller_SearchApplication',
			'label' => 'LLL:EXT:community/lang/locallang_applications.xml:search',
			'accessControl' => false,
			'actions' => array(
				'index',
				'search'
			),
			'defaultAction' => 'index',
			'widgets' => array(
				'quickSearchInput' => array(
					'classReference' => 'EXT:community/controller/search/class.tx_community_controller_search_quicksearchinputwidget.php:tx_community_controller_search_QuickSearchInputWidget',
					'label' => 'LLL:EXT:community/lang/locallang_applications.xml:search_quickSearchInput',
					'actions' => array(
						'index'
					),
					'defaultAction' => 'index',
					'accessControl' => false
				)
			)
		),
		'userList' => array(
			'classReference' => 'EXT:community/controller/class.tx_community_controller_userlistapplication.php:tx_community_controller_UserListApplication',
			'accessControl' => false,
			'excludeFromPluginListing' => true,
			'action' => array(
				'index'
			),
			'defaultAction' => 'index'
		),
		'listGroups' => array(
			'classReference' => 'EXT:community/controller/class.tx_community_controller_listgroupsapplication.php:tx_community_controller_ListGroupsApplication',
			'label' => 'LLL:EXT:community/lang/locallang_applications.xml:listGroups',
			'accessControl' => false,
			'actions' => array( // those are not the actual profile actions, but controller actions
				'index'
			),
			'defaultAction' => 'index',
		),
		'editGroup' => array(
			'classReference' => 'EXT:community/controller/class.tx_community_controller_editgroupapplication.php:tx_community_controller_EditGroupApplication',
			'label' => 'LLL:EXT:community/lang/locallang_applications.xml:editGroup',
			'accessControl' => false,
			'actions' => array( // those are not the actual profile actions, but controller actions
				'index',
				'saveData',
				'inviteUser',
				'removeMember',
				'changeAdmin'
			),
			'defaultAction' => 'index',
		),
		'newGroup' => array(
			'classReference' => 'EXT:community/controller/class.tx_community_controller_newgroupapplication.php:tx_community_controller_NewGroupApplication',
			'label' => 'LLL:EXT:community/lang/locallang_applications.xml:newGroup',
			'accessControl' => false,
			'actions' => array( // those are not the actual profile actions, but controller actions
				'index',
				'newGroup'
			),
			'defaultAction' => 'index',
		),
		'privacy' => array(
			'classReference' => 'EXT:community/controller/class.tx_community_controller_privacyapplication.php:tx_community_controller_PrivacyApplication',
			'label' => 'LLL:EXT:community/lang/locallang_applications.xml:privacy',
			'accessControl' => false,
			'actions' => array(
				'index',
				'savePermissions'
			),
			'defaultAction' => 'index'
		)
	)
);

	// adding save+close+new buttons for some tables
t3lib_extMgm::addUserTSConfig('options.saveDocNew.tx_community_acl_role = 1');
t3lib_extMgm::addUserTSConfig('options.saveDocNew.tx_community_acl_rule = 1');
t3lib_extMgm::addUserTSConfig('options.saveDocNew.tx_community_friend = 1');

?>