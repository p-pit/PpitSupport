<?php
namespace PpitSupport\Model;

use PpitContact\Model\Community;
use PpitContact\Model\Contract;
use PpitCore\Model\Context;
use PpitCore\Model\Generic;
use PpitCore\Model\Instance;
use PpitDocument\Model\Document;
use SplFileObject;
use Zend\Db\Sql\Where;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ImapMessage implements InputFilterAwareInterface
{
	public $id;
    public $instance_id;
    public $contract_id;
    public $identifier;
    public $type;
    public $content;
    public $status;
    public $update_time;

    // Joined properties
    public $customer_name;
    public $supplyer_name;

    // Transient properties
    public $files;
    public $toaddress;
	public $fromaddress;
	public $subject;
	public $body;
    
    protected $inputFilter;

    // Static fields
    private static $table;

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->contract_id = (isset($data['contract_id'])) ? $data['contract_id'] : null;
        $this->identifier = (isset($data['identifier'])) ? $data['identifier'] : null;
        $this->type = (isset($data['type'])) ? $data['type'] : null;
        $this->content = (isset($data['content'])) ? $data['content'] : null;
        $this->status = (isset($data['status'])) ? $data['status'] : null;
        $this->update_time = (isset($data['update_time'])) ? $data['update_time'] : null;
        
        // Joined properties
        $this->customer_name = (isset($data['customer_name'])) ? $data['customer_name'] : null;
        $this->supplyer_name = (isset($data['supplyer_name'])) ? $data['supplyer_name'] : null;
    }

    public function toArray() {

    	$data = array();
    	$data['id'] = (int) $this->id;
    	$data['contract_id'] = (int) $this->contract_id;
    	$data['identifier'] = $this->identifier;
    	$data['type'] = $this->type;
    	$data['content'] = $this->content;
    	$data['status'] = $this->status;
    	 
    	return $data;
	}
	
	public static function getList($major, $dir, $filter = array())
	{
		$select = ImapMessage::getTable()->getSelect()->order(array($major.' '.$dir, 'update_time DESC'));
		$where = new Where;
		foreach ($filter as $property => $value) {
			$where->like($property, '%'.$value.'%');
		}
		$select->where($where);
		$cursor = ImapMessage::getTable()->selectWith($select);
		$messages = array();
		foreach ($cursor as $message) $messages[$message->identifier] = $message;
		return $messages;
	}

	public static function get($id, $column = 'id')
	{
		$context = Context::getCurrent();
		$message = ImapMessage::getTable()->get($id, $column);

		// Retrieve the contract
		if ($message->contract_id) {
			$contract = Contract::getTable()->get($message->contract_id);
	
			// Retrieve the customer
			$customer = Community::get($contract->customer_community_id);
			$message->customer_name = $customer->name;
	
			// Retrieve the supplyer
			$supplyer = Community::get($contract->supplyer_community_id);
			$message->supplyer_name = $supplyer->name;
		}

		return $message;
	}

	public static function instanciate($type = null, $content = null)
	{
		$context = Context::getCurrent();
		$message = new Message;
		$message->type = $type;		
		$message->content = $content;
		return $message;
	}

	public static function scanMailbox($mailbox)
	{
		$context = Context::getCurrent();
		$config = $context->getConfig();

		// Retrieve the already mapped messages
		$messages = ImapMessage::getList('identifier', 'ASC', array('type' => $mailbox));

		// Scan the mailbox
		$mbox = imap_open ($config['ppitSupportSettings']['supportInboxServer'].$mailbox, $config['ppitSupportSettings']['supportInboxUser'], $config['ppitSupportSettings']['supportInboxPassword']);
		$messageNumbers = imap_search($mbox, 'ALL');
		$result = array();
		foreach ($messageNumbers as $messageNumber) {
			$message = $messages[$messageNumber];
			if (!$message) $message = ImapMessage::instanciate($mailbox);
			$message->toaddress = imap_headerinfo($mbox, 1)->toaddress;
			$message->fromAddress = imap_headerinfo($mbox, 1)->fromaddress;
			$message->subject = imap_headerinfo($mbox, 1)->subject;
			$message->body = imap_qprint(imap_body($mbox, 1));
			$result[] = $message;
		}
		imap_close($mbox);
		return $result;
	}
	
	public function loadData($data, $files)
	{
		$this->files = $files;
		return 'OK';
	}

	public function loadDataFromRequest($request)
	{
		$context = Context::getCurrent();
		
		// Retrieve the data from the request
		$data = array();
		$files = $request->getFiles()->toArray();
		$return = $this->loadData($data, $files);
		if ($return != 'OK') throw new \Exception('View error');
	}

	public function add($type)
	{
		$context = Context::getCurrent();
		$this->id = null;

	    if ($this->files) {
    		if ($context->getCommunityId()) {
    			$community = Community::get($context->getCommunityId());
    			$root_id = $community->root_document_id;
    		}
    		else $root_id = Document::getTable()->get(0, 'parent_id')->id; 
    		$document = Document::instanciate($root_id);
    		$document->files = $this->files;
    		$document->saveFile();
    		$document_id = $document->save();
    		$this->import($type, $document_id);
    	}
		ImapMessage::getTable()->save($this);
    	return ('OK');
	}

	public function import($type, $document_id, $ignoreFirst = true)
	{
		$context = Context::getCurrent();
		$config = $context->getConfig();
		$maxRows = $config['ppitOrderSettings']['orderImportMaxRows'];
		$properties = $context->getConfig()['ppitOrderSettings']['orderImportProperties'];
		$filePath = 'data/documents/'.$document_id;
	
		$this->type = $type;
	
		ini_set('auto_detect_line_endings', true);
		$file = new SplFileObject($filePath, 'r');
		$file->setFlags(SplFileObject::READ_CSV);
		$file->setCsvControl(';');
		$rows = array(); $first = TRUE;
		foreach($file as $row) {
			if ((!$first || !$ignoreFirst) && count($row) > 0) {
				$content = false;
				foreach ($row as $cell) if ($cell) $content = true;
				if ($content) $rows[] = $row;
			}
			$first = FALSE;
		}
	
		// Number of rows limitation
		$nbRows = ($ignoreFirst) ? count($rows) -1 : count($rows);
		if ($nbRows > $maxRows) {
			$errors[] = array('line' => NULL, 'column' => NULL, 'type' => 'nb_rows', 'caption' => 'A maximum of '.$maxRows.' lines can be imported at a time');
			$this->http_status = 'Integrity errors';
		}
		else {
			for ($i = 0; $i < count($rows); $i++) {
				$row = $rows[$i];
					
				// Number of columns validation
				if (count($properties) != count($row)) $this->http_status = 'Integrity';
			}
			if (!$this->http_status) {
				$this->http_status = 'Loaded';
	
				$content = array();
				foreach ($rows as $row) {
					$contentRow = array();
					for ($i = 0; $i < count($row); $i++) {
						$contentRow[] = utf8_encode($row[$i]);
					}
					$content[] = $contentRow;
				}
				$this->xml_content = json_encode($content);
			}
		}
	}

	public function update($update_time)
	{
		$context = Context::getCurrent();
		$message = ImapMessage::get($this->id);
		
		// Isolation check
		if ($message->update_time > $update_time) return 'Isolation';

		ImapMessage::getTable()->save($this);
		
		return 'OK';
	}

	public function isDeletable()
	{
		$context = Context::getCurrent();
	
		// Check dependencies
		$config = $context->getConfig();
		foreach($config['ppitSupportDependencies'] as $dependency) {
			if ($dependency->isUsed($this)) return false;
		}
		
		return true;
	}
	
	public function delete($update_time)
	{
		$context = Context::getCurrent();
		$message = ImapMessage::get($this->id);
		
		// Isolation check
		if ($message->update_time > $update_time) return 'Isolation';
		
		ImapMessage::getTable()->delete($this->id);
		
		return 'OK';
	}
	
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        throw new \Exception("Not used");
    }

    public static function getTable()
    {
    	if (!ImapMessage::$table) {
    		$sm = Context::getCurrent()->getServiceManager();
    		ImapMessage::$table = $sm->get('PpitSupport\Model\ImapMessageTable');
    	}
    	return ImapMessage::$table;
    }
}
