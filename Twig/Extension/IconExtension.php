<?php

namespace nickolaus\MultipleIconFontsBundle\Twig\Extension;

class IconExtension extends \Twig_Extension
{
    /**
     * @var \Twig_Template
     */
    protected $iconTemplate;


    public function getFunctions()
    {
        $options = array(
            'is_safe' => array('html'),
            'needs_environment' => true,
        );

        return array(
            new \Twig_SimpleFunction('icon', array($this, 'getIcon'), $options),
            new \Twig_SimpleFunction('icon_fontawesome', array($this, 'getFontawesome'), $options),
            new \Twig_SimpleFunction('icon_glyphicon', array($this, 'getGlyphicon'), $options),
            new \Twig_SimpleFunction('icon_ionicons', array($this, 'getIonIcon'), $options),
            new \Twig_SimpleFunction('icon_material_design', array($this, 'getMaterialDesignIcon'), $options)
        );
    }

    /**
     * @param \Twig_Environment $env
     * @param string $identifier
     * @param string $fontFamily
     * @param array $options
     * @return string
     */
    public function getIcon(\Twig_Environment $env, $identifier, $fontFamily, array $options = null) {
        $template = $this->getIconTemplate($env);
        $context = array(
            'iconIdentifier' => $identifier,
            'fontFamily' => $fontFamily,
            'options' => $options
        );
        return $template->renderBlock('icon', $context);
    }

    public function getFontawesome(\Twig_Environment $env, $identifier, array $options = null) {
        return $this->getIcon($env, $identifier, 'fa', $options);
    }

    public function getGlyphicon(\Twig_Environment $env, $identifier, array $options = null) {
        return $this->getIcon($env, $identifier, 'glyphicon', $options);
    }

    public function getIonIcon(\Twig_Environment $env, $identifier, array $options = null) {
        return $this->getIcon($env, $identifier, 'ion', $options);
    }

    public function getMaterialDesignIcon(\Twig_Environment $env, $identifier, array $options = null) {
        return $this->getIcon($env, $identifier, 'mdi', $options);
    }


    /**
     * @return \Twig_Template
     */
    protected function getIconTemplate(\Twig_Environment $env)
    {
        if (null === $this->iconTemplate) {
            $this->iconTemplate = $env->loadTemplate('MultipleIconFontsBundle::icons.html.twig');
        }
        return $this->iconTemplate;
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'icon_extension';
    }
}
