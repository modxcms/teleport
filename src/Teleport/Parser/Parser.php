<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) Jason Coward <jason@opengeek.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Parser;

class Parser extends \modParser
{
    public function processElementTags($parentTag, & $content, $processUncacheable = false, $removeUnprocessed = false, $prefix = "{+", $suffix = "}", $tokens = array(), $depth = 10)
    {
        $this->_processingTag = true;
        $this->_processingUncacheable = (boolean)$processUncacheable;
        $this->_removingUnprocessed = (boolean)$removeUnprocessed;
        $depth = $depth > 0 ? $depth - 1 : 0;
        $processed = 0;
        $tags = array();
        if ($collected = $this->collectElementTags($content, $tags, $prefix, $suffix, $tokens)) {
            $tagMap = array();
            foreach ($tags as $tag) {
                if ($tag[0] === $parentTag) {
                    $tagMap[$tag[0]] = '';
                    $processed++;
                    continue;
                }
                $tagOutput = $this->processTag($tag, $processUncacheable);
                if ($tagOutput !== null && $tagOutput !== false) {
                    $tagMap[$tag[0]] = $tagOutput;
                    if ($tag[0] !== $tagOutput) $processed++;
                }
            }
            $this->mergeTagOutput($tagMap, $content);
            if ($depth > 0) {
                $processed += $this->processElementTags($parentTag, $content, $processUncacheable, $removeUnprocessed, $prefix, $suffix, $tokens, $depth);
            }
        }
        $this->_processingTag = false;
        return $processed;
    }

    public function processTag($tag, $processUncacheable = true)
    {
        $this->_processingTag = true;
        $element = null;
        $elementOutput = null;

        $outerTag = $tag[0];
        $innerTag = $tag[1];

        /* collect any nested element tags in the innerTag and process them */
        $this->processElementTags($outerTag, $innerTag, $processUncacheable);
        $this->_processingTag = true;
        $outerTag = '{+' . $innerTag . '}';

        $elementOutput = (string)$this->modx->getPlaceholder($innerTag);

        if ($elementOutput === null || $elementOutput === false) {
            $elementOutput = $outerTag;
        }
        if ($this->modx->getDebug() === true) {
            $this->modx->log(\xPDO::LOG_LEVEL_DEBUG, "Processing {$outerTag} as {$innerTag}:\n" . print_r($elementOutput, 1) . "\n\n");
            /* $this->modx->cacheManager->writeFile(MODX_BASE_PATH . 'parser.log', "Processing {$outerTag} as {$innerTag}:\n" . print_r($elementOutput, 1) . "\n\n", 'a'); */
        }
        $this->_processingTag = false;
        return $elementOutput;
    }
} 
