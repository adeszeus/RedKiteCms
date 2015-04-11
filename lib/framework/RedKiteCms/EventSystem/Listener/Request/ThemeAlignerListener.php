<?php
/**
 * This file is part of the RedKite CMS Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <info@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    GPL LICENSE Version 2.0
 *
 */

namespace RedKiteCms\EventSystem\Listener\Request;


use RedKiteCms\Configuration\ConfigurationHandler;
use RedKiteCms\Content\PageCollection\PagesCollectionParser;
use RedKiteCms\Content\Theme\ThemeSlotsGenerator;
use RedKiteCms\Content\Theme\ThemeAligner;
use RedKiteCms\Content\Theme\ThemeGenerator;
use RedKiteCms\FilesystemEntity\Page;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class ThemeAlignerListener listens to Kernel Request to align the RedKite CMS autogenerated files that defines the theme in use in the current site, according to changes made with the theme design
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\EventSystem\Listener\Request
 */
class ThemeAlignerListener
{
    /**
     * @type \RedKiteCms\Configuration\ConfigurationHandler
     */
    private $configurationHandler;
    /**
     * @type \RedKiteCms\Content\PageCollection\PagesCollectionParser
     */
    private $pagesCollectionParser;
    /**
     * @type \Symfony\Component\Security\Core\SecurityContext
     */
    private $securityContext;
    /**
     * @var \RedKiteCms\Content\Theme\ThemeGenerator
     */
    private $themeGenerator;
    /**
     * @var \RedKiteCms\Content\Theme\ThemeSlotsGenerator
     */
    private $slotsGenerator;
    /**
     * @var \RedKiteCms\Content\Theme\ThemeAligner
     */
    private $themeAligner;

    /**
     * Constructor
     *
     * @param \RedKiteCms\Configuration\ConfigurationHandler $configurationHandler
     * @param \RedKiteCms\Content\PageCollection\PagesCollectionParser $pagesCollectionParser
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext
     * @param \RedKiteCms\Content\Theme\ThemeGenerator $themeGenerator
     * @param \RedKiteCms\Content\Theme\ThemeSlotsGenerator $slotsGenerator
     * @param \RedKiteCms\Content\Theme\ThemeAligner $themeAligner
     */
    public function __construct(ConfigurationHandler $configurationHandler, PagesCollectionParser $pagesCollectionParser, SecurityContext $securityContext, ThemeGenerator $themeGenerator, ThemeSlotsGenerator $slotsGenerator, ThemeAligner $themeAligner, Page $page)
    {
        $this->configurationHandler = $configurationHandler;
        $this->pagesCollectionParser = $pagesCollectionParser;
        $this->securityContext = $securityContext;
        $this->themeGenerator = $themeGenerator;
        $this->slotsGenerator = $slotsGenerator;
        $this->themeAligner = $themeAligner;
        $this->page = $page;
    }

    /**
     * Aligns the site slots
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $token = $this->securityContext->getToken();
        if (null === $token) {
            return;
        }

        $username = null;
        if ( ! $this->configurationHandler->isTheme()) {
            $username = $token->getUser()->getUsername();
        }
        
        $this->pagesCollectionParser
            ->contributor($username)
            ->parse()
        ;



        $this->themeGenerator->generate();
        $this->slotsGenerator->synchronize($this->page, $this->pagesCollectionParser->pages());
        $this->themeAligner->align($this->pagesCollectionParser);
        $this->slotsGenerator->generate();
    }
} 