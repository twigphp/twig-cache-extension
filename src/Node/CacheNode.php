<?php

/*
 * This file is part of twig-cache-extension.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Twig\CacheExtension\Node;

/**
 * Cache twig node.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class CacheNode extends \Twig_Node
{
    private static $cacheCount = 1;

    /**
     * @param \Twig_Node_Expression $annotation
     * @param \Twig_Node_Expression $keyInfo
     * @param \Twig_NodeInterface   $body
     * @param integer               $lineno
     * @param string                $tag
     */
    public function __construct(\Twig_Node_Expression $annotation, \Twig_Node_Expression $keyInfo, \Twig_Node $body, $lineno, $tag = null)
    {
        parent::__construct(array('key_info' => $keyInfo, 'body' => $body, 'annotation' => $annotation), array(), $lineno, $tag);
    }

    /**
     * {@inheritDoc}
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $i = self::$cacheCount++;

        if (version_compare(\Twig_Environment::VERSION, '1.26.0', '>=')) {
            $extension = 'Twig\CacheExtension\Extension';
        } else {
            $extension = 'twig_cache';
        }

        $compiler
            ->addDebugInfo($this)
            ->write("\$twigCacheStrategy".$i." = \$this->env->getExtension('{$extension}')->getCacheStrategy();\n")
            ->write("\$twigKey".$i." = \$twigCacheStrategy".$i."->generateKey(")
                ->subcompile($this->getNode('annotation'))
                ->raw(", ")
                ->subcompile($this->getNode('key_info'))
            ->write(");\n")
            ->write("\$twigCacheBody".$i." = \$twigCacheStrategy".$i."->fetchBlock(\$twigKey".$i.");\n")
            ->write("if (\$twigCacheBody".$i." === false) {\n")
            ->indent()
                ->write("ob_start();\n")
                    ->indent()
                        ->subcompile($this->getNode('body'))
                    ->outdent()
                ->write("\n")
                ->write("\$twigCacheBody".$i." = ob_get_clean();\n")
                ->write("\$twigCacheStrategy".$i."->saveBlock(\$twigKey".$i.", \$twigCacheBody".$i.");\n")
            ->outdent()
            ->write("}\n")
            ->write("echo \$twigCacheBody".$i.";\n")
        ;
    }
}
