<?php
/* ==========================================================================

 Copyright (C) 2005 - 2012 Jaspersoft Corporation. All rights reserved.
 http://www.jaspersoft.com.

 Unless you have purchased a commercial license agreement from Jaspersoft,
 the following license terms apply:

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as
 published by the Free Software Foundation, either version 3 of the
 License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero  General Public License for more details.

 You should have received a copy of the GNU Affero General Public  License
 along with this program. If not, see <http://www.gnu.org/licenses/>.

=========================================================================== */

namespace Jasper;

// Objects used by the class
require_once 'Constants.php';
require_once 'RestRequest.php';
require_once 'User.php';
require_once 'Organization.php';
require_once 'Role.php';
require_once 'Attribute.php';
require_once 'ResourceDescriptor.php';


// PEAR Packages
require_once 'XML/Serializer.php';
// require_once 'XML/Unserializer.php';

/** JasperClient library for PHP
 *
 * This library was created to make interactions with the REST API simplified for
 * those people using PHP when developing services that are to interact with JasperServer
 *
 * author: gbacon
 * date: 06/07/2012
 */
class JasperClient {

	protected $hostname;
	protected $port;
	protected $username;
	protected $password;
	protected $orgId;
	protected $baseUrl;
	private $restReq;
	private $restUrl;
	private $restUrl2;

	/***> INTERNAL FUNCTIONS <***/

	/** Constructor for JasperClient. All these values are required to be defined so that
	 * the client can function properly
	 *
	 * @param string $hostname - Hostname of the JasperServer that the API is running on
	 * @param int|string $port - Port of the same server
	 * @param string $username - Username for authentication
	 * @param string $password - Password for authetication
	 * @param string $baseUrl - base URL (i.e: /jasperserver-pro or /jasperserver (community edition))
	 */
	public function __construct($hostname = 'localhost', $port = '8080', $username = null, $password = null, $baseUrl = "/jasperserver-pro", $orgId = null)
	{
		$this->hostname = $hostname;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;
		$this->baseUrl = $baseUrl;
		$this->orgId = $orgId;

		$this->restReq = new RestRequest; // This object is recycled
		if (!empty($this->orgId)) {
			$this->restReq->setUsername($this->username .'|'. $this->orgId);
		} else {
			$this->restReq->setUsername($this->username);	// Configure userpwd for our req object
		}
		$this->restReq->setPassword($this->password);
		$this->restUrl = PROTOCOL . $this->hostname . ":" . $this->port . $this->baseUrl . BASE_REST_URL;
		$this->restUrl2 = PROTOCOL . $this->hostname . ':' . $this->port . $this->baseUrl . BASE_REST2_URL;
	}

	/** Internal function that prepares and send the request. This function validates that
	 * the status code returned matches the $expectedCode provided and returns a bool
	 * based on that
	 *
	 * @param string $url - URL to be called
	 * @param string $verb - verb to be used
	 * @param int|string $expectedCode - Expected HTTP status code
	 * @param string $reqBody - The body of the request (POST/PUT)
	 * @return boolean - true if expectedCode == statusCode; if no match, returns status code
	 * @throws Exception - If the statusCodes do not match
	 */
	protected function prepAndSend($url, $expectedCode = 200, $verb = null, $reqBody = null, $returnData = false) {
		$expectedCode = (integer) $expectedCode;
		$this->restReq->flush();
		$this->restReq->setUrl($url);
		if ($verb !== null) {
			$this->restReq->setVerb($verb);
		}
		if ($reqBody !== null) {
			$this->restReq->buildPostBody($reqBody);
		}
		$this->restReq->execute();
		$statusCode = $this->restReq->getResponseInfo();
		$statusCode = $statusCode['http_code'];
		if ($statusCode !== $expectedCode) {
			throw new \Exception('Operation unsuccessful. Unexpected HTTP code returned: ' . $statusCode . ' The expected code was: ' . $expectedCode);
			return $statusCode;
		}
		if($returnData == true) {
			return $this->restReq->getResponseBody();
		}
		return true;
	}

	/***> ATTRIBUTE SERVICE <***/

	/** Retrieve attributes of a user
	 *
	 * @param User $user - user object of the user you wish to retrieve data about
	 * @returns Attribute - an array of attribute objects
	 * @throws Exception - if HTTP fails
	 */
	public function getAttributes(User $user) {
		$result = array();
		$url = $this->restUrl . ATTRIBUTE_BASE_URL . '/' . $user->getUsername();
		if($user->getTenantId() !== null) {
			$url .= PIPE . $user->getTenantId();
		}
		if ($data = $this->prepAndSend($url, 200, 'GET', null, true)) {
			$xml = new \SimpleXMLElement($data);
		} else {
			return false;
		}
		foreach ($xml->Item as $item) {
			$tempAttribute = new Attribute(
				$item->attrName,
				$item->attrValue);
			$result[] = $tempAttribute;
		}
		return $result;
	}

