<?php
namespace Users\src\Users\Tools;

use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\Sql\Select;
use Zend\Paginator\Paginator;

class MyDbselect extends DbSelect
{
    public function count()
    {
    	$select = new Select();
    	$select->from('orgnization')->columns(array('org_name'=>'test'));
    
    	$statement = $this->sql->prepareStatementForSqlObject($select);
    	$result    = $statement->execute();
    	$row       = $result->current();
    	$this->rowCount = $row['org_name'];
    
    	return $this->rowCount;
    }
}

//$select = new Select();
//$adapter = new Adapter($driver);

?>