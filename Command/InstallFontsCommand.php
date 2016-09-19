<?php

namespace nickolaus\MultipleIconFontsBundle\Command;

use nickolaus\MultipleIconFontsBundle\Component\IgnoredClasses;
use nickolaus\MultipleIconFontsBundle\Component\FontLocation;
use nickolaus\MultipleIconFontsBundle\Component\IconPrefix;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\Parser as CssParser;
use Sabberworm\CSS\Property\AtRule;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\RuleSet\AtRuleSet;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\RuleSet\RuleSet;
use Sabberworm\CSS\Value\CSSString;
use Sabberworm\CSS\Value\RuleValueList;
use Sabberworm\CSS\Value\URL;
use Sabberworm\CSS\Value\Value;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\KernelInterface;

class InstallFontsCommand extends ContainerAwareCommand
{

    /** @var  array */
    private $iconPrefix;

    /** @var  array */
    private $iconSets;

    /** @var  ContainerInterface */
    private $container;

    /** @var  Filesystem */
    private $filesystem;

    /** @var  KernelInterface */
    private $kernel;

    /** @var  string */
    private $rootDir;

    /** @var string $destinationPublicRoot */
    private $destinationPublicRoot;

    /** @var  array */
    private $ignoredCssClasses;

    /** @var  integer */
    private $numberOfIgnoredClasses;

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName('nickolaus:install:fonts')
            ->setDescription("Install fonts")
            ->addOption('develop', 'dev', InputOption::VALUE_OPTIONAL, 'Set this to true for src developing', false);
    }

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container = $this->getContainer();
        $this->kernel = $this->container->get('kernel');
        $this->rootDir = sprintf('%s%s%s%s', $this->kernel->getRootDir(), DIRECTORY_SEPARATOR, '..', DIRECTORY_SEPARATOR);

        if ($input->getOption('develop')) {
            $this->destinationPublicRoot = $destination = $this->rootDir .
                'src' . DIRECTORY_SEPARATOR . 'nickolaus' . DIRECTORY_SEPARATOR .
                'MultipleIconFontsBundle' . DIRECTORY_SEPARATOR . 'Resources'  . DIRECTORY_SEPARATOR .
                'public' . DIRECTORY_SEPARATOR;
        }
        else {
            $this->destinationPublicRoot = $destination = $this->rootDir .
                'vendor' . DIRECTORY_SEPARATOR . 'nickolaus' . DIRECTORY_SEPARATOR .
                'multiple-icon-fonts-bundle' . DIRECTORY_SEPARATOR . 'Resources'  . DIRECTORY_SEPARATOR .
                'public' . DIRECTORY_SEPARATOR;
        }


        $this->filesystem = new Filesystem();

        $this->iconSets = FontLocation::all();
        $this->iconPrefix = IconPrefix::all();

        $this->ignoredCssClasses = IgnoredClasses::all();
        $this->numberOfIgnoredClasses = count($this->ignoredCssClasses);

        if (count($this->iconSets) !== count($this->iconPrefix)) {
            throw new \Exception('Icon-Font location or prefix is not defined');
        }
