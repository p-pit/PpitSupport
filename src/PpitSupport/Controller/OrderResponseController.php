<?php
namespace PpitOrder\Controller;

use PpitCore\Form\CsrfForm;
use PpitCore\Model\Context;
use PpitCore\Model\Csrf;
use PpitOrder\Model\Message;
use PpitOrder\Model\Order;
use PpitOrder\Model\OrderProduct;
use PpitOrder\Model\XmlOrder;
use PpitOrder\Model\XmlOrderResponse;
use PpitOrder\Model\XmlShipmentResponse;
use Zend\Session\Container;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

define ('DONE_DEAL_NUMBER', 31);
define ('SALES_ORDER_NUMBER', 36);
define ('SERIAL_NUMBER', 48);
define ('SHIPMENT_DATE', 54);
define ('DELIVERY_DATE', 56);
define ('COMMISSIONING_DATE', 57);

class OrderResponseController extends AbstractActionController
{	
	public function confirmAction()
	{
		// Retrieve the context
		$context = Context::getCurrent();
	
		$id = (int) $this->params()->fromRoute('id', 0);
    	$act = $this->params()->fromRoute('act', null);
		if (!$id) return $this->redirect()->toUrl('index');
		$order = Order::get($id);

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
				$order->loadDataFromRequest($request, $act);
	
				// Atomically save
				$connection = Order::getTable()->getAdapter()->getDriver()->getConnection();
				$connection->beginTransaction();
				try {
					// Submit the response message
					$safe = $context->getConfig()['ppitUserSettings']['safe'];
					
					$client = new Client(
							$context->getConfig()['ppitOrderSettings']['responseMessageUrl'],
							array(
									'adapter' => 'Zend\Http\Client\Adapter\Curl',
									'maxredirects' => 0,
									'timeout'      => 30,
							)
					);

					$client->setAuth('XEROX', $safe['ugap']['XEROX'], Client::AUTH_BASIC);
					$client->setEncType('text/xml');
					$client->setMethod('POST');

					$xmlOrderResponse = new XmlOrderResponse;
		    		$xmlOrderResponse->setResponseType(($act == 'confirm') ? 'Accepted' : 'Rejected');
//		    		$xmlOrderResponse->setItemDetailResponse(($act == 'confirm') ? 'ItemAccepted' : 'ItemRejected');
					if ($act == 'reject') {
						$xmlOrderResponse->setHeaderGeneralNote($order->comment);
						$xmlOrderResponse->setHeaderNoteId($order->property_7);
//						$xmlOrderResponse->setDetailGeneralNote($order->comment);
					}
					else {
						$xmlOrderResponse->setHeaderGeneralNote('');
						$xmlOrderResponse->setHeaderNoteId('');
//						$xmlOrderResponse->setDetailGeneralNote('');
					}
					// Save the message
				   	$message = Message::instanciate('orderResponse/confirm', $xmlOrderResponse->asXML());
				   	$message->add();

					// Update the order
					$order->confirmation_message_id = $message->id;
				   	$return = $order->update($request->getPost('update_time'), $act, $xmlOrderResponse);

					if ($return != 'OK') {
						$connection->rollback();
						$error = $return;
					}
					else {

			    		$xmlOrderResponse->setBuyerOrderResponseNumber($message->id);
						$content = $xmlOrderResponse->asXML();
						$message->identifier = $order->identifier;
						$message->xml_content = $content;

						// Post the confirmation message
						$client->setRawBody($content);
						$response = $client->send();
						$message->http_status = $response->renderStatusLine();

						// Write to the log
				   		if ($context->getConfig()['ppitCoreSettings']['isTraceActive']) {
				   			$writer = new \Zend\Log\Writer\Stream('data/log/orderResponse.txt');
				   			$logger = new \Zend\Log\Logger();
				   			$logger->addWriter($writer);
				   			$logger->info('confirm;'.$order->identifier.';'.$response->renderStatusLine());
				   		}

				   		// Save the message
				   		$message->update($message->update_time);
				   		 
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
				'act' => $act,
				'orderProperties' => Order::getProperties(),
				'order' => $order,
				'csrfForm' => $csrfForm,
				'error' => $error,
				'message' => $message
		));
		$view->setTerminal(true);
		return $view;
	}

