<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Adapters\Symfony\CliApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use org\bovigo\vfs\vfsStream;

/**
 * Features context.
 */
class FeatureContext implements Context
{
    private $root;
    private $structure;
    private $output;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     */
    public function __construct()
    {
        $this->root = vfsStream::setup('project');
        $this->structure = array();
    }

    /**
     * @Given /^a PHP File named "([^"]*)" with:$/
     */
    public function aPhpFileNamedWith($file, PyStringNode $code)
    {
        $this->structure = $this->appendToTree($this->structure, $file, (string)$code);
    }

    private function appendToTree($tree, $path, $code)
    {
        @list($head, $rest) = explode("/", $path, 2); // Muting notice that happens when there are no 2 elements left.

        if (!isset($tree[$head])) {
            $tree[$head] = array();
        }

        if (empty($rest)) {
            $tree[$head] = $code;
        } else {
            $tree[$head] = $this->appendToTree($tree, $rest, $code);
        }

        return $tree;
    }

    /**
     * @When /^I use refactoring "([^"]*)" with:$/
     */
    public function iUseRefactoringWith($refactoringName, TableNode $table)
    {
        vfsStream::create($this->structure, $this->root);

        $data = array('command' => $refactoringName);
        foreach ($table->getHash() as $line) {
            $data[$line['arg']] = $line['value'];
        }

        if (isset($data['file'])) {
            $data['file'] = vfsStream::url('project/' . $data['file']);
        }
        if (isset($data['dir'])) {
            $data['dir'] = vfsStream::url('project/' . $data['dir']);
        }

        $data['--verbose'] = true;

        $fh = fopen("php://memory", "rw");
        $input = new ArrayInput($data);
        $output = new StreamOutput($fh);

        $app = new CliApplication();
        $app->setAutoExit(false);
        $app->run($input, $output);

        rewind($fh);
        $this->output = stream_get_contents($fh);
    }

    /**
     * @Then /^the PHP File "([^"]*)" should be refactored:$/
     */
    public function thePhpFileShouldBeRefactored($file, PyStringNode $expectedPatch)
    {
        $output = array_map('trim', explode("\n", rtrim($this->output)));
        $formattedExpectedPatch = $this->formatExpectedPatch((string)$expectedPatch);

        TestCase::assertEquals(
            $formattedExpectedPatch,
            $output,
            "Expected File:\n" . (string)$expectedPatch . "\n\n" .
            "Refactored File:\n" . $this->output . "\n\n" .
            "Diff:\n" . print_r(array_diff($formattedExpectedPatch, $output), true)
        );
    }

    /**
     * converts / paths in expectedPatch text to \ paths
     *
     * leaves the a/ b/ slashes untouched
     * returns an array of lines
     * @return array
     */
    protected function formatExpectedPatch($patch)
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $formatLine = function ($line) {
                // replace lines for diff-path-files starting with --- or +++
                $line = preg_replace_callback('~^((?:---|\+\+\+)\s*(?:a|b)/)(.*)~', function ($match) {
                    list($all, $diff, $path) = $match;

                    // dont replace wrapped path separators
                    if (! preg_match('~^[a-z]+://~i', $path)) {
                        $path = str_replace('/', '\\', $path);
                    }

                    return $diff.$path;

                }, $line);

                return trim($line);
            };

        } else {
            $formatLine = function ($line) {
                return trim($line);
            };
        }

        return array_map($formatLine, explode("\n", rtrim($patch)));
    }
}
