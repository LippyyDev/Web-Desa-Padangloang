<?php

/**
 * HTML Purifier Helper
 * 
 * Provides a helper function to sanitize HTML content, allowing safe
 * formatting tags while removing dangerous elements like <script>, onclick, etc.
 */

if (!function_exists('purify_html')) {
    /**
     * Sanitize HTML content using HTMLPurifier.
     * Allows safe formatting tags (p, br, strong, em, ul, ol, li, h1-h6, a, img, table, etc.)
     * while stripping dangerous elements and attributes.
     *
     * @param string|null $html The raw HTML string to purify
     * @return string Sanitized HTML string
     */
    function purify_html(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        static $purifier = null;

        if ($purifier === null) {
            $config = \HTMLPurifier_Config::createDefault();
            
            // Cache directory for HTMLPurifier serializer
            $cachePath = WRITEPATH . 'cache/htmlpurifier';
            if (!is_dir($cachePath)) {
                mkdir($cachePath, 0775, true);
            }
            $config->set('Cache.SerializerPath', $cachePath);
            
            // Allow safe HTML elements typically used by WYSIWYG editors
            $config->set('HTML.Allowed', 
                'p,br,strong,b,em,i,u,s,strike,sub,sup,' .
                'h1,h2,h3,h4,h5,h6,' .
                'ul,ol,li,' .
                'a[href|target|rel|title],' .
                'img[src|alt|width|height|style],' .
                'table[border|cellpadding|cellspacing|style],thead,tbody,tfoot,tr,th[colspan|rowspan|style],td[colspan|rowspan|style],' .
                'blockquote,pre,code,' .
                'hr,div[style],span[style]'
            );
            
            // Allow safe CSS properties
            $config->set('CSS.AllowedProperties', 
                'font-size,font-weight,font-style,font-family,' .
                'text-align,text-decoration,text-transform,' .
                'color,background-color,' .
                'margin,margin-top,margin-bottom,margin-left,margin-right,' .
                'padding,padding-top,padding-bottom,padding-left,padding-right,' .
                'border,border-collapse,' .
                'width,max-width,height,' .
                'list-style-type,' .
                'display,float,clear'
            );
            
            // Allow target="_blank" on links
            $config->set('Attr.AllowedFrameTargets', ['_blank']);

            // Automatically add rel="noopener noreferrer" to external links
            $config->set('HTML.Nofollow', true);

            // Set encoding
            $config->set('Core.Encoding', 'UTF-8');

            $purifier = new \HTMLPurifier($config);
        }

        return $purifier->purify($html);
    }
}