	public function ship($orderProduct, $action, $request)
	{
		// Retrieve the context
		$context = Context::getCurrent();

		// Submit the response message
		$safe = $context->getConfig()['ppitUserSettings']['safe'];
	
		$client = new Client(
				$context->getConfig()['ppitOrderSettings']['responseMessageUrl'],
				array(
						'adapter' => 'Zend\Http\Client\Adapter\Curl',
						'maxredirects' => 0,
						'timeout'      => 30,
				)
		);
	
		$client->setAuth('XEROX', $safe['ugap']['XEROX'], Client::AUTH_BASIC);
		$client->setEncType('text/xml');
		$client->setMethod('POST');

		if ($action == 'ship') $xmlShipmentResponse = new XmlShipmentResponse('ASNLIV');
		elseif ($action == 'deliver') $xmlShipmentResponse = new XmlShipmentResponse('ASNLIV1');
		elseif ($action == 'commission') $xmlShipmentResponse = new XmlShipmentResponse('ASNLIV2');

		$xmlShipmentResponse->setASNIssueDate(date('Y-m-d').'T'.date('G:i:s'));
    	$xmlShipmentResponse->setBuyerOrderNumber($orderProduct->order->identifier);
    	$xmlShipmentResponse->setType(($action == 'ship') ? 'Planned' : 'Actual');

    	if ($action == 'ship') $shipDate = $orderProduct->shipment_date;
    	elseif ($action == 'deliver') $shipDate = $orderProduct->delivery_date;
    	elseif ($action == 'commission') $shipDate = $orderProduct->commissioning_date;
		$xmlShipmentResponse->setShipDate($shipDate.'T00:00:00');

		$xmlShipmentResponse->setSellerParty($orderProduct->order->property_14);

    	$xmlMessage = Message::get($orderProduct->order->order_message_id);
		$xmlOrder = new XmlOrder(new \SimpleXMLElement($xmlMessage->xml_content));
		if ($orderProduct->changed_equipment_identifier) $equipment_identifier = $orderProduct->changed_equipment_identifier;
		else $equipment_identifier = $orderProduct->equipment_identifier;
		for ($i = 0; $i < $xmlOrder->getNumberOfLines(); $i++) {
			$xmlShipmentResponse->addItemDetail($xmlOrder->getBuyerLineItemNum($i), $equipment_identifier, $shipDate.'T00:00:00');
		}

		//if ($action == 'deliver' || $action == 'commission') $xmlShipmentResponse->setProductIdentifier($orderProduct->equipment_identifier);
    	
    	$return = $orderProduct->update($orderProduct->update_time);
    	if ($return != 'OK') return $return;
		else {
	
			// Save the message
			$message = Message::instanciate('orderResponse/ship', $xmlShipmentResponse->asXML());
			if ($action == 'ship') $message->type = 'ASNLIV';
			elseif ($action == 'deliver') $message->type = 'ASNLIV1';
			elseif ($action == 'commission') $message->type = 'ASNLIV2';
			$message->identifier = $orderProduct->order_identifier;
			$message->add();
			
			// Add the message id to the order line
			if ($action == 'ship') $orderProduct->shipment_message_id = $message->id;
			elseif ($action == 'deliver') $orderProduct->delivery_message_id = $message->id;
			elseif ($action == 'commission') $orderProduct->commissioning_message_id = $message->id;

			$xmlShipmentResponse->setASNNumber($message->id);
			$content = $xmlShipmentResponse->asXML();
			$message->identifier = $orderProduct->line_identifier;
			$message->xml_content = $content;
	
			// Post the confirmation message
			$client->setRawBody($content);
			$response = $client->send();
			$message->http_status = $response->renderStatusLine();
	
			// Write to the log
			if ($context->getConfig()['ppitCoreSettings']['isTraceActive']) {
				$writer = new \Zend\Log\Writer\Stream('data/log/orderResponse.txt');
				$logger = new \Zend\Log\Logger();
				$logger->addWriter($writer);
				$logger->info('ship;'.$orderProduct->line_identifier.';'.$response->renderStatusLine());
			}
	
			// Save the message
			$message->update($message->update_time);
			
			return 'OK';
		}
	}