$this->downloadFromUrl('');
        $this->copyFonts($output);
        $this->generateCssFile($output);
    }


    /**
     * Copy font-fils to bundle public-resources
     *
     * @param OutputInterface $output
     */
    private function copyFonts(OutputInterface $output) {

        foreach ($this->iconSets as $name => $iconSet) {

            $name = strtolower($name);

            $finder = new Finder();

            $fontFiles = $finder
                ->files()
                ->in($this->rootDir . 'vendor' . DIRECTORY_SEPARATOR . $iconSet . DIRECTORY_SEPARATOR . 'fonts');

            $destination = $this->destinationPublicRoot . 'fonts' . DIRECTORY_SEPARATOR . $name. DIRECTORY_SEPARATOR;

            if (!$this->filesystem->exists($destination)) {
                $this->filesystem->mkdir($destination);
            }

            /** @var SplFileInfo $fontFile */
            foreach ($fontFiles as $fontFile) {
                $origin = $fontFile->getPath() . DIRECTORY_SEPARATOR . $fontFile->getFilename();

                $output->writeln('<info>Copying font-file from: "'. $origin . '" to "' . $destination . '"</info>');
                $this->filesystem->copy(
                    $fontFile->getPath() . DIRECTORY_SEPARATOR . $fontFile->getFilename(),
                    $destination . $fontFile->getFilename()
                );
            }


        }
    }


    /**
     * Generate a single css-file containing all icons and icon-properties
     *
     * @param OutputInterface $output
     * @throws \Exception
     */
    private function generateCssFile(OutputInterface $output) {
        $mergedDocument = new Document();

        foreach ($this->iconSets as $name => $iconSet) {
            $source = $this->rootDir . 'vendor' . DIRECTORY_SEPARATOR . $iconSet . DIRECTORY_SEPARATOR;
            if (FontLocation::GLYPHICON === $iconSet) {
                $source .= 'dist' . DIRECTORY_SEPARATOR;
            }
            $source .= 'css';
            $finder = new Finder();
            $cssFiles = $finder->files()->in($source)->name('*.css')->notName('*.min.*')->notName('*theme*');


            $numberOfFiles = count($cssFiles);
            if ($numberOfFiles !== 1) {
                throw new \Exception('Could not distinct css file');
            }
            else {
                /** @var SplFileInfo $cssFile */
                foreach ($cssFiles as $cssFile) {
                    $this->mergeFontCSSBlocks($mergedDocument, $cssFile, $name);
                }
            }
        }

        $finder = new Finder();
        $iconPropertiesCSSFiles = $finder
            ->files()
            ->in($this->destinationPublicRoot . 'css' . DIRECTORY_SEPARATOR)->name('icon-properties.css');

        /** @var SplFileInfo $iconPropertiesFile */
        foreach ($iconPropertiesCSSFiles as $iconPropertiesCSSFile) {
            $this->mergePropertiesCSSBlocks($mergedDocument, $iconPropertiesCSSFile);
        }

        $destination = $this->destinationPublicRoot . 'css' . DIRECTORY_SEPARATOR;

        $mergedCSSFileAsString = $mergedDocument->render(\Sabberworm\CSS\OutputFormat::createPretty());
        $this->filesystem->dumpFile($destination . 'icon-fonts.css', $mergedCSSFileAsString);

        $minifiedMergedFileAsString = $mergedDocument->render(\Sabberworm\CSS\OutputFormat::createCompact());
        $this->filesystem->dumpFile($destination . 'icon-fonts.min.css', $minifiedMergedFileAsString);

    }


    /**
     * merge icon-properties.css files
     *
     * @param Document $mergedDocument
     * @param SplFileInfo $cssFile
     */
    private function mergePropertiesCSSBlocks(Document &$mergedDocument, SplFileInfo $cssFile) {
        $cssParser = new CssParser($cssFile->getContents());
        $document = $cssParser->parse();

        $this->mergeCSSBlocks($document, $mergedDocument, 'properties');
    }


    /**
     * merge font~.css-files
     *
     * @param Document $mergedDocument
     * @param SplFileInfo $cssFile
     */
    private function mergeFontCSSBlocks(Document &$mergedDocument, SplFileInfo $cssFile, $iconSetName) {
        $cssParser = new CssParser($cssFile->getContents());
        $document = $cssParser->parse();

        /** @var RuleSet $ruleSet */
        foreach ($document->getContents() as $key => $ruleSet) {


            /**@ @var AtRuleSet $ruleSet */
            if ($ruleSet instanceof AtRuleSet && 0 === strcmp('font-face', $ruleSet->atRuleName())) {

                /** @var Rule $rule */
                foreach ($ruleSet->getRules() as $rule) {

                    /** @var Value $value */
                    $value = $rule->getValue();
                    /** @var URL $value */
                    if ($value instanceof URL) {
                            $this->modifySourceUrl($value, $iconSetName);
                    }

                    /** @var RuleValueList $value */
                    elseif ($value instanceof RuleValueList) {
                        $components = $value->getListComponents();
                        foreach ($components as $component) {
                            if ($component instanceof URL) {
                                $this->modifySourceUrl($component, $iconSetName);
                            }
                            elseif ($component instanceof RuleValueList) {
                                $subComponents = $component->getListComponents();
                                foreach ($subComponents as $subComponent) {
                                    if ($subComponent instanceof URL) {
                                        $this->modifySourceUrl($subComponent, $iconSetName);
                                    }
                                }
                            }
                        }
                    }


                }
                $mergedDocument->append($ruleSet);
            }
        }

        $this->mergeCSSBlocks($document, $mergedDocument, 'fonts');

    }


    /**
     * @param Value $value
     * @param $iconSetName
     */
    private function modifySourceUrl(Value $value, $iconSetName) {
        if ($value instanceof URL) {
            $url = $value->getURL();
            /** @var CSSString $url */
            if ($url instanceof CSSString) {

                /** @var $urlAsString */
                $urlAsString = $url->getString();
                $urlAsString = substr_replace($urlAsString, strtolower($iconSetName) . '/', strpos($urlAsString, '/fonts/') + 7, 0);
                $url->setString($urlAsString);
            }
        }
    }

    /**
     * merge the different css-blocks into a single file
     *
     * @param Document $document
     * @param Document $mergedDocument
     * @param $filter
     */
    private function mergeCSSBlocks(Document $document, Document &$mergedDocument, $filter) {
        /** @var DeclarationBlock $declarationBlock */
        foreach ($document->getAllDeclarationBlocks() as $declarationBlock) {

            $selectorIsIcon = false;

            /** @var Selector $selector */
            foreach ($declarationBlock->getSelectors() as $selector) {

                $selectorAsString = $selector->getSelector();

                if ('properties' === $filter) {
                    $selectorIsIcon = $this->filterProperties($mergedDocument, $declarationBlock);
                }
                if ('fonts' === $filter) {
                    $selectorIsIcon = $this->filterFonts($mergedDocument, $declarationBlock, $selectorAsString);
                }

                if ($selectorIsIcon) {
                    break;
                }

            }
        }
    }


    /*
     * filter function prepared for icon properties
     */
    private function filterProperties(Document &$mergedDocument, DeclarationBlock $declarationBlock) {
        $mergedDocument->append($declarationBlock);
        return true;
    }

    /**
     * Filter for font style definitions
     *
     * @param Document $mergedDocument
     * @param DeclarationBlock $declarationBlock
     * @param $selectorAsString
     * @return bool
     */
    private function filterFonts(Document &$mergedDocument, DeclarationBlock $declarationBlock, $selectorAsString) {
        foreach ($this->iconPrefix as $prefix) {

            if (false !== strpos($selectorAsString, $prefix . '-') || 0 === strcmp('.' . $prefix, $selectorAsString)) {
                $classIsAllowed = true;

                /** @noinspection ForeachInvariantsInspection */
                for ($i = 0; $i < $this->numberOfIgnoredClasses; $i++) {

                    if (strpos($selectorAsString,$this->ignoredCssClasses[$i])) {
                        $classIsAllowed = false;
                        break;
                    }
                }

                if ($classIsAllowed) {
                    $mergedDocument->append($declarationBlock);
                    return true;
                }
            }
        }
        return false;
    }


    private function downloadFromUrl($url) {
        $browser = $this->container->get('buzz');
        $browser->get('http://zurb.com/playground/uploads/upload/upload/288/foundation-icons.zip');
    }
}