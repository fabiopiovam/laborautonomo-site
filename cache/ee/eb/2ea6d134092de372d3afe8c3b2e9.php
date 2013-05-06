<?php

/* index.twig */
class __TwigTemplate_eeeb2ea6d134092de372d3afe8c3b2e9 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = $this->env->loadTemplate("base.twig");

        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "base.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_content($context, array $blocks = array())
    {
        // line 4
        echo "<center>
    <br /><br />
    <h1>Site em construção </h1>
    <h3>Por favor, aguardem...</h3>
    <br /><br />
    <h1>Sitio en construcción</h1>
    <h3>Por favor, espera...</h3>
    <br /><br />
    <h1>Site under construction</h1>
    <h3>Please wait...</h3>
</center>
";
    }

    public function getTemplateName()
    {
        return "index.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  31 => 4,  28 => 3,);
    }
}
