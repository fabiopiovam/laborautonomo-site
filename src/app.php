<?php 
$app = require __DIR__.'/bootstrap.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

$app->get($app['translator']->trans('projetos'), function () use ($app) {
    $subRequest = Request::create('/api/projects', 'GET');
    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
})
->bind('projects');

$app->match('/', function () use ($app) {

    $form = $app['form.factory']->createBuilder('form')
        ->add('name', 'text', array(
            'label' => false, 
            'attr'  => array(
                'placeholder' => $app['translator']->trans('nome')
                )
            )
         )
        ->add('email', 'email', array(
            'label' => false, 
            'attr'  => array(
                'placeholder' => $app['translator']->trans('e-mail')
                )
            )
         )
        ->add('site', 'url', array(
            'label'     => false,
            'required'  => false,
            'attr'      => array(
                'placeholder' => 'website / blog (ex.: http://yoursite.com)'
                )
            )
         )
        ->add('message', 'textarea', array(
            'label' => false,
            'attr'  => array(
                'placeholder'   => $app['translator']->trans('mensagem'),
                'rows'          => '5',
                )
            )
         )
        ->getForm();
        
    $request = $app['request'];
    if ('POST' == $request->getMethod()) {
        $form->bind($request);

        if ($form->isValid()) {
            $data = array_merge($form->getData(), $_SERVER);
            try{
                $message = \Swift_Message::newInstance()
                ->setSubject(sprintf('Contato por %s', $data['SERVER_NAME']))
                ->setFrom(array($data['email']))
                ->setTo($app['mail.to'])
                ->setBody($app['twig']->render('contact.message.twig', $data), 'text/html');
    
                $app['mailer']->send($message);
            }
            catch(Exception $e){
                $app['monolog']->addError(sprintf('%s Error on %s : %s', $e->getCode(), $data['REQUEST_URI'], $e->getMessage()));
                $app['session']->getFlashBag()->add('error', $app['translator']->trans('Ops! Falha no envio da mensagem. Por favor, tente novamente.'));
                return $app->redirect($app['url_generator']->generate('homepage'));
            }            
            
            $app['session']->getFlashBag()->add('success', $app['translator']->trans('Sua mensagem foi enviada com sucesso! Obrigado :)'));
            return $app->redirect($app['url_generator']->generate('homepage'));
        }
    }
    
    return $app['twig']->render('index.twig', array('form' => $form->createView()));
})
->bind('homepage');

$app->mount('/api', include 'api.php');

return $app;