<?php
/**
 * An AlphaLemonCms Block
 */

namespace AlphaLemon\Block\ImageBundle\Core\Block;

use AlphaLemon\AlphaLemonCmsBundle\Core\Content\Block\JsonBlock\AlBlockManagerJsonBlockContainer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AlphaLemon\AlphaLemonCmsBundle\Core\Content\Validator\AlParametersValidatorInterface;

/**
 * Description of AlBlockManagerImage
 */
class AlBlockManagerImage extends AlBlockManagerJsonBlockContainer
{
    protected $translator;
    protected $cmsLanguage;
    protected $blockTemplate = 'ImageBundle:Image:image.html.twig';  
    protected $editorTemplate = 'ImageBundle:Editor:_editor.html.twig';
    protected $domain = 'messages';
     
    public function __construct(ContainerInterface $container, AlParametersValidatorInterface $validator = null)
    {
        parent::__construct($container, $validator);
        
        $this->translator = $this->container->get('translator');
        $this->cmsLanguage = $this->container->get('alpha_lemon_cms.configuration')->read('language');
    }
    
    public function getDefaultValue()
    {
        $value = sprintf(
            '
                {
                    "0" : {
                        "src": "",
                        "data_src": "holder.js/260x180",
                        "title" : "%s",
                        "alt" : "%s"
                    }
                }
            ',  $this->translator->trans("Sample title", array(), $this->domain, $this->cmsLanguage), 
                $this->translator->trans("Sample alt", array(), $this->domain, $this->cmsLanguage));
        
        return array('Content' => $value);
    }
    
    protected function renderHtml()
    {
        $items = $this->decodeJsonContent($this->alBlock->getContent());
        
        return array('RenderView' => array(
            'view' => $this->blockTemplate,
            'options' => array(
                'image' => $items[0],
            ),
        ));
    }
    
    public function editorParameters()
    {
        $items = $this->decodeJsonContent($this->alBlock->getContent());
        $item = $items[0];
        
        $formClass = $this->container->get('image.form');
        $form = $this->container->get('form.factory')->create($formClass, $item);
        
        return array(
            "template" => $this->editorTemplate,
            "title" => $this->translator->trans("Image editor", array(), $this->domain, $this->cmsLanguage),
            "form" => $form->createView(),
        );
    }
}
