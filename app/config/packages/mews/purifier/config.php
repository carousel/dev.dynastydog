<?php

/*
 * This file is part of HTMLPurifier Bundle.
 * (c) 2012 Maxime Dizerens
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return array(
	'encoding' => 'UTF-8',
    'finalize' => true,
    'preload'  => false,
    'settings' => array(
        'default' => array(
            'HTML.Doctype'             => 'XHTML 1.0 Strict',
            'HTML.Allowed'             => 'b,strong,em,small,a[href|title],ul,ol,li,p,br,span[style],img[width|height|alt|src]',
            'CSS.AllowedProperties'    => 'font,font-size,font-weight,font-style,font-family,text-decoration,color,background-color,text-align',
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty'   => true,
        ),
        'linkify' => array(
            'HTML.Doctype'             => 'XHTML 1.0 Strict',
            'HTML.Allowed'             => 'b,strong,em,small,a[href|title],ul,ol,li,p,br,span[style],img[width|height|alt|src]',
            'CSS.AllowedProperties'    => 'font,font-size,font-weight,font-style,font-family,text-decoration,color,background-color,text-align',
            'HTML.TargetBlank'         => true,
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty'   => true,
            'AutoFormat.Linkify'       => true,
        ),
        'strip_all' => array(
            'HTML.Allowed'             => '',
        ),
        'only_linkify' => array(
            'HTML.Allowed'             => 'a[href]',
            'HTML.TargetBlank'         => true,
            'AutoFormat.Linkify'       => true,
        ),
    ),
);
