<?php
declare(strict_types=1);

namespace Gang\WebComponents;

use Gang\WebComponents\Logger\WebComponentLogger;
use Gang\WebComponents\Renderer\Renderer;
use Gang\WebComponents\Renderer\TwigTemplateRenderer;
use Gang\WebComponents\Logger\WebComponentLogger as Log;
use Gang\WebComponents\Parser\Nodes\WebComponent;
use Gang\WebComponents\Parser\Parser;
use Psr\Log\LoggerInterface;

class WebComponentController
{
    private $parser;
    private $renderer;
    private $factory;
    private $library;

    public function __construct(
        ?Parser $parser=null,
        ?Renderer $renderer=null,
        ?ComponentLibrary $library=null,
        ?HTMLComponentFactory $factory = null,
        ?LoggerInterface $logger = null
    ) {
        $this->parser = $parser ?? new Parser();
        $this->library = $library ?? new ComponentLibrary();
        $this->renderer = $renderer ?? new Renderer(new TwigTemplateRenderer(), $this->library);
        $this->factory = $factory ?? new HTMLComponentFactory($library);
        if (null !== $logger) {
            WebComponentLogger::setLogger($logger);
        }
    }

    /**
     * Replaces the WebComponents for actual HTML
     */
    public function process(string $content) : string
    {
        $rendered_content = '';
        foreach ($this->parser->parse($content) as $token) {
            if ($token instanceof WebComponent) {
                Log::debug("[Controller@process] Processing parsed token <".$token->getTagName()."> as WebComponent");
                $html_component = $this->factory->create($token);
                $rendered_component = $this->renderer->render($html_component);
                $rendered_content .= $this->process($rendered_component);
                Log::debug("[Controller@process] Finished rendering <".$token->getTagName().">");
                ;
            } else {
                $fragment = substr($token->__toString(), 0, 10) . (strlen($token->__toString())>10?"...":'');
                Log::debug("[Controller@process] Processing parsed token $fragment as non WebComponent");
                $rendered_content .= $token;
            }
        }
        return $rendered_content;
    }
}
