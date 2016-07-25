<?php
namespace PpitSupport\Controller;

use PpitCore\Form\CsrfForm;
use PpitCore\Model\Context;
use PpitCore\Model\Csrf;
use PpitSupport\Model\ImapMessage;
use Zend\Http\Client;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;

class ImapMessageController extends AbstractActionController
{
	public function indexAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();
    	 
    	// Prepare the SQL request
    	$mailbox = $this->params()->fromQuery('mailbox', 'INBOX');
    	$major = $this->params()->fromQuery('major', 'update_time');
    	$dir = $this->params()->fromQuery('dir', 'DESC');
    	$imapMessages = ImapMessage::scanMailbox($mailbox);

    	// Return the link list
    	$view = new ViewModel(array(
    		'context' => $context,
			'config' => $context->getconfig(),
    		'title' => 'Messages',
    		'mailbox' => $mailbox,
    		'major' => $major,
    		'dir' => $dir,
    		'imapMessages' => $imapMessages,
        ));
		$view->setTerminal(true);
		return $view;
    }

    public function importAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();
    	
    	$imapMessage = ImapMessage::instanciate();
    	
    	// Instanciate the csrf form
    	$csrfForm = new CsrfForm();
    	$csrfForm->addCsrfElement('csrf');
    	$message = null;
    	$error = null;
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    	
    		$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
    		$csrfForm->setData($request->getPost());
    	
    		if ($csrfForm->isValid()) { // CSRF check
    	
    			$imapMessage->loadDataFromRequest($request);
    	
    			// Atomically save
    			 
    			$connection = ImapMessage::getTable()->getAdapter()->getDriver()->getConnection();
    			$connection->beginTransaction();
    			try {

    				// Import the file
    				$imapMessage->add();

    				$connection->commit();
    				$message = 'OK';
    			}
    			catch (Exception $e) {
    				$connection->rollback();
    				throw $e;
    			}
    		}
    	}
    	$view = new ViewModel(array(
    			'context' => $context,
    			'config' => $context->getconfig(),
    			'csrfForm' => $csrfForm,
    			'message' => $message,
    			'error' => $error,
    	));
    	$view->setTerminal(true);
    	return $view;
    }

    public function deleteAction()
    {
    	// Check the presence of the id parameter for the entity to delete
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if (!$id) return $this->redirect()->toRoute('index');

       	// Retrieve the context
    	$context = Context::getCurrent();
    	 
    	// Retrieve the XML message
    	$imapMessage = ImapMessage::getTable()->get($id);

    	// Instanciate the csrf form
    	$csrfForm = new CsrfForm();
    	$csrfForm->addCsrfElement('csrf');
    	$message = null;
    	$error = null;
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    	
    		$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
    		$csrfForm->setData($request->getPost());
    	
    		if ($csrfForm->isValid()) { // CSRF check

				// Atomically delete the message
		        try {
			    	$connection = ImapMessage::getTable()->getAdapter()->getDriver()->getConnection();
		    		$connection->beginTransaction();
					$imapMessage->delete();
			        $connection->commit();
			        $message = 'OK';
				}
		    	catch (Exception $e) {
		    		$connection->rollback();
		    		throw $e;
		    	}
    		}
    	}
    
    	$view = new ViewModel(array(
    		'context' => $context,
			'config' => $context->getconfig(),
    		'imapMessage' => $imapMessage,
    		'id' => $id,
    		'csrfForm' => $csrfForm,
    		'message' => $message,
    		'error' => $error,
    	));
		$view->setTerminal(true);
		return $view;
    }

    public function downloadAction()
    {
    	// Check the presence of the id parameter for the entity to download
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if (!$id) return $this->redirect()->toRoute('index');

       	// Retrieve the context
    	$context = Context::getCurrent();
    	 
    	// Retrieve the XML message
    	$imapMessage = ImapMessage::getTable()->get($id);
    
    	$view = new ViewModel(array(
    		'context' => $context,
			'config' => $context->getconfig(),
    		'imapMessage' => $imapMessage,
    		'id' => $id,
    	));
		$view->setTerminal(true);
		return $view;
    }
}
