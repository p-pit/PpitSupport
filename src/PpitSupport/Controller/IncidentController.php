<?php
namespace PpitSupport\Controller;

use DateInterval;
use Date;
use Zend\View\Model\ViewModel;
use PpitOrder\Model\Message;
use PpitSupport\Model\Incident;
use PpitCore\Form\CsrfForm;
use PpitCore\Model\Context;
use PpitCore\Model\Csrf;
use DOMPDFModule\View\Model\PdfModel;
use Zend\Session\Container;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;

class IncidentController extends AbstractActionController
{
	public function getFilters($params)
	{
		// Retrieve the query parameters
		$filters = array();
		
		$type = ($params()->fromQuery('type', null));
		if ($type) $filters['type'] = $type;

		$status = ($params()->fromQuery('status', null));
		if ($status) $filters['status'] = $status;
		
		$min_incident_date = ($params()->fromQuery('min_incident_date', null));
		if ($min_incident_date) $filters['min_incident_date'] = $min_incident_date;
		
		$max_incident_date = ($params()->fromQuery('max_incident_date', null));
		if ($max_incident_date) $filters['max_incident_date'] = $max_incident_date;

		$identifier = ($params()->fromQuery('identifier', null));
		if ($identifier) $filters['identifier'] = $identifier;
		
		$caption = ($params()->fromQuery('caption', null));
		if ($caption) $filters['caption'] = $caption;
		
		$property_1 = ($params()->fromQuery('property_1', null));
		if ($property_1) $filters['property_1'] = $property_1;
		$min_property_1 = ($params()->fromQuery('min_property_1', null));
		if ($min_property_1) $filters['min_property_1'] = $min_property_1;
		$max_property_1 = ($params()->fromQuery('max_property_1', null));
		if ($max_property_1) $filters['max_property_1'] = $max_property_1;
		
		$property_2 = ($params()->fromQuery('property_2', null));
		if ($property_2) $filters['property_2'] = $property_2;
		$min_property_2 = ($params()->fromQuery('min_property_2', null));
		if ($min_property_2) $filters['min_property_2'] = $min_property_2;
		$max_property_2 = ($params()->fromQuery('max_property_2', null));
		if ($max_property_2) $filters['max_property_2'] = $max_property_2;
		
		$property_3 = ($params()->fromQuery('property_3', null));
		if ($property_3) $filters['property_3'] = $property_3;
		$min_property_3 = ($params()->fromQuery('min_property_3', null));
		if ($min_property_3) $filters['min_property_3'] = $min_property_3;
		$max_property_3 = ($params()->fromQuery('max_property_3', null));
		if ($max_property_3) $filters['max_property_3'] = $max_property_3;
		
		$property_4 = ($params()->fromQuery('property_4', null));
		if ($property_4) $filters['property_4'] = $property_4;
		$min_property_4 = ($params()->fromQuery('min_property_4', null));
		if ($min_property_4) $filters['min_property_4'] = $min_property_4;
		$max_property_4 = ($params()->fromQuery('max_property_4', null));
		if ($max_property_4) $filters['max_property_4'] = $max_property_4;
		
		$property_5 = ($params()->fromQuery('property_5', null));
		if ($property_5) $filters['property_5'] = $property_5;
		$min_property_5 = ($params()->fromQuery('min_property_5', null));
		if ($min_property_5) $filters['min_property_5'] = $min_property_5;
		$max_property_5 = ($params()->fromQuery('max_property_5', null));
		if ($max_property_5) $filters['max_property_5'] = $max_property_5;
		
		$property_6 = ($params()->fromQuery('property_6', null));
		if ($property_6) $filters['property_6'] = $property_6;
		$min_property_6 = ($params()->fromQuery('min_property_6', null));
		if ($min_property_6) $filters['min_property_6'] = $min_property_6;
		$max_property_6 = ($params()->fromQuery('max_property_6', null));
		if ($max_property_6) $filters['max_property_6'] = $max_property_6;
		
		$property_7 = ($params()->fromQuery('property_7', null));
		if ($property_7) $filters['property_7'] = $property_7;
		$min_property_7 = ($params()->fromQuery('min_property_7', null));
		if ($min_property_7) $filters['min_property_7'] = $min_property_7;
		$max_property_7 = ($params()->fromQuery('max_property_7', null));
		if ($max_property_7) $filters['max_property_7'] = $max_property_7;
		
		$property_8 = ($params()->fromQuery('property_8', null));
		if ($property_8) $filters['property_8'] = $property_8;
		$min_property_8 = ($params()->fromQuery('min_property_8', null));
		if ($min_property_8) $filters['min_property_8'] = $min_property_8;
		$max_property_8 = ($params()->fromQuery('max_property_8', null));
		if ($max_property_8) $filters['max_property_8'] = $max_property_8;
		
		$property_9 = ($params()->fromQuery('property_9', null));
		if ($property_9) $filters['property_9'] = $property_9;
		$min_property_9 = ($params()->fromQuery('min_property_9', null));
		if ($min_property_9) $filters['min_property_9'] = $min_property_9;
		$max_property_9 = ($params()->fromQuery('max_property_9', null));
		if ($max_property_9) $filters['max_property_9'] = $max_property_9;
		
		$property_10 = ($params()->fromQuery('property_10', null));
		if ($property_10) $filters['property_10'] = $property_10;
		$min_property_10 = ($params()->fromQuery('min_property_10', null));
		if ($min_property_10) $filters['min_property_10'] = $min_property_10;
		$max_property_10 = ($params()->fromQuery('max_property_10', null));
		if ($max_property_10) $filters['max_property_10'] = $max_property_10;

		$property_11 = ($params()->fromQuery('property_11', null));
		if ($property_11) $filters['property_11'] = $property_11;
		$min_property_11 = ($params()->fromQuery('min_property_11', null));
		if ($min_property_11) $filters['min_property_11'] = $min_property_11;
		$max_property_11 = ($params()->fromQuery('max_property_11', null));
		if ($max_property_11) $filters['max_property_11'] = $max_property_11;

		$property_12 = ($params()->fromQuery('property_12', null));
		if ($property_12) $filters['property_12'] = $property_12;
		$min_property_12 = ($params()->fromQuery('min_property_12', null));
		if ($min_property_12) $filters['min_property_12'] = $min_property_12;
		$max_property_12 = ($params()->fromQuery('max_property_12', null));
		if ($max_property_12) $filters['max_property_12'] = $max_property_12;

		$property_13 = ($params()->fromQuery('property_13', null));
		if ($property_13) $filters['property_13'] = $property_13;
		$min_property_13 = ($params()->fromQuery('min_property_13', null));
		if ($min_property_13) $filters['min_property_13'] = $min_property_13;
		$max_property_13 = ($params()->fromQuery('max_property_13', null));
		if ($max_property_13) $filters['max_property_13'] = $max_property_13;

		$property_14 = ($params()->fromQuery('property_14', null));
		if ($property_14) $filters['property_14'] = $property_14;
		$min_property_14 = ($params()->fromQuery('min_property_14', null));
		if ($min_property_14) $filters['min_property_14'] = $min_property_14;
		$max_property_14 = ($params()->fromQuery('max_property_14', null));
		if ($max_property_14) $filters['max_property_14'] = $max_property_14;
		
		$property_15 = ($params()->fromQuery('property_15', null));
		if ($property_15) $filters['property_15'] = $property_15;
		$min_property_15 = ($params()->fromQuery('min_property_15', null));
		if ($min_property_15) $filters['min_property_15'] = $min_property_15;
		$max_property_15 = ($params()->fromQuery('max_property_15', null));
		if ($max_property_15) $filters['max_property_15'] = $max_property_15;

		$property_16 = ($params()->fromQuery('property_16', null));
		if ($property_16) $filters['property_16'] = $property_16;
		$min_property_16 = ($params()->fromQuery('min_property_16', null));
		if ($min_property_16) $filters['min_property_16'] = $min_property_16;
		$max_property_16 = ($params()->fromQuery('max_property_16', null));
		if ($max_property_16) $filters['max_property_16'] = $max_property_16;

		$property_17 = ($params()->fromQuery('property_17', null));
		if ($property_17) $filters['property_17'] = $property_17;
		$min_property_17 = ($params()->fromQuery('min_property_17', null));
		if ($min_property_17) $filters['min_property_17'] = $min_property_17;
		$max_property_17 = ($params()->fromQuery('max_property_17', null));
		if ($max_property_17) $filters['max_property_17'] = $max_property_17;

		$property_18 = ($params()->fromQuery('property_18', null));
		if ($property_18) $filters['property_18'] = $property_18;
		$min_property_18 = ($params()->fromQuery('min_property_18', null));
		if ($min_property_18) $filters['min_property_18'] = $min_property_18;
		$max_property_18 = ($params()->fromQuery('max_property_18', null));
		if ($max_property_18) $filters['max_property_18'] = $max_property_18;

		$property_19 = ($params()->fromQuery('property_19', null));
		if ($property_19) $filters['property_19'] = $property_19;
		$min_property_19 = ($params()->fromQuery('min_property_19', null));
		if ($min_property_19) $filters['min_property_19'] = $min_property_19;
		$max_property_19 = ($params()->fromQuery('max_property_19', null));
		if ($max_property_19) $filters['max_property_19'] = $max_property_19;
	
		$property_20 = ($params()->fromQuery('property_20', null));
		if ($property_20) $filters['property_20'] = $property_20;
		$min_property_20 = ($params()->fromQuery('min_property_20', null));
		if ($min_property_20) $filters['min_property_20'] = $min_property_20;
		$max_property_20 = ($params()->fromQuery('max_property_20', null));
		if ($max_property_20) $filters['max_property_20'] = $max_property_20;
		
		return $filters;
	}
	
