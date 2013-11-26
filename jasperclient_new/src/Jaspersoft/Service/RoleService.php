<?php
namespace Jaspersoft\Service;

use Jaspersoft\Tool\RESTRequest;
use Jaspersoft\Tool\Util;
use Jaspersoft\Dto\Role\Role;

class RoleService
{
	protected $service;
	protected $restUrl2;
	
	public function __construct(RESTRequest $service, $baseUrl)
	{
		$this->service = $service;
		$this->restUrl2 = $baseUrl;
	}
	
	private function make_url($organization = null, $roleName = null, $params = null) {
        if(!empty($organization))
            $url = $this->restUrl2 . '/organizations/' . $organization . '/roles';
        else
            $url = $this->restUrl2 . '/roles';
        if (!empty($roleName))
            $url .= '/' . $roleName;
        // If a role name is defined, no parameters are expected
        else if (!empty($params))
            $url .= '?' . Util::query_suffix($params);
        return $url;
    }

    /**
     * Search for many or all roles on the server.
     * You can search by organization as well.
     *
     * @param null $organization
     * @param null $includeSubOrgs
     * @return array
     */
    public function getManyRoles($organization = null, $includeSubOrgs = null) {
        $result = array();
        $url = self::make_url($organization, null, array('includeSubOrgs' => $includeSubOrgs));
        $data = $this->service->prepAndSend($url, array(200, 204), 'GET', null, true, 'application/json', 'application/json');
        $data = (!empty($data)) ? json_decode($data, true) : null;
        if ($data === null)
            return $result;
        foreach ($data['role'] as $r)
            $result[] = @new Role($r['name'], $r['tenantId'], $r['externallyDefined']);
        return $result;
    }
	
    /** Get a Role by its name
     *
     * @param $roleName
     * @param $organization - Name of organization role belongs to
     * @return Role
     */
    public function getRole($roleName, $organization = null) {
        $url = self::make_url($organization, $roleName);
        $resp = $this->service->prepAndSend($url, array(200), 'GET', null, true, 'application/json', 'application/json');	
		$data = json_decode($resp);
		$data->externallyDefined = ($data->externallyDefined) ? 'true' : 'false';
        return @new Role($data->name, $data->tenantId, $data->externallyDefined);
    }
	
    /**
     * Add a new role.
     *
     * Provide a role object that represents the role you wish to add.
     *
     * @param Role $role - role to add (1 at a time)
     * @return bool - based on success of function
     * @throws Exception - if http request doesn't succeed
     */
    public function createRole(Role $role) {
        $url = self::make_url($role->getTenantId(), $role->getRoleName());
        if ($this->service->prepAndSend($url, array(201, 200), 'PUT', json_encode($role), false, 'application/json', 'application/json'))
            return true;
        return false;
    }
	
    /**
     * Remove a role currently in existence.
     *
     * Provide the Role object of the role you wish to remove. Use getRole() to retrieve Roles.
     *
     * @param Role $role
     * @internal param string $roleName - Name of the role to DELETE
     * @return bool - based on success of function
     */
	public function deleteRole(Role $role) {
        $url = self::make_url($role->getTenantId(), $role->getRoleName());
        if ($this->service->prepAndSend($url, array(204, 200), 'DELETE'))
            return true;
        return false;
	}
	
    /**
     * Update a role currently in existence.
     *
     * Provide the Role object of the role you wish to change, then a string of the new name
     * you wish to give the role. You can optionally provide a new tenantId if you wish to change
     * that as well.
     *
     * @param Role $role - Role object to be changed
     * @param string $oldName - previous name for the role
     * @return bool
     * @throws Exception - if http request does not succeed
     */
    public function updateRole(Role $role, $oldName = null) {
        $url = self::make_url($role->getTenantId(), $oldName);
        if ($this->service->prepAndSend($url, array(200, 201), 'PUT', json_encode($role), false, 'application/json', 'application/json'))
            return true;
        return false;
    }
	
}

?>