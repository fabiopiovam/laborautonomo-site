<?php

/* base.twig */
class __TwigTemplate_c82bea1a5aa3109a5e1362e8a76b3f17 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!doctype html>
<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"en\">
    <head>
        <meta charset=\"utf-8\">
        <title>";
        // line 5
        $this->displayBlock('title', $context, $blocks);
        echo " - LaborAutonomo</title>
    </head>
    <body>
        <nav>
            <ul>
                <li><a href=\"/\">";
        // line 10
        echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans("Início"), "html", null, true);
        echo "</a></li>
                <li><a href=\"/contact\">";
        // line 11
        echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans("Contato"), "html", null, true);
        echo "</a></li>
            </ul>
        </nav>

        ";
        // line 15
        $this->displayBlock('content', $context, $blocks);
        // line 16
        echo "    </body>
</html>";
    }

    // line 5
    public function block_title($context, array $blocks = array())
    {
    }

    // line 15
    public function block_content($context, array $blocks = array())
    {
    }

    public function getTemplateName()
    {
        return "base.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  58 => 15,  53 => 5,  48 => 16,  46 => 15,  39 => 11,  35 => 10,  27 => 5,  21 => 1,);
    }
}