	/** Change attributes of a user or create new attribute
	 *
	 * Note: If you want to update an attribute, supply an attribute object with an existing attribute name
	 * but a different value. If you have multiple attributes with the same name, this function will NOT work
	 * as you may expect it to. The API overwrites the data that already matches
	 *
	 * Note 2: This function could be optimized so that multiple calls aren't being made to update more than
	 * one attribute at a ime
	 *
	 * @param User $user - user object of user whos attributes you wish to change
	 * @param Attribute $attributes - array of attributes or one attribute object
	 * @throws Exception - if HTTP returns an error status code
	 */
	public function postAttributes(User $user, $attributes) {
		$url = $this->restUrl . ATTRIBUTE_BASE_URL . '/' . $user->getUsername();
		if ($user->getTenantId() !== null) {
			$url .= PIPE . $user->getTenantId();
		}
		if (is_array($attributes)) {
			foreach ($attributes as $attribute) {
				$this->prepAndSend($url, 201, 'PUT', $attribute->asXML());
			}
		} else {
			$this->prepAndSend($url, 201, 'PUT', $attributes->asXML());
		}
	}

	/***> USER SERVICE <***/

	/** Retrieve users from the server. If one user is found, simply a User object is returned.
	 * If multiple users are found, an array filled with User objects is returned instead.
	 *
	 * @param string $searchTerm - part of user name you would like to search for
	 *
	 * @return User or User[]
	 * @throws Exception if HTTP request fails
	 */
	public function getUsers($searchTerm = null) {
		$url = $this->restUrl . USER_BASE_URL . '/' . $searchTerm;
		$result = array();

		if($data = $this->prepAndSend($url, 200, 'GET', null, true)) {
			$xml = new \SimpleXMLElement($data);
		}
		foreach ($xml->user as $user) {
			$tempUser = new User(
				$user->username,
				$user->password,
				$user->emailAddress,
				$user->fullName,
				$user->tenantId,
				$user->roles,
				$user->enabled,
				$user->externallyDefined,
				$user->previousPasswordChangeTime);
			$result[] = $tempUser;
		}
		if (count($result) == 1) {
			return $result[0];
		}
		return $result;
	}

	/** PUT User(s)
	 *
	 * This function adds NEW users. It will accept an array of User objects,
	 * or one User object to add to the database
	 *
	 * @param User $users - single User object or array of User objects to be created
	 * @return boolean - based on success of function
	 */
	public function putUsers($users) {
		$url = $this->restUrl . USER_BASE_URL . '/';
		$xml = null;
		if (is_array($users)) {
			foreach ($users as $u)
			{
				$xml .= $u->asXML();
			}
		} else {
			$xml = $users->asXML();
		}
		$this->prepAndSend($url, 201, 'PUT', $xml);
		return true;
	}

	/** POST User
	 *
	 * This function UPDATES a user. You can only update one user at a time.
	 * the best practice is to retrieve a user object from the server initially,
	 * make modifications to the user as needed, and then POST the updates using this
	 * function. It is not advised to create a User object from scratch to make updates
	 *
	 * @param User $user - single User object
	 * @return boolean - based on success of function
	 */
	public function postUser(User $user) {
		$url = $this->restUrl . USER_BASE_URL . '/' . $user->getUsername() . PIPE . $user->getTenantId();
		$xml = $user->asXML();
		if ($this->prepAndSend($url, 200, 'POST', $xml)) {
			return true;
		}
		return false;
	}

	/** This function will delete a user, only one user
	 *
	 * First get the user using getUsers(), then provide the user you wish to delete
	 * as the parameter for this function
	 *
	 * @param User $user - user to delete
	 * @return boolean - based on success of function
	 */
	public function deleteUser(User $user) {
		$url = $this->restUrl . USER_BASE_URL . '/' . $user->getUsername();
		if ($user->getTenantId() !== null) { $url .= PIPE . $user->getTenantId(); }
		if ($this->prepAndSend($url, 200, 'DELETE')) {
			return true;
		}
		return false;
	}

	/***> ORGANIZATION SERVICE <***/

