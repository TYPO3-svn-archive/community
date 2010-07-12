<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Pascal Jungblut <mail@pascalj.de>
*
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

/**
 * Repository for Tx_Community_Domain_Model_AclRole
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @author Pascal Jungblut <mail@pascalj.com>
 */
class Tx_Community_Domain_Repository_AclRoleRepository extends Tx_Extbase_Persistence_Repository {
	public function findDefault(Tx_Community_Domain_Model_User $user, $code = NULL) {
		$query = $this->createQuery();
		if($code === NULL) {
			return $query->matching(
				$query->logicalAnd(
					$query->logicalNot(
						$query->equals('defaultRole', Tx_Community_Domain_Model_AclRole::NOT_DEFAULT_ROLE)
					),
					$query->equals('owner', $user)
				)
			)->execute();
		} else {
			return $query->matching(
				$query->logicalAnd(
					$query->equals('owner', $user),
					$query->equals('defaultRole', $code)
				)
			)->execute();
		}
	}
}
?>