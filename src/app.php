<?php 
$app = require __DIR__.'/bootstrap.php';

$app->get('/', function () use ($app) {
    //return $app['twig']->render('index.twig');
    return $app['twig']->render('about.twig');
})
->bind('homepage');

$app->get($app['translator']->trans('sobre'), function () use ($app) {
    return $app['twig']->render('about.twig');
})
->bind('about');

$app->get('blog', function () use ($app) {
    return $app['twig']->render('blog.twig');
})
->bind('blog');

$app->get($app['translator']->trans('projetos'), function () use ($app) {
    return $app['twig']->render('projects.twig');
})
->bind('projects');

$app->match($app['translator']->trans('contato'), function () use ($app) {

    $form = $app['form.factory']->createBuilder('form')
        ->add('name', 'text', array(
            'label' => false, 
            'attr'  => array(
                'placeholder' => $app['translator']->trans('Nome')
                )
            )
         )
        ->add('email', 'email', array(
            'label' => false, 
            'attr'  => array(
                'placeholder' => $app['translator']->trans('E-mail')
                )
            )
         )
        ->add('site', 'url', array(
            'label'     => false,
            'required'  => false,
            'attr'      => array(
                'placeholder' => 'Website / Blog (Ex.: http://yoursite.com)'
                )
            )
         )
        ->add('message', 'textarea', array(
            'label' => false,
            'attr'  => array(
                'placeholder'   => $app['translator']->trans('Mensagem'),
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