<?php

return array(
    'controllers' => array(
        'invokables' => array(
        	'PpitSupport\Controller\Incident' => 'PpitSupport\Controller\IncidentController',
        	'PpitSupport\Controller\IncidentResponse' => 'PpitSupport\Controller\IncidentResponseController',
        	'PpitSupport\Controller\ImapMessage' => 'PpitSupport\Controller\ImapMessageController',
        ),
    ),

	'console' => array(
		'router' => array(
			'routes' => array(
				'notify' => array(
					'options' => array(
						'route'    => 'order notify',
						'defaults' => array(
							'controller' => 'PpitSupport\Controller\Incident',
							'action' => 'notify'
                        )
                    )
                )
			)
		)
	),
	
	'router' => array(
		'routes' => array(
			'index' => array(
				'type' => 'literal',
				'options' => array(
					'route'    => '/',
					'defaults' => array(
						'controller' => 'PpitSupport\Controller\Incident',
						'action' => 'home',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'index' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '/index',
							'defaults' => array(
								'action' => 'index',
							),
						),
					),
				),
			),
			'incident' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/incident',
					'defaults' => array(
						'controller' => 'PpitSupport\Controller\Incident',
						'action'     => 'index',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'index' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '/index[/:type]',
							'defaults' => array(
								'action' => 'index',
							),
						),
					),
					'list' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '/list[/:type]',
							'defaults' => array(
								'action' => 'list',
							),
						),
					),
					'export' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '/export[/:type]',
							'defaults' => array(
								'action' => 'export',
							),
						),
					),
					'detail' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '/detail[/:id]',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'action' => 'detail',
							),
						),
					),
					'update' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '/update[/:id][/:act]',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'action' => 'update',
							),
						),
					),
					'delete' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '/delete[/:id]',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'action' => 'delete',
							),
						),
					),
				),
			),
        	'imapMessage' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/imap-message',
                    'defaults' => array(
                        'controller' => 'PpitSupport\Controller\ImapMessage',
                        'action'     => 'index',
                    ),
                ),
            	'may_terminate' => true,
            		'child_routes' => array(
            				'index' => array(
            						'type' => 'segment',
            						'options' => array(
            								'route' => '/index',
            								'defaults' => array(
            										'action' => 'index',
            								),
            						),
            				),
            				'download' => array(
            						'type' => 'segment',
            						'options' => array(
            								'route' => '/download[/:id]',
            								'constraints' => array(
            										'id'     => '[0-9]*',
            								),
            								'defaults' => array(
            										'action' => 'download',
            								),
            						),
            				),
            				'select' => array(
            						'type' => 'segment',
            						'options' => array(
            								'route' => '/select[/:id]',
            								'constraints' => array(
            										'id'     => '[0-9]*',
            								),
            								'defaults' => array(
            										'action' => 'select',
            								),
            						),
            				),
            				'delete' => array(
            						'type' => 'segment',
            						'options' => array(
            								'route' => '/delete[/:id]',
            								'constraints' => array(
            										'id'     => '[0-9]*',
            								),
            								'defaults' => array(
            										'action' => 'delete',
            								),
            						),
            				),
            		),
        	),
		),
	),

    'bjyauthorize' => array(
    		
        // Guard listeners to be attached to the application event manager
        'guards' => array(
            'BjyAuthorize\Guard\Route' => array(

            	// Incident
            	array('route' => 'incident', 'roles' => array('admin')),
            	array('route' => 'incident/index', 'roles' => array('user')),
            	array('route' => 'incident/list', 'roles' => array('user')),
            	array('route' => 'incident/export', 'roles' => array('user')),
            	array('route' => 'incident/detail', 'roles' => array('user')),
            	array('route' => 'incident/update', 'roles' => array('user')),
            	array('route' => 'incident/delete', 'roles' => array('admin')),
            	array('route' => 'incident/notify', 'roles' => array('guest')),

            	array('route' => 'imapMessage/delete', 'roles' => array('admin')),
            	array('route' => 'imapMessage/download', 'roles' => array('admin')),
            	array('route' => 'imapMessage/index', 'roles' => array('admin')),
            	array('route' => 'imapMessage/import', 'roles' => array('admin')),
            )
        )
    ),

    'view_manager' => array(
    	'strategies' => array(
    			'ViewJsonStrategy',
    	),
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',       // On défini notre doctype
        'not_found_template'       => 'error/404',   // On indique la page 404
        'exception_template'       => 'error/index', // On indique la page en cas d'exception
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
        ),
        'template_path_stack' => array(
            'ppit-support' => __DIR__ . '/../view',
        ),
    ),
	'translator' => array(
		'locale' => 'fr_FR',
		'translation_file_patterns' => array(
			array(
				'type'     => 'phparray',
				'base_dir' => __DIR__ . '/../language',
				'pattern'  => '%s.php',
				'text_domain' => 'ppit-support'
			),
	       	array(
	            'type' => 'phpArray',
	            'base_dir' => './vendor/zendframework/zendframework/resources/languages/',
	            'pattern'  => 'fr/Zend_Validate.php',
	        ),
 		),
	),

	'ppitSupportDependencies' => array(
	),
);
