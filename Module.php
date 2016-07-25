<?php
namespace PpitSupport;

use PpitCore\Model\GenericTable;
use PpitSupport\Model\Incident;
use PpitSupport\Model\ImapMessage;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Authentication\Storage;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;
use Zend\EventManager\EventInterface;
use Zend\Validator\AbstractValidator;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'PpitSupport\Model\IncidentTable' =>  function($sm) {
                	$tableGateway = $sm->get('IncidentTableGateway');
                	$table = new GenericTable($tableGateway);
                	return $table;
                },
                'IncidentTableGateway' => function ($sm) {
                	$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                	$resultSetPrototype = new ResultSet();
                	$resultSetPrototype->setArrayObjectPrototype(new Incident());
                	return new TableGateway('support_incident', $dbAdapter, null, $resultSetPrototype);
                },
				'PpitSupport\Model\ImapMessageTable' =>  function($sm) {
                	$tableGateway = $sm->get('ImapMessageTableGateway');
                	$table = new GenericTable($tableGateway);
                	return $table;
                },
                'ImapMessageTableGateway' => function ($sm) {
                	$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                	$resultSetPrototype = new ResultSet();
                	$resultSetPrototype->setArrayObjectPrototype(new ImapMessage());
                	return new TableGateway('support_message', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
    
    public function onBootstrap(EventInterface $e)
    {
    	$serviceManager = $e->getApplication()->getServiceManager();
    
    	// Set the translator for default validation messages
    	$translator = $serviceManager->get('translator');
    	AbstractValidator::setDefaultTranslator($translator);
    }
}