	/** This function retrieves an organization and its information by ID
	 *
	 * @param string $org - organization id (i.e: "organization_1")
	 * @param boolean $listSub - If this is true, suborganizations are only retrieved
	 * @return Organization - object that represents organization & its data
	 * @throws Exception - if HTTP request doesn't respond as expected
	 */
	public function getOrganization($org, $listSub = false) {
		$url = $this->restUrl . ORGANIZATION_BASE_URL . '/' . $org;
		if ($listSub == true) { $url .= '?listSubOrgs=true'; }
		if($data = $this->prepAndSend($url, 200, 'GET', null, true)) {
			$xml = new \SimpleXMLElement($data);
			if($listSub == true) {
				$arrayResult = array();
				// This recursion simplifies Serializer handling and unknown responses (may only have 0 or more suborganizations, etc)
				// However making multiple API calls may see scaling issues with large amounts of data
				foreach($xml->tenant as $t) {
					$arrayResult[] = $this->getOrganization($t->id, false);
				}
				if (count($arrayResult) > 1) {
					return $arrayResult;
				}
				return $arrayResult[0];
			}
			$orgObj = new Organization(
				$xml->alias,
				$xml->id,
				$xml->parentId,
				$xml->tenantName,
				$xml->theme,
				$xml->tenantDesc,
				$xml->tenantFolderUri,
				$xml->tenantNote,
				$xml->tenantUri);
		} else {
			return false;
		}
		return $orgObj;
	}

	/** This function creates an organization on the server you must provide a
	 * built organization object to it as a parameter
	 *
	 * @param Organization $org - organization object to add
	 * @return boolean - based on success of request
	 * @throws Exception - if HTTP request doesn't signify success
	 */
	public function putOrganization(Organization $org) {
		$url = $this->restUrl . ORGANIZATION_BASE_URL;
		$xml = $org->asXML();
		if($this->prepAndSend($url, 201, 'PUT', $xml)) {
			return true;
		}
		return false;
	}

	/** Delete an organization
	 *
	 * @param Organization $org - organization object
	 * @return boolean - based on success of request
	 * @throws Exception - if HTTP request doesn't succeed
	 */
	public function deleteOrganization(Organization $org) {
		$url = $this->restUrl . ORGANIZATION_BASE_URL . '/' . $org->getId();
		if($this->prepAndSend($url, 200, 'DELETE')) {
			return true;
		}
		return false;
	}

	/** Update an organisation
	 *
	 * It is suggested that you use the getOrganization function to retrieve an object to be updated
	 * then from there you can modify it using the set functions, and then provide it to this function
	 * to be udpated on the server side. Integrity checks are not made through this library, but
	 * any errors retrieved by the server do raise an Exception
	 *
	 * @param Organization $org - organisation object
	 * @return boolean - based on success of request
	 * @throws Exception - if HTTP request doesn't succeed
	 */
	public function postOrganization(Organization $org) {
		$url = $this->restUrl . ORGANIZATION_BASE_URL . '/' . $org->getId();
		if($this->prepAndSend($url, 200, 'POST', $org->asXML())) {
			return true;
		}
		return false;
	}

	/***> ROLE SERVICE <***/

	/** Retrieve existing roles
	 *
	 * Returns all roles that match $searchTerm (results can be >1). If you wish to retrieve all roles in a
	 * suborganization, set $searchTerm to an empty string and define the suborganization
	 * i.e: $jasperclient->getRoles('', 'organization_1');
	 *
	 * @param string $searchTerm - search the roles for matching values - returns multiple if multiple matches
	 * @param string $tenantId - if the role is part of an organization, be sure to add tenantId
	 * @return Role role - role object that represents the role
	 * @throws Exception - if http request doesn't succeed
	 */
	public function getRoles($searchTerm = null, $tenantId = null) {
		$result = array();
		$url = $this->restUrl . ROLE_BASE_URL;

		if ($searchTerm !== null) { $url .= '/' . $searchTerm; }
		if ($tenantId !== null) { $url .= PIPE . $tenantId; }

		if($data = $this->prepAndSend($url, 200, 'GET', null, true)) {
			$xml = new \SimpleXMLElement($data);
		} else {
			return false;
		}
		foreach ($xml->role as $role) {
			$tempRole = new Role($role->roleName,
					$role->externallyDefined,
					$role->tenantId);
			$result[] = $tempRole;
		}
		if(count($result) == 1) {
			return $result[0];
		}
		return $result;
	}

	/** Add a new role
	 *
	 * Provide a role object that represents the role you wish to add
	 *
	 * @param Role $role - role to add (1 at a time)
	 * @return boolean - based on success of function
	 * @throws Exception - if http request doesn't succeed
	 */
	public function putRole(Role $role) {
		$url = PROTOCOL . $this->hostname . ':' . $this->port . $this->baseUrl . BASE_REST_URL . ROLE_BASE_URL;
		if($this->prepAndSend($url, 201, 'PUT', $role->asXML())) {
			return true;
		}
		return false;
	}

