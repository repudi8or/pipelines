<?php

/* this file is part of pipelines */

namespace Ktomk\Pipelines\Runner;

use Ktomk\Pipelines\Cli\Exec;
use Ktomk\Pipelines\File\BbplMatch;

class ArtifactSource
{
    /**
     * @var Exec
     */
    private $exec;

    /**
     * @var string container id
     */
    private $id;

    /**
     * @var string in-container build directory
     */
    private $dir;

    /**
     * @var array|null all in-container build directory file paths
     */
    private $allFiles;

    /**
     * ArtifactSource constructor.
     * @param Exec $exec
     * @param string $id container id
     * @param string $dir in-container build directory ($BITBUCKET_CLONE_DIR)
     */
    public function __construct(Exec $exec, $id, $dir)
    {
        $this->exec = $exec;
        $this->id = $id;
        $this->dir = $dir;
    }

    /**
     * @return array|string[]
     */
    public function getAllFiles()
    {
        if ($this->allFiles === null) {
            $this->allFiles = $this->getFindPaths();
        }

        return $this->allFiles;
    }

    public function findByPattern($pattern)
    {
        $matcher = function ($subject) use ($pattern) {
            return BbplMatch::match($pattern, $subject);
        };

        $paths = $this->getAllFiles();

        $found = array_filter($paths, $matcher);

        return array_values($found);
    }

    /**
     * get an array of paths obtained via docker exec & find
     *
     * @return array
     */
    private function getFindPaths()
    {
        $buffer = $this->getFindBuffer();

        $lines = explode("\n", trim($buffer, "\n"));
        $pattern = '~^\\./~';
        $existing = preg_grep($pattern, $lines);
        $paths = preg_replace($pattern, '', $existing);

        return array_values($paths);
    }

    private function getFindBuffer()
    {
        $command = array(
            'find', '(', '-name', '.git', '-o', '-name', '.idea', ')',
            '-prune', '-type', 'f', '-o', '-type', 'f',
        );

        $status = $this->exec->capture('docker', array(
            'exec', '-w', '/app', $this->id, $command
        ), $out);

        if (0 === $status) {
            return $out;
        }

        return '';
    }
}