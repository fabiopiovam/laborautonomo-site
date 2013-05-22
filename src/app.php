<?php 
$app = require __DIR__.'/bootstrap.php';

//APP DEFINITION
$app->get('/', function () use ($app) {
    return $app['twig']->render('index.twig');
})
->bind('homepage');

$app->get($app['translator']->trans('sobre'), function () use ($app) {
    return $app['twig']->render('about.twig');
})
->bind('about');

$app->get('blog', function () use ($app) {
    return $app['twig']->render('about.twig');
})
->bind('blog');

$app->get($app['translator']->trans('projetos'), function () use ($app) {
    return $app['twig']->render('projects.twig');
})
->bind('projects');

$app->match($app['translator']->trans('contato'), function () use ($app) {

    $form = $app['form.factory']->createBuilder('form')
        ->add('name', 'text', array('label' => $app['translator']->trans('Nome')))
        ->add('email', 'email', array('label'=>'E-mail'))
        ->add('site', 'url', array('label'=>'site / blog','required'=>false))
        ->add('message', 'textarea', array('label' => $app['translator']->trans('Mensagem')))
        ->getForm();
        
    $request = $app['request'];
    if ('POST' == $request->getMethod()) {
        $form->bind($request);

        if ($form->isValid()) {
            $data = $form->getData();

            try{
                $message = \Swift_Message::newInstance()
                ->setSubject(sprintf('Contato por %s', $_SERVER['SERVER_NAME']))
                ->setFrom(array($data['email']))
                ->setTo(array('fabio@laborautonomo.org'))
                ->setBody($data['message']);
    
                $app['mailer']->send($message);
            }
            catch(Exception $e){
                $app['monolog']->addError(sprintf('%s Error on %s : %s', $e->getCode(), $app['request']->server->get('REQUEST_URI'), $e->getMessage()));
                $app['session']->getFlashBag()->add('error', $app['translator']->trans('Ops! Houve uma falha na entrega de sua mensagem. Por favor, tente novamente.'));
                return $app->redirect($app['url_generator']->generate('contact'));
            }            
            
            $app['session']->getFlashBag()->add('success', $app['translator']->trans('Sua mensagem foi enviada com sucesso! Obrigado :)'));
            return $app->redirect($app['url_generator']->generate('contact'));
        }
    }
    
    return $app['twig']->render('contact.twig', array('form' => $form->createView()));
})
->bind('contact');

return $app;