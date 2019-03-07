<?php

namespace Gang\WebComponents\Parser;

use Gang\WebComponents\Exceptions\ParserException;

class InnerHTMLExtractor
{
    public static function extract(string $outerHtml, string $tagName): string
    {
        $matching_tags = [];
        preg_match_all('/(<\/?'.$tagName.')( ([^>])*?)?(?:(\'|").*>.*(\'|"))?\/?>/m',$outerHtml,$matching_tags,PREG_OFFSET_CAPTURE);
        if(array_key_exists(0,$matching_tags[0])) {
            $opening_tag_end = self::getOpeningTagEndPosition($matching_tags);
            $innerHTML = substr($outerHtml,$opening_tag_end);
            if( count ($matching_tags[0]) > 1) {
                $closing_tag_start = self::getClosingTagStartPosition($matching_tags);
                $innerHTML = substr($innerHTML,0,$closing_tag_start-$opening_tag_end);
            }
        } else {
            throw new ParserException($tagName . " tag not found in " . $outerHtml, $tagName);
        }
        return $innerHTML;
    }


    private static function getOpeningTagEndPosition(array $matching_tags) : int
    {
        return $matching_tags[0][0][1] + strlen($matching_tags[0][0][0]);
    }

    private static function getClosingTagStartPosition(array $matching_tags) : int
    {
        return $matching_tags[0][count($matching_tags[0])-1][1];
    }
}
