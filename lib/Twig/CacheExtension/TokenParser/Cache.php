<?php

/*
 * This file is part of twig-cache-extension.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Twig\CacheExtension\TokenParser;

use Twig\CacheExtension\Node\CacheNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * Parser for cache/endcache blocks.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class Cache extends AbstractTokenParser
{
    /**
     * @param Token $token
     *
     * @return boolean
     */
    public function decideCacheEnd(Token $token)
    {
        return $token->test('endcache');
    }

    /**
     * {@inheritDoc}
     */
    public function getTag()
    {
        return 'cache';
    }

    /**
     * {@inheritDoc}
     */
    public function parse(Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $annotation = $this->parser->getExpressionParser()->parseExpression();

        $key = $this->parser->getExpressionParser()->parseExpression();

        $stream->expect(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideCacheEnd'), true);
        $stream->expect(Token::BLOCK_END_TYPE);

        return new CacheNode($annotation, $key, $body, $lineno, $this->getTag());
    }
}
