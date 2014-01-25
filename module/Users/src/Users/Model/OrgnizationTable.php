<?php
namespace Users\Model;

use Zend\Text\Table\Row;


use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class OrgnizationTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function saveOrgnization(Orgnization $org)
    {
        $data = array(
            'org_name'      => $org->org_name,
            'org_des'       => $org->org_des,
            'org_tel'       => $org->org_tel,            
            'org_address'   => $org->org_address,            
            'org_website'   => $org->org_website,            
            'org_logo'      => $org->org_logo,            
            'org_CT'        => $org->org_CT,            
            'org_LM'        => null, 
            'org_creater_id'=> $org->org_creater_id, 
                       
        );

        $id = (int)$org->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getOrgnization($id)) {
            	
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Orgnization ID does not exist');
            }
        }
    }
    
    /**
     * Get all orgnization
     * @return ResultSet
     */
    public function fetchAll()
    {
    	$resultSet = $this->tableGateway->select();
    	return $resultSet;
    }
    
    /**
     * Get orgnization by orgId
     * @param string $id
     * @throws \Exception
     * @return Row
     */    
    public function getOrgnization($id)
    {
    	$id  = (int) $id;
    	$rowset = $this->tableGateway->select(array('id' => $id));
    	$row = $rowset->current();
    	if (!$row) {
    		throw new \Exception("Could not find row $id");
    	}
    	return $row;
    }
    
    /**
     * Get Orgnization by Creater id
     * @param int $id
     * @throws \Exception
     * @return Row
     */
    public function getOrgnizationByCreaterId($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('org_creater_id' => $id));
        $row = $rowset->current();
    	if (!$row) {
    		throw new \Exception("Could not find row $id");
    	}
    	return $row;    
    }
    
    /**
     * Delete Orgnization by OrgId
     * @param string $id
     */
    public function deleteUser($id)
    {
    	$this->tableGateway->delete(array('id' => $id));
    }
        
}