	public function shipAction()
	{
		// Retrieve the context
		$context = Context::getCurrent();
	
		// Retrieve the order line and the corresponding order
		$id = (int) $this->params()->fromRoute('id', 0);
		$action = $this->params()->fromRoute('act', null);

		if (!$id) return $this->redirect()->toUrl('index');
		$order = Order::get($id);
		$orderProduct = $order->uniqueOrderProduct;

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
				$orderProduct->loadDataFromRequest($request, $action);

				// Atomically save
				$connection = OrderProduct::getTable()->getAdapter()->getDriver()->getConnection();
				$connection->beginTransaction();
				try {

					$return = $this->ship($orderProduct, $action, $request);
					if ($return != 'OK') {
						$connection->rollback();
						$error = $return;						
					}
		    		else {
				    	// Update the order and the unique order line
				    	if ($action == 'ship') $order->status = 'shipped';
				    	elseif ($action == 'deliver') $order->status = 'delivered';
				    	elseif ($action == 'commission') $order->status = 'commissioned';
	
				    	$return = $order->update($request->getPost('update_time'));
				    	if ($return != 'OK') {
							$connection->rollback();
							$error = $return;						
						}
			    		else {
					    	$return = $order->uniqueOrderProduct->update($order->uniqueOrderProduct->update_time);
					    	if ($return != 'OK') {
					    		$connection->rollback();
					    		$error = $return;
					    	}
							else {
						    	$connection->commit();
						    	$message = 'OK';
							}
			    		}
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
				'orderProduct' => $orderProduct,
				'order' => $order,
				'csrfForm' => $csrfForm,
				'error' => $error,
				'message' => $message
		));
		$view->setTerminal(true);
		return $view;
	}