	/** Remove a role currently in existence
	 *
	 * Provide the role object of the role you wish to remove. Use getRole() to retrieve ROLEs
	 *
	 * @param string $roleName - Name of the role to DELETE
	 * @return boolean - based on success of function
	 * @throws Exception - if http request doesn't succeed
	 */
	public function deleteRole(Role $role) {
		$url = $this->restUrl . ROLE_BASE_URL . '/' . $role->getRoleName();
		$tenantId = $role->getTenantId();
		if ($tenantId !== null || $tenantId !== '') { $url .= PIPE . $tenantId; }
		if($this->prepAndSend($url, 200, 'DELETE')) {
			return true;
		}
		return false;
	}

	/** Update a role currently in existence
	 *
	 * Provide the Role object of the role you wish to change, then a string of the new name
	 * you wish to give the role. You can optionally provide a new tenantId if you wish to change
	 * that as well.
	 *
	 * @param Role $role - Role object to be changed
	 * @param string $newName - new name for the role
	 * @return boolean - based on success of function
	 * @throws Exception - if http request does not succeed
	 */
	public function postRole(Role $role, $newName = null) {
		$url = $this->restUrl . ROLE_BASE_URL . '/' . $role->getRoleName();
		if ($role->getTenantId() !== '' && $role->getTenantId() !== null) {
			$url .= PIPE . $role->getTenantId();
		}
		if($newName !== null) {
			$role->setRoleName($newName);
		}
		if($this->prepAndSend($url, 200, 'POST', $role->asXML())) {
			return true;
		}
		return false;
	}

	/***> REPORT SERVICE <***/

	/**
	 * This function runs and retrieves the binary data of a report
	 *
	 * Note: This function utilizes the "rest_v2" service.
	 *
	 * @param string $uri - URI for the report you wish to run
	 * @param string $format - The format you wish to receive the report in (default: pdf)
	 * @param string $page - Request a specific page
	 * @param array $inputControls - associative array of key => value for any input controls
	 * @return string - the binary data of the report to be handled by external functions
	 */
	public function runReport($uri, $format = 'pdf', $page = null, $inputControls = null) {
		$url = $this->restUrl2 . REPORTS_BASE_URL . $uri . '.' . $format;
		if(!(empty($page) && empty($inputControls))) {
			$url .= '?' . http_build_query(array('page' => $page) + (array) $inputControls);
		}
		$binary = $this->prepAndSend($url, 200, 'GET', null, true);
		return $binary;
	}

	/***> REPOSITORY SERVICE <***/

	/**
	 * This function retrieves the Resources from the server. It returns an array consisting of ResourceDescriptor objects that represnt the data.
	 *
	 * @param string $uri
	 * @param string $query
	 * @param string $wsType
	 * @param string $recursive
	 * @param string $limit
	 * @return array
	 */
	public function getRepository($uri = null, $query = null, $wsType = null, $recursive = null, $limit = null) {
		$url = $this->restUrl . '/resources';
		$suffix = http_build_query(array('q' => $query, 'type' => $wsType, 'recursive' => $recursive, 'limit' => $limit));
		$result = array();

		if(!empty($uri)) { $url .= $uri; }
		if (!empty($suffix)) { $url .= '?' . $suffix; }
		$data = $this->prepAndSend($url, 200, 'GET', null, true);
		$xml = new \SimpleXMLElement($data);
		foreach ($xml->resourceDescriptor as $rd) {
			$obj = ResourceDescriptor::createFromXML($rd->asXML());
			$result[] = $obj;
		}
		return $result;
	}

	/** This function retrieves the descriptor for the resource at $path in form $file.
	 *  for other paramaters.. see web services documentation
	 *
	 * @param string $path
	 * @param string $file
	 * @param string $ic_get_query_data
	 * @param string $p_param
	 * @param string $pl_param
	 * @return \Jasper\ResourceDescriptor
	 */
	public function getResource($path, $file = null, $ic_get_query_data = null, $p_param = null, $pl_param = null) {
		$url = $this->restUrl . '/resource' . $path;
		$suffix = http_build_query(array('file' => $file, 'IC_GET_QUERY_DATA' => $ic_get_query_data));
		if (!empty($suffix)) { $url .= '?' . $suffix; }
		$data = $this->prepAndSend($url, 200, 'GET', null, true);

		return ResourceDescriptor::createFromXML($data);
	}

	public function putResource($path, $rd) {
		// TODO
	}

	public function postResource($path, $rd) {
		// TODO
	}

	/** This function deletes a resource
	 * will only succeed if certain requirments are met. See "Web Services Guide" to see these requirements.
	 *
	 * @param string $path - path to resource to be deleted
	 * @return boolean
	 */
	public function deleteResource($path) {
		$url = $this->restUrl . '/resource' . $path;
		$result = $this->prepAndSend($url, 200, 'DELETE');
		return $result;
	}
}

?>