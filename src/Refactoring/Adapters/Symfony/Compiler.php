<?php

namespace QafooLabs\Refactoring\Adapters\Symfony;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * The Compiler class compiles composer into a phar.
 *
 * Converted from Composer's compiler.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class Compiler
{
    private $version;

    private $directory;

    public function __construct($directory)
    {
        $this->directory = realpath($directory);
        $this->version = $this->getVersion();
    }

    /**
     * Compiles composer into a single phar file.
     *
     * @throws \RuntimeException
     */
    public function compile()
    {
        $pharFile = 'refactor.phar';

        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $phar = new \Phar($pharFile, 0, 'refactor.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        $this->includeSrc($phar);
        $this->includeVendor($phar);
        $this->includeBin($phar);

        $phar->setStub($this->getStub());
        $phar->stopBuffering();

        unset($phar);
    }

    private function includeVendor(\Phar $phar)
    {
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->exclude('test')
            ->exclude('tests')
            ->exclude('features')
            ->in([$this->directory.'/vendor'])
        ;

        foreach ($finder as $file) {
            $path = str_replace($this->directory.DIRECTORY_SEPARATOR, '', $file->getRealPath());
            $phar->addFile($file->getRealPath(), $path);
        }
    }

    private function includeSrc(\Phar $phar)
    {
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->notName('Compiler.php')
            ->in($this->directory.'/src')
        ;

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        return $finder;
    }

    private function includeBin(\Phar $phar)
    {
        $content = file_get_contents($this->directory.'/bin/refactor');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/refactor', $content);
    }

    private function getVersion()
    {
        $process = new Process(['git', 'log', '--pretty="%H"', '-n1', 'HEAD'], $this->directory);
        if ($process->run() != 0) {
            throw new \RuntimeException('Can\'t run git log. You must ensure to run compile from git repository clone and that git binary is available.');
        }
        $version = trim($process->getOutput());

        $process = new Process(['git', 'describe', '--tags', 'HEAD']);
        if ($process->run() == 0) {
            return trim($process->getOutput());
        }

        return $version;
    }

    private function addFile($phar, $file, $strip = true)
    {
        $path = str_replace($this->directory.DIRECTORY_SEPARATOR, '', $file->getRealPath());

        $content = file_get_contents($file);
        if ($strip) {
            $content = $this->stripWhitespace($content);
        } elseif (basename($file) === 'LICENSE') {
            $content = "\n".$content."\n";
        }

        $content = str_replace('@package_version@', $this->version, $content);

        $phar->addFromString($path, $content);
    }

    /**
     * Removes whitespace from a PHP source string while preserving line numbers.
     *
     * @param string $source A PHP string
     *
     * @return string The PHP string with the whitespace removed
     */
    private function stripWhitespace($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], [T_COMMENT, T_DOC_COMMENT])) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif ($token[0] === T_WHITESPACE) {
                // reduce wide spaces
                $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace('{\n +}', "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }

    private function getStub()
    {
        return <<<'EOF'
#!/usr/bin/env php
<?php
Phar::mapPhar('refactor.phar');

require 'phar://refactor.phar/bin/refactor';

__HALT_COMPILER();
EOF;
    }
}
