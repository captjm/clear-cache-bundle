<?php

namespace CaptJM\ClearCacheBundle\Maker;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use function Symfony\Component\String\u;

final class ClassMaker
{
    private KernelInterface $kernel;
    private string $projectDir;
    private Filesystem $fs;

    public function __construct(KernelInterface $kernel, string $projectDir)
    {
        $this->kernel = $kernel;
        $this->projectDir = $projectDir;
        $this->fs = new Filesystem();
    }

    /**
     * @return string The path of the created file (relative to the project dir)
     */
    public function make(string $generatedFilePathPattern, string $skeletonName, array $skeletonParameters): string
    {
        $skeletonPath = sprintf('%s/%s', $this->kernel->locateResource('@CaptJMClearCacheBundle/Resources/skeleton'), $skeletonName);
        $generatedFileRelativeDir = u($generatedFilePathPattern)->beforeLast('/')->trimEnd('/')->toString();
        $generatedFileNamePattern = u($generatedFilePathPattern)->afterLast('/')->trimStart('/');

        $generatedFileDir = sprintf('%s/%s', $this->projectDir, $generatedFileRelativeDir);
        $this->fs->mkdir($generatedFileDir);
        if (!$this->fs->exists($generatedFileDir)) {
            throw new \RuntimeException(sprintf('The "%s" directory does not exist and cannot be created, so the class generated by this command cannot be created.', $generatedFileDir));
        }

        // first, try to create a file name without any autoincrement index in it
        $generatedFileName = $generatedFileNamePattern->replace('{number}', '');
        $i = 1;
        while ($this->fs->exists(sprintf('%s/%s', $generatedFileDir, $generatedFileName))) {
            $generatedFileName = $generatedFileNamePattern->replace('{number}', (string) ++$i);
        }
        $generatedFilePath = sprintf('%s/%s', $generatedFileDir, $generatedFileName);

        $skeletonParameters = array_merge($skeletonParameters, [
            'class_name' => u($generatedFileName)->beforeLast('.php')->toString(),
        ]);

        $this->fs->dumpFile($generatedFilePath, $this->renderSkeleton($skeletonPath, $skeletonParameters));

        return u($generatedFilePath)->after($this->projectDir)->trim('/')->toString();
    }

    private function renderSkeleton(string $filePath, array $parameters): string
    {
        ob_start();
        extract($parameters, \EXTR_SKIP);
        include $filePath;

        return ob_get_clean();
    }
}
