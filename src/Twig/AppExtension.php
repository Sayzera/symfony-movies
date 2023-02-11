<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('slugify', [$this, 'slugify']),
            new TwigFilter('form_error', [$this, 'form_error']),
            new TwigFilter('external_link', array($this, 'externalLinkFilter')),

        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('function_name', [$this, 'doSomething']),

        ];
    }

    public function externalLinkFilter($url)
    {
        if (strpos('http', $url) && strpos('https', $url) === false) {
            $url = preg_replace('/http/', 'http:', $url);
        } else {
            $url = '//' . $url;
        }
        return $url;
    }

    public function slugify($string)
    {
        $_string =  preg_replace('/ +/', "-", $string);

        $_string = mb_strtolower(preg_replace('/[^A-Za-z0-9-]+/', '', $_string));

        return $_string;
    }

    public function form_error($val)
    {
        // form.name.vars.errors
        if (count($val->vars['errors']) > 0) {
            return $val->vars['errors'][0]->getMessage();
        } else {
            return '';
        }
    }
}
