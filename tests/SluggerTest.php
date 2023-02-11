<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Twig\AppExtension;

class SluggerTest extends TestCase
{
    /**
     * @dataProvider getSlugs
     */
    public function testSlugify(string $string, string $slug)
    {
        $slugger = new AppExtension();
        // Benzer çıktıyı üretmesi beklenir
        $this->assertSame($slug, $slugger->slugify($string));

        /**
         * test edebilmek için console  php ./bin/phpunit yazıp çalıştırıyoruz
         */
    }

    public function getSlugs()
    {
        yield   ['Lorem Ipsum', 'lorem-ipsum'];
        yield   ['Lorem Ipsum', 'lorem-ipsum'];
        yield   ['Lorem Ipsum', 'lorem-ipsum'];
    }
}