   	public function indexAction()
   	{
		// Retrieve the context
		$context = Context::getCurrent();

		// Retrieve the order type
		$type = $this->params()->fromRoute('type', 0);
		
   		// Return the link list
   		$view = new ViewModel(array(
   				'context' => $context,
				'config' => $context->getconfig(),
   				'statusDescriptor' => Incident::getStatusDescriptor(),
   				'type' => $type,
   		));
       	return $view;
   	}

   	public function getList()
   	{
		// Retrieve the context
		$context = Context::getCurrent();

		$params = $this->getFilters($this->params());

		// Retrieve the order type
		$type = $this->params()->fromRoute('type', null);
		
		$major = ($this->params()->fromQuery('major', 'identifier'));
		$dir = ($this->params()->fromQuery('dir', 'ASC'));

		if (count($params) == 0) $mode = 'todo'; else $mode = 'search';

		// Retrieve the list
		$incidents = Incident::getList($type, $params, $major, $dir, $mode);

   		// Return the link list
   		$view = new ViewModel(array(
   				'context' => $context,
				'config' => $context->getconfig(),
   				'type' => $type,
   				'incidentProperties' => Incident::getProperties(),
   				'statusDescriptor' => Incident::getStatusDescriptor(),
   				'incidents' => $incidents,
   				'mode' => $mode,
   				'params' => $params,
   				'major' => $major,
   				'dir' => $dir,
   		));
		$view->setTerminal(true);
       	return $view;
   	}
   	
