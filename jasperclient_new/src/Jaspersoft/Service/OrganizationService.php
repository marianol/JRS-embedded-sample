<?php
namespace Jaspersoft\Service;

use Jaspersoft\Dto\Organization\Organization;
use Jaspersoft\Tool\Util;
use Jaspersoft\Tool\RESTRequest;

class OrganizationService
{
	protected $service;
	protected $restUrl2;
	
	public function __construct(RESTRequest $service, $baseUrl)
	{
		$this->service = $service;
		$this->restUrl2 = $baseUrl;
	}
	
	private function make_url($organization = null, $params = null)
	{
        $url = $this->restUrl2 . '/organizations';
        if (!empty($organization)) {
            $url .= '/' . $organization;
            return $url;
        }
        if (!empty($params))
            $url .= '?' . Util::query_suffix($params);
        return $url;
    }
	
	/**
     * Use this function to search for organizations.
     *
     * Unlike the searchUsers function, full Organization objects are returned with this function.
     * You will receive an array with zero or more elements which are Organization objects that can be manipulated
     * or used with other functions requiring Organization objects.
     *
     * @param null $query
     * @param null $rootTenantId
     * @param null $maxDepth
     * @param null $includeParents
     * @param null $limit
     * @param null $offset
     * @return array
     */
    public function searchOrganizations($query = null, $rootTenantId = null, $maxDepth = null, $includeParents = null,
                                       $limit = null, $offset = null)
	{
        $result = array();
        $url = self::make_url(null, array(
            'q' => $query,
            'rootTenantId' => $rootTenantId,
            'maxDepth' => $maxDepth,
            'includeParents' => $includeParents,
            'limit' => $limit,
            'offset' => $offset));
        $resp = $this->service->prepAndSend($url, array(200, 204), 'GET', null, true, 'application/json', 'application/json');
        if (empty($resp))
            return $result;
        $orgs = json_decode($resp);
        foreach ($orgs->organization as $org) {
            $result[] = @new Organization($org->alias,
                $org->id,
                $org->parentId,
                $org->tenantName,
                $org->theme,
                $org->tenantDesc,
                $org->tenantFolderUri,
                $org->tenantNote,
                $org->tenantUri);
        }
        return $result;
    }
	
    /**
     * This function creates a new organization. If you do not wish for default users to be created
     * supply false as the second parameter.
     *
     * @param Organization $org
     * @param bool $defaultUsers
     * @return bool
     */
    public function createOrganization(Organization $org, $defaultUsers = true)
	{
        $url = self::make_url(null, array('defaultUsers' => $defaultUsers));
        $data = json_encode($org);
        if ($this->service->prepAndSend($url, array(201), 'POST', $data, false, 'application/json', 'application/json'))
            return true;
        return false;
    }

	/**
     * Delete an organization.
	 *
	 * @param Organization $org - organization object
	 * @return bool - based on success of request
	 * @throws Exception - if HTTP request doesn't succeed
	 */
	public function deleteOrganization(Organization $org)
	{
        $url = self::make_url($org->getId());
		if($this->service->prepAndSend($url, array(200, 204), 'DELETE')) {
			return true;
		}
		return false;
	}
	
    /**
     * This function updates an existing organization. Supply an organization object with the expected changes.
     *
     * @param Organization $org
     * @return bool
     */
    public function updateOrganization(Organization $org)
	{
        $url = self::make_url($org->getId());
        $data = json_encode($org);
        if ($this->service->prepAndSend($url, array(201, 200), 'PUT', $data, false, 'application/json', 'application/json'))
            return true;
        return false;
    }
	
	/**
	 * This function requests the single entity of one organization when supplied with the ID
	 *
	 * @param string id The ID of the organization
	 * @return Organization
	 */
	public function getOrganization($id)
	{
		$url = self::make_url($id);
		$data = $this->service->prepAndSend($url, array(200, 204), 'GET', null, true, 'application/json', 'application/json');
		$org = json_decode($data);
		return @new Organization(
				$org->alias,
                $org->id,
                $org->parentId,
                $org->tenantName,
                $org->theme,
                $org->tenantDesc,
                $org->tenantFolderUri,
                $org->tenantNote,
                $org->tenantUri
			    );
	}
}
