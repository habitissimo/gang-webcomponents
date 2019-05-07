<?php
declare(strict_types=1);

namespace Gang\WebComponents;

use Gang\WebComponents\Logger\WebComponentLogger;
use Gang\WebComponents\Logger\WebComponentLogger as Log;
use Gang\WebComponents\Parser\NewParser;
use Gang\WebComponents\Parser\Nodes\WebComponent;
use Gang\WebComponents\Renderer\TreeRenderer;
use Psr\Log\LoggerInterface;

class WebComponentController
{
    static  $instance;
    private $parser;
    private $renderer;


    public function __construct(
      ?ComponentLibrary $library = null, ?TreeRenderer $renderer = null, ?NewParser $parser = null, ?LoggerInterface $logger = null
    ) {
        $library = $library ?? new ComponentLibrary();
        $this->parser = $parser ?? new NewParser();
        $this->renderer = $renderer ?? new TreeRenderer($library);
        if (null !== $logger) {
            WebComponentLogger::setLogger($logger);
        }

        self::$instance = $this;
    }

  /**
   * Replaces the WebComponents for actual HTML
   * @param string $content
   * @return string
   */
    public function process(string $content) : string
    {
        $rendered_content = '';
        foreach ($this->parser->parse($content) as $token) {
            if ($token instanceof WebComponent) {
                $rendered_content .= $this->process($this->renderer->render($token));
            } else {
                $fragment = substr($token->__toString(), 0, 10) . (strlen($token->__toString())>10?"...":'');
                Log::debug("[Controller@process] Processing parsed token $fragment as non WebComponent");
                $rendered_content .= $token;
            }
        }
        return $rendered_content;
    }
}
