<?php
namespace PpitSupport\Model;

use PpitContact\Model\ContactMessage;
use PpitCore\Model\Community;
use PpitCore\Model\Context;
use PpitCore\Model\Vcard;
use PpitDocument\Model\Document;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\db\sql\Where;

class Incident implements InputFilterAwareInterface
{
    public $id;
	public $contract_id;
	public $type;
	public $status;
	public $incident_date;
	public $identifier;
	public $caption;
	public $description;
	public $property_1;
	public $property_2;
	public $property_3;
	public $property_4;
	public $property_5;
	public $property_6;
	public $property_7;
	public $property_8;
	public $property_9;
	public $property_10;
	public $property_11;
	public $property_12;
	public $property_13;
	public $property_14;
	public $property_15;
	public $property_16;
	public $property_17;
	public $property_18;
	public $property_19;
	public $property_20;
	public $audit = array();
	public $incident_message_id;
	public $notification_time;
	public $update_time;
    
	// Transient properties
	public $properties;
    public $current_audit;
	public $files;
    public $comment;

    // Static fields
    private static $table;
    
    public static function getProperties()
    {
    	// Retrieve the properties
    	return Context::getCurrent()->getConfig()['ppitSupportSettings']['incidentProperties'];
    }

    public static function getStatusDescriptor()
    {
    	// Retrieve the status
    	return Context::getCurrent()->getConfig()['ppitSupportSettings']['incidentStatus'];
    }
    
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->contract_id = (isset($data['contract_id'])) ? $data['contract_id'] : null;
        $this->type = (isset($data['type'])) ? $data['type'] : null;
        $this->status = (isset($data['status'])) ? $data['status'] : null;
        $this->incident_date = (isset($data['incident_date'])) ? $data['incident_date'] : null;
        $this->identifier = (isset($data['identifier'])) ? $data['identifier'] : null;
        $this->caption = (isset($data['caption'])) ? $data['caption'] : null;
        $this->description = (isset($data['description'])) ? $data['description'] : null;
        $this->property_1 = (isset($data['property_1'])) ? $data['property_1'] : null;
        $this->property_2 = (isset($data['property_2'])) ? $data['property_2'] : null;
        $this->property_3 = (isset($data['property_3'])) ? $data['property_3'] : null;
		$this->property_4 = (isset($data['property_4'])) ? $data['property_4'] : null;
		$this->property_5 = (isset($data['property_5'])) ? $data['property_5'] : null;
        $this->property_6 = (isset($data['property_6'])) ? $data['property_6'] : null;
        $this->property_7 = (isset($data['property_7'])) ? $data['property_7'] : null;
        $this->property_8 = (isset($data['property_8'])) ? $data['property_8'] : null;
		$this->property_9 = (isset($data['property_9'])) ? $data['property_9'] : null;
        $this->property_10 = (isset($data['property_10'])) ? $data['property_10'] : null;
        $this->property_11 = (isset($data['property_11'])) ? $data['property_11'] : null;
        $this->property_12 = (isset($data['property_12'])) ? $data['property_12'] : null;
        $this->property_13 = (isset($data['property_13'])) ? $data['property_13'] : null;
        $this->property_14 = (isset($data['property_14'])) ? $data['property_14'] : null;
        $this->property_15 = (isset($data['property_15'])) ? $data['property_15'] : null;
        $this->property_16 = (isset($data['property_16'])) ? $data['property_16'] : null;
        $this->property_17 = (isset($data['property_17'])) ? $data['property_17'] : null;
        $this->property_18 = (isset($data['property_18'])) ? $data['property_18'] : null;
        $this->property_19 = (isset($data['property_19'])) ? $data['property_19'] : null;
        $this->property_20 = (isset($data['property_20'])) ? $data['property_20'] : null;
        $this->audit = (isset($data['audit'])) ? json_decode($data['audit'], true) : array();
        $this->incident_message_id = (isset($data['incident_message_id'])) ? $data['incident_message_id'] : null;
        $this->notification_time = (isset($data['notification_time'])) ? $data['notification_time'] : null;
        $this->update_time = (isset($data['update_time'])) ? $data['update_time'] : null;
    }

    public function toArray() {
    	$data = array();
    	$data['id'] = (int) $this->id;
    	$data['contract_id'] = (int) $this->contract_id;
    	$data['type'] = $this->type;
    	$data['status'] = $this->status;
    	$data['incident_date'] = ($this->incident_date) ? $this->incident_date : null;
    	$data['identifier'] = $this->identifier;
    	$data['caption'] = $this->caption;
    	$data['description'] = $this->description;
    	$data['property_1'] = $this->property_1;
    	$data['property_2'] = $this->property_2;
    	$data['property_3'] = $this->property_3;
    	$data['property_4'] = $this->property_4;
    	$data['property_5'] = $this->property_5;
    	$data['property_6'] = $this->property_6;
    	$data['property_7'] = $this->property_7;
    	$data['property_8'] = $this->property_8;
    	$data['property_9'] = $this->property_9;
    	$data['property_10'] = $this->property_10;
    	$data['property_11'] = $this->property_11;
    	$data['property_12'] = $this->property_12;
    	$data['property_13'] = $this->property_13;
    	$data['property_14'] = $this->property_14;
    	$data['property_15'] = $this->property_15;
    	$data['property_16'] = $this->property_16;
    	$data['property_17'] = $this->property_17;
    	$data['property_18'] = $this->property_18;
    	$data['property_19'] = $this->property_19;
    	$data['property_20'] = $this->property_20;
    	$data['audit'] = json_encode($this->audit);
    	$data['incident_message_id'] = $this->incident_message_id;
    	$data['notification_time'] = ($this->notification_time) ? $this->notification_time : null;
    	 
    	return $data;
    }

    public static function getList($type, $params, $major, $dir, $mode)
    {
    	$context = Context::getCurrent();
    	$select = Order::getTable()->getSelect();
    	
    	$where = new Where();

    	// Filter on type
		if ($type) $where->equalTo('type', $type);

		// Todo list vs search modes
		if ($mode == 'todo') {

			$statusList = array('new', 'managed', 'pending', 'solved');
			if (count($statusList) > 0) $where->in('status', $statusList);
		}
		else {
			
			// Set the filters
			if (isset($params['identifier'])) $where->like('identifier', '%'.$params['identifier'].'%');
			if (isset($params['status'])) $where->like('status', '%'.$params['status'].'%');
			if (isset($params['min_date'])) $where->greaterThanOrEqualTo('incident_date', $params['min_date']);
			if (isset($params['max_date'])) $where->lessThanOrEqualTo('incident_date', $params['max_date']);
			if (isset($params['caption'])) $where->like('caption', '%'.$params['caption'].'%');
				
			for ($i = 1; $i < 20; $i++) {
				if (isset($params['property_'.$i])) $where->like('property_'.$i, '%'.$params['property_'.$i].'%');
				if (isset($params['min_property_'.$i])) $where->greaterThanOrEqualTo('property_'.$i, $params['min_property_'.$i]);
				if (isset($params['max_property_'.$i])) $where->lessThanOrEqualTo('property_'.$i, $params['max_property_'.$i]);
			}
		}

    	// Sort the list
    	$select->where($where)->order(array($major.' '.$dir, 'incident_date DESC'));

    	$cursor = Incident::getTable()->selectWith($select);
    	$incidents = array();
    	foreach ($cursor as $incident) $incidents[] = $incident;

    	return $incidents;
    }

    public static function get($id, $column = 'id')
    {
    	$context = Context::getCurrent();
    	$incident = Incident::getTable()->get($id, $column);
		return $incident;
    }

    public static function instanciate()
    {
    	$incident = new Incident;
    	return $incident;
    }

    public function loadData($data, $files, $action) 
    {
    	$context = Context::getCurrent();
		$settings = $context->getConfig();

    	// Retrieve the data from the request

		$this->type = trim(strip_tags($data['type']));
		if (!$this->type || strlen($this->caption) > 255) return 'Integrity';

		$this->incident_date = trim(strip_tags($data['incident_date']));
		if (!$this->incident_date || $this->incident_date && !checkdate(substr($this->incident_date, 5, 2), substr($this->incident_date, 8, 2), substr($this->incident_date, 0, 4))) return 'Integrity';

		$this->identifier = trim(strip_tags($data['identifier']));
		if (strlen($this->identifier) > 255) return 'Integrity';
		
	    $this->caption = trim(strip_tags($data['caption']));
	   	if (!$this->caption || strlen($this->caption) > 255) return 'Integrity';
	    
	   	$this->description = trim(strip_tags($data['description']));
	    if (!$this->description || strlen($this->description) > 2047) return 'Integrity';
	     
	    $this->property_1 = $data['property_1'];
	    if (strlen($this->property_1) > 255) return 'Integrity';

	    $this->property_2 = $data['property_2'];
	    if (strlen($this->property_2) > 255) return 'Integrity';

	    $this->property_3 = $data['property_3'];
	    if (strlen($this->property_3) > 255) return 'Integrity';

	    $this->property_4 = $data['property_4'];
	    if (strlen($this->property_4) > 255) return 'Integrity';

	    $this->property_5 = $data['property_5'];
	    if (strlen($this->property_5) > 255) return 'Integrity';

	    $this->property_6 = $data['property_6'];
	    if (strlen($this->property_6) > 255) return 'Integrity';

	    $this->property_7 = $data['property_7'];
	    if (strlen($this->property_7) > 255) return 'Integrity';

	    $this->property_8 = $data['property_8'];
	    if (strlen($this->property_8) > 255) return 'Integrity';

	    $this->property_9 = $data['property_9'];
	    if (strlen($this->property_9) > 255) return 'Integrity';

	    $this->property_10 = $data['property_10'];
	    if (strlen($this->property_10) > 255) return 'Integrity';

	    $this->property_11 = $data['property_11'];
	    if (strlen($this->property_11) > 255) return 'Integrity';

	    $this->property_12 = $data['property_12'];
	    if (strlen($this->property_12) > 255) return 'Integrity';

	    $this->property_13 = $data['property_13'];
	    if (strlen($this->property_13) > 255) return 'Integrity';

	    $this->property_14 = $data['property_14'];
	    if (strlen($this->property_14) > 255) return 'Integrity';

	    $this->property_15 = $data['property_15'];
	    if (strlen($this->property_15) > 255) return 'Integrity';

	    $this->property_16 = $data['property_16'];
	    if (strlen($this->property_16) > 255) return 'Integrity';

	    $this->property_17 = $data['property_17'];
	    if (strlen($this->property_17) > 255) return 'Integrity';

	    $this->property_18 = $data['property_18'];
	    if (strlen($this->property_18) > 255) return 'Integrity';

	    $this->property_19 = $data['property_19'];
	    if (strlen($this->property_19) > 255) return 'Integrity';

	    $this->property_20 = $data['property_20'];
	    if (strlen($this->property_20) > 255) return 'Integrity';
	     
    	$this->comment = trim(strip_tags($data['comment']));
    	if (strlen($this->comment) > 2047) return 'Integrity';

    	$this->files = $files;

    	// Change the status
    	if ($action == 'new') {
	    	$this->status = 'new';
    	}
    	elseif ($action == 'manage') {
	    	$this->status = 'managed';
    	}
    	elseif ($action == 'reject') {
	    	$this->status = 'rejected';
		}
        elseif ($action == 'suspend') {
	    	$this->status = 'pending';
		}
        elseif ($action == 'close') {
	    	$this->status = 'closed';
		}
		// Update the audit
    	$this->current_audit = array(
				'status' => $this->status,
				'time' => Date('Y-m-d G:i:s'),
				'n_fn' => $context->getFormatedName(),
				'comment' => $this->comment,
    	);
		$this->audit[] = $this->current_audit;
    	$this->notification_time = null;
		return 'OK';
    }

    public function loadDataFromRequest($request, $action) {

    	$context = Context::getCurrent();

    	// Retrieve the data from the request
    	$data = array();

    	$data['type'] = $request->getPost('type');
    	$data['incident_date'] = $request->getPost('incident_date');
    	$data['identifier'] = $request->getPost('identifier');
    	$data['caption'] = $request->getPost('caption');
    	$data['description'] = $request->getPost('description');
    	$data['comment'] = $request->getPost('comment');
    	
    	// Specific properties
    	foreach($context->getConfig()['ppitSupportSettings']['incidentProperties'] as $propertyId => $property) {
			$data[$propertyId] = $request->getPost($propertyId);
    	}

    	// Retrieve the order form
    	$files = $request->getFiles()->toArray();
    	 
    	$return = $this->loadData($data, $files, $action);
    	if ($return != 'OK') throw new \Exception('View error');
    }

    public function saveAttachment()
    {
		$context = Context::getCurrent();

    	if ($this->files) {
    		if ($context->getCommunityId()) {
    			$community = Community::get($context->getCommunityId());
    			$root_id = $community->root_document_id;
    		}
    		else $root_id = Document::getTable()->get(0, 'parent_id')->id;
    		$document = Document::instanciate($root_id);
    		$document->files = $this->files;
    		$document->saveFile();
    		$this->current_audit['attachment'] = $document->save();
    	}
    }
    
    public function add()
    {
		$context = Context::getCurrent();
		$config = $context->getConfig();

    	// Check consistency
    	if ($this->identifier) {
    		$incident = Incident::getTable()->get($this->identifier, 'identifier');
    		if ($incident) return 'Consistency'; // Already exists
    	}

    	if (!$this->incident_date) $this->incident_date = date('Y-m-d');

    	// Save the attachment
    	$this->saveAttachment();

    	$this->id = Incident::getTable()->save($this);
		if (!$this->identifier) $this->identifier = 'INC-'.sprintf('%1$06d', $incident->id);

		// Consistency check
		$select = Incident::getTable()->getSelect()->columns(array('id'))->where(array('identifier' => $this->identifier));
		$cursor = Incident::getTable()->selectWith($select);
		if (count($cursor) > 0) return 'Duplicate';
		
    	Incident::getTable()->save($this);

    	return 'OK';
    }

    public function update($update_time, $action = null)
    {
    	$context = Context::getCurrent();
    	$config = $context->getConfig();
    	$incident = Incident::get($this->id);

    	// Isolation check
    	if ($incident->update_time > $update_time) return 'Isolation';

    	// Consistency check
	    $select = Incident::getTable()->getSelect()->columns(array('id'))->where(array('identifier' => $this->identifier, 'id <> ?' => $this->id));
	    $cursor = Incident::getTable()->selectWith($select);
	    if (count($cursor) > 0) return 'Duplicate';

	    // Save the attachment
	    $this->saveAttachment();

    	Incident::getTable()->save($this);

    	return 'OK';
    }

    public static function notify()
    {
    	$context = Context::getCurrent();
    	$config = $context->getConfig();
    	$select = Incident::getTable()->getSelect()->where(array('notification_time' => null));
		$cursor = Incident::getTable()->selectWith($select);

		$statuses = $config['ppitSupportSettings']['incidentStatus'];
		$incidentsByStatus = array();
		foreach ($statuses as $status => $properties) {
			$incidentsByStatus[$status] = array();
		}
		foreach($cursor as $incident) {
			$incidentsByStatus[$incident->status][] = $incident->caption;
			$incident->notification_time = date('Y-m-d H:i:s');
			Incident::getTable()->save($incident);
		}
		
		foreach ($incidentsByStatus as $status => $incidents) 
    	if (count($incidents) > 0) {

    		// Notifications for action
    		$target = array();
    		if (array_key_exists('responsible', $statuses[$status])) {
	    		foreach ($statuses[$status]['responsible'] as $role) {
	    			$select = Vcard::getTable()->getSelect();
		    		$where = new Where;
		    		$where->like('roles', $role);
		    		$select->where($where);
		    		$cursor = Vcard::getTable()->selectWith($select);
		    		$url = $config['ppitCoreSettings']['domainName'];
		    		$title = $statuses[$status]['actionTitle']['fr_FR'];
		    		$text = sprintf($statuses[$status]['actionText']['fr_FR'], $url, implode(';', $incidents));
		    		foreach ($cursor as $contact) {
		    			if (!array_key_exists($contact->id, $target)) $target[$contact->id] = $contact->email;
		    		}
	    		}
	    		foreach ($target as $email) ContactMessage::sendMail($email, $text, $title, null);
	    	}
    	
    		// Notifications for information
    		$target = array();
    		if (array_key_exists('informed', $statuses[$status])) {
	    		foreach ($statuses[$status]['informed'] as $role) {
	    			$select = Vcard::getTable()->getSelect();
		    		$where = new Where;
		    		$where->like('roles', $role);
		    		$select->where($where);
		    		$cursor = Vcard::getTable()->selectWith($select);
		    		$url = $config['ppitCoreSettings']['domainName'];
		    		$title = $statuses[$status]['informationTitle']['fr_FR'];
		    		$text = sprintf($statuses[$status]['informationText']['fr_FR'], $url, implode(';', $incidents));
		    		foreach ($cursor as $contact) {
		    			if (!array_key_exists($contact->id, $target)) $target[$contact->id] = $contact->email;
		    		}
	    		}
	    		foreach ($target as $email) ContactMessage::sendMail($email, $text, $title, null);
	    	}
    	}
    }

    public function isUsed($object)
    {
    	return false;
    }
    
    public function isDeletable() {
    
    	// Check the order status
    	return false;
    }
    
    public function delete($update_time)
    {
    	$context = Context::getCurrent();
    	$incident = Incident::get($this->id);
    
    	// Isolation check
    	if ($incident->update_time > $update_time) return 'Isolation';
    	
    	Incident::getTable()->delete($this->id);
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
    	if (!Incident::$table) {
    		$sm = Context::getCurrent()->getServiceManager();
    		Incident::$table = $sm->get('PpitSupport\Model\IncidentTable');
    	}
    	return Incident::$table;
    }
}
