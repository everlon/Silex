<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Silex;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Kernel;

/**
 * The Compiler class compiles the Silex framework.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.org>
 */
class Compiler
{
    public function compile($pharFile = 'silex.phar')
    {
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $phar = new \Phar($pharFile, 0, 'Silex');
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        $finder = new Finder();
        $finder->
            files()->
            ignoreVCS(true)->
            name('*.php')->
            exclude('tests')->
            exclude('Bundle')->
            in(__DIR__.'/../..');

        foreach ($finder as $file) {
            $path = str_replace(realpath(__DIR__.'/../..').'/', '', realpath($file));
            $content = Kernel::stripComments(file_get_contents($file));

            $phar->addFromString($path, $content);
        }

        // Stubs
        $phar['_cli_stub.php'] = $this->getStub();
        $phar['_web_stub.php'] = $this->getStub();
        $phar->setDefaultStub('_cli_stub.php', '_web_stub.php');

        $phar->stopBuffering();

        // $phar->compressFiles(\Phar::GZ);

        unset($phar);
    }

    protected function getStub()
    {
        return <<<EOF
<?php
/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require_once __DIR__.'/autoload.php';

__HALT_COMPILER();
EOF;
    }
}