   	public function listAction()
   	{
   		return $this->getList();
   	}

   	public function exportAction()
   	{
   		return $this->getList();
   	}
    
    public function detailAction()
    {
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if (!$id) {
    		return $this->redirect()->toRoute('index');
    	}
    	// Retrieve the context
    	$context = Context::getCurrent();

    	// Retrieve the incident
    	$incident = Incident::get($id);

    	$view = new ViewModel(array(
    		'context' => $context,
			'config' => $context->getconfig(),
   			'statusDescriptor' => Incident::getStatusDescriptor(),
    		'id' => $id,
    		'incident' => $incident,
    	));
		$view->setTerminal(true);
		return $view;
    }

    public function updateAction()
    {
		// Retrieve the context
		$context = Context::getCurrent();

    	$id = (int) $this->params()->fromRoute('id', 0);
    	$action = $this->params()->fromRoute('act', null);
    	if ($id) $incident = Incident::get($id);
    	else $incident = new Incident;
    	
    	// Instanciate the csrf form
    	$csrfForm = new CsrfForm();
    	$csrfForm->addCsrfElement('csrf');
    	$error = null;
    	$message = null;
    	$request = $this->getRequest();
    	if ($request->isPost()) {

    		$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
    		$csrfForm->setData($request->getPost());
    		 
    		if ($csrfForm->isValid()) { // CSRF check

    			// Load the input data
    			$incident->loadDataFromRequest($request, $action);

    			// Atomically save
    			$connection = Incident::getTable()->getAdapter()->getDriver()->getConnection();
    			$connection->beginTransaction();
    			try {
    				if (!$incident->id) $return = $incident->add();
    				else $return = $incident->update($request->getPost('update_time'), $action);

    				if ($return != 'OK') {
    					$connection->rollback();
    					$error = $return;
    				}
    				else {
    					$connection->commit();
	    				$message = 'OK';
    				}
    			}
    			catch (\Exception $e) {
    				$connection->rollback();
    				throw $e;
    			}
    		}
    	}

    	$view = new ViewModel(array(
				'context' => $context,
				'config' => $context->getconfig(),
    			'id' => $id,
    			'action' => $action,
   				'incidentProperties' => Incident::getProperties(),
    			'incident' => $incident,
    			'csrfForm' => $csrfForm,
    			'error' => $error,
    			'message' => $message
    	));
		$view->setTerminal(true);
       	return $view;
    }

    public function notifyAction()
    {
    	Incident::notify();
    }

    public function deleteAction()
    {
		// Check the presence of the id parameter for the entity to delete
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if (!$id) {
    		return $this->redirect()->toRoute('index');
    	}
    	// Retrieve the current user
    	$context = Context::getCurrent();

    	// Retrieve the incident
    	$incident = Incident::getTable()->get($id);

    	$csrfForm = new CsrfForm();
    	$csrfForm->addCsrfElement('csrf');
    	$message = null;
       	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
    		$csrfForm->setData($request->getPost());
    
    		if ($csrfForm->isValid()) { // CSRF check

    			// Atomically delete the user and the role
    			$connection = Incident::getTable()->getAdapter()->getDriver()->getConnection();
    			$connection->beginTransaction();
    			try {
	    				 
	    			// Delete order structure and workflow
	    			$incident->delete($id);
    				
    				$connection->commit();
    				
    				$message = 'OK';
    			}    
    		    catch (Exception $e) {
    				$connection->rollback();
    				throw $e;
    			}
    		}
    	}

    	return array(
    		'context' => $context,
			'config' => $context->getconfig(),
    		'title' => 'Order',
    		'id' => $id,
    		'incident' => $incident,
    		'csrfForm' => $csrfForm,
    		'message' => $message,
    		'error' => $error,
    	);
    }
}