	public function processAction()
	{
		// Retrieve the context
		$context = Context::getCurrent();
		
		$id = (int) $this->params()->fromRoute('message_id', 0);
		if (!$id) return $this->redirect()->toUrl('index');
		$xmlMessage = Message::get($id);
		
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

				$resultMessage = array();
				foreach (json_decode($xmlMessage->xml_content, true) as $row) {

					// Retrieve the order
					$sales_order_number = $row[SALES_ORDER_NUMBER];
					$order = Order::get($sales_order_number, 'property_9');
					if ($order) {

						// Check integrity
						$error = false;
						
						$shipment_date = $row[SHIPMENT_DATE];
						if ($shipment_date && !checkdate(substr($shipment_date, 3, 2), substr($shipment_date, 0, 2), substr($shipment_date, 6, 4))) $error = true;
						else $shipment_date = substr($shipment_date, 6, 4).'-'.substr($shipment_date, 3, 2).'-'.substr($shipment_date, 0, 2);

						$delivery_date = $row[DELIVERY_DATE];
						if ($delivery_date && !checkdate(substr($delivery_date, 3, 2), substr($delivery_date, 0, 2), substr($delivery_date, 6, 4))) $error = true;
						else $delivery_date = substr($delivery_date, 6, 4).'-'.substr($delivery_date, 3, 2).'-'.substr($delivery_date, 0, 2);

						$serial_number = $row[SERIAL_NUMBER];

						$commissioning_date = $row[COMMISSIONING_DATE];
						if ($commissioning_date && !checkdate(substr($commissioning_date, 3, 2), substr($commissioning_date, 0, 2), substr($commissioning_date, 6, 4))) $error = true;
						else $commissioning_date = substr($commissioning_date, 6, 4).'-'.substr($commissioning_date, 3, 2).'-'.substr($commissioning_date, 0, 2);

						if ($error) {
							$row[] = 'KO';
							break;
						}

						// Shipment case
						if ($order->status == 'registered' && $shipment_date) {
							$row[] = 'ASNLIV';

							// Atomically save
							$connection = OrderProduct::getTable()->getAdapter()->getDriver()->getConnection();
							$connection->beginTransaction();
							try {
	
								$select = OrderProduct::getTable()->getSelect()->where(array('order_id' => $order->id));
								$cursor = OrderProduct::getTable()->selectWith($select);
								foreach ($cursor as $orderProduct) {
									$orderProduct->order = $order;
									$orderProduct->shipment_date = $shipment_date;
									if ($serial_number) {
										if (!$orderProduct->equipment_identifier) $orderProduct->equipment_identifier = $serial_number;
										elseif ($serial_number != $orderProduct->equipment_identifier && !$orderProduct->changed_equipment_identifier) $orderProduct->changed_equipment_identifier = $serial_number;
									}
									$this->ship($orderProduct, 'ship', $request);
								}
								// Update the order
						    	$order->status = 'shipped';
						    	$order->audit[] = array(
										'status' => $order->status,
										'time' => Date('Y-m-d G:i:s'),
										'n_fn' => $context->getFormatedName(),
										'comment' => '',
						    	);
						    	$return = $order->update($order->update_time);
								if ($return != 'OK') {
									$connection->rollback();
									$error = $return;						
								}
					    		else {
							    	$return = $orderProduct->update();
							    	if ($return != 'OK') {
							    		$connection->rollback();
							    		$error = $return;
							    	}
									else {
								    	$connection->commit();
								    	$message = 'OK';
									}
					    		}
							}
							catch (\Exception $e) {
								$connection->rollback();
								throw $e;
							}
						}

						// Delivery case
						else if ($order->status == 'shipped' && $delivery_date && $serial_number) {
							$row[] = 'ASNLIV1';

							// Atomically save
							$connection = OrderProduct::getTable()->getAdapter()->getDriver()->getConnection();
							$connection->beginTransaction();
							try {

								$select = OrderProduct::getTable()->getSelect()->where(array('order_id' => $order->id));
								$cursor = OrderProduct::getTable()->selectWith($select);
								foreach ($cursor as $orderProduct) {
									$orderProduct->order = $order;
									$orderProduct->delivery_date = $delivery_date;
									if ($serial_number) {
										if (!$orderProduct->equipment_identifier) $orderProduct->equipment_identifier = $serial_number;
										elseif ($serial_number != $orderProduct->equipment_identifier && !$orderProduct->changed_equipment_identifier) $orderProduct->changed_equipment_identifier = $serial_number;
									}
									$this->ship($orderProduct, 'deliver', $request);
								}
						    	// Update the order
						    	$order->status = 'delivered';
						    	$order->audit[] = array(
										'status' => $order->status,
										'time' => Date('Y-m-d G:i:s'),
										'n_fn' => $context->getFormatedName(),
										'comment' => '',
						    	);
						    	$return = $order->update($order->update_time);
								if ($return != 'OK') {
									$connection->rollback();
									$error = $return;						
								}
					    		else {
							    	$return = $orderProduct->update();
							    	if ($return != 'OK') {
							    		$connection->rollback();
							    		$error = $return;
							    	}
									else {
								    	$connection->commit();
								    	$message = 'OK';
									}
					    		}
							}
							catch (\Exception $e) {
								$connection->rollback();
								throw $e;
							}
						}
					
						// Commissioning case
						else if ($order->status == 'delivered' && $commissioning_date && $serial_number) {
							$row[] = 'ASNLIV2';

							// Atomically save
							$connection = OrderProduct::getTable()->getAdapter()->getDriver()->getConnection();
							$connection->beginTransaction();
							try {

								$select = OrderProduct::getTable()->getSelect()->where(array('order_id' => $order->id));
								$cursor = OrderProduct::getTable()->selectWith($select);
								foreach ($cursor as $orderProduct) {
									$orderProduct->order = $order;
									$orderProduct->commissioning_date = $commissioning_date;
									if ($serial_number) {
										if (!$orderProduct->equipment_identifier) $orderProduct->equipment_identifier = $serial_number;
										elseif ($serial_number != $orderProduct->equipment_identifier && !$orderProduct->changed_equipment_identifier) $orderProduct->changed_equipment_identifier = $serial_number;
									}
									$this->ship($orderProduct, 'commission', $request);
								}
						    	// Update the order
						    	$order->status = 'commissioned';
						    	$order->audit[] = array(
										'status' => $order->status,
										'time' => Date('Y-m-d G:i:s'),
										'n_fn' => $context->getFormatedName(),
										'comment' => '',
						    	);
						    	$return = $order->update($order->update_time);
								if ($return != 'OK') {
									$connection->rollback();
									$error = $return;						
								}
					    		else {
							    	$return = $orderProduct->update();
							    	if ($return != 'OK') {
							    		$connection->rollback();
							    		$error = $return;
							    	}
									else {
								    	$connection->commit();
								    	$message = 'OK';
									}
					    		}
							}
							catch (\Exception $e) {
								$connection->rollback();
								throw $e;
							}
						}
					}
					$resultMessage[] = $row;
				}
			}
			$xmlMessage->xml_content = json_encode($resultMessage);
			$xmlMessage->http_status = 'Processed';
			$xmlMessage->update($xmlMessage->update_time);
			$message = 'OK';
		}
		
		$view = new ViewModel(array(
				'context' => $context,
				'config' => $context->getconfig(),
				'id' => $id,
				'csrfForm' => $csrfForm,
				'error' => $error,
				'message' => $message
		));
		$view->setTerminal(true);
		return $view;
	}

	public function testMessageAction()
   	{
		// Retrieve the context
		$context = Context::getCurrent();
   		$safe = $context->getConfig()['ppitUserSettings']['safe'];
		
		$client = new Client(
		$context->getConfig()['ppitOrderSettings']['responseMessageUrl'],
			array(
				'adapter' => 'Zend\Http\Client\Adapter\Curl',
				'maxredirects' => 0,
				'timeout'      => 30
			));
/*		$adapter = new Zend\Http\Client\Adapter\Socket();
		$adapter->setStreamContext(array(
			'ssl' => array(
				'verify_peer' => false,
				'allow_self_signed' => false,
				'cafile' => '/etc/ssl/certs/ugap_public_cert.cer',
				'verify_depth' => 5,
				'CN_match' => 'editest.ugap.fr'
			)
		));
		$client->setAdapter($adapter);*/

		$client->setAuth('XEROX', $safe['ugap']['XEROX'], Client::AUTH_BASIC);
		$client->setEncType('text/xml');
		$client->setMethod('POST');

		$message = Message::instanciate();
		$content = new XmlOrderResponse();
		$content->setBuyerOrderResponseNumber(111);
		$content->setOrderResponseIssueDate('2015-06-16T09:45:00');
		$content->setOrderReference('999');
		$content->setSellerParty('0000099201');
		$content->setBuyerParty('MIXT');
		$content->setResponseType('Accepted');
		$content->setShipDate('2015-06-28T12:00:00');
		$client->setRawBody($content->asXML());
		
		$response = $client->send();
   	
   		// Return the link list
   		$view = new ViewModel(array(
   				'context' => $context,
   				'response'=> $response,
//				'config' => $context->getconfig(),
   		));
		$view->setTerminal(true);
       	return $view;
   	}

   	public function testReceiveAction()
   	{
   		// Retrieve the context
   		$context = Context::getCurrent();
   		$safe = $context->getConfig()['ppitUserSettings']['safe'];
   		$username = null;
   		$password = null;

   		// Check basic authentication
   		if (isset($_SERVER['PHP_AUTH_USER'])) {
   			$username = $_SERVER['PHP_AUTH_USER'];
   			$password = $_SERVER['PHP_AUTH_PW'];
   		} elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
   			if (strpos(strtolower($_SERVER['HTTP_AUTHORIZATION']),'basic')===0)
   				list($username,$password) = explode(':',base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
   		}
   		if ($username != 'XEROX' || $password != $safe['ugap']['XEROX']) {
   			$this->getResponse()->setStatusCode(401);
   			return $this->getResponse();
   		}

   		$request = $this->getRequest();
   	
   		$content = new \SimpleXMLElement($request->getContent());
   	
   		$this->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'text/xml; charset=utf-8');
   		$this->getResponse()->setContent($content->asXML());
   		return $this->getResponse();
   	}
}
