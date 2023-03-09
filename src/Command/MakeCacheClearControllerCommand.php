<?php

namespace CaptJM\ClearCacheBundle\Command;

use CaptJM\ClearCacheBundle\Maker\ClassMaker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use function Symfony\Component\String\u;

#[AsCommand(
    name: 'make:cache-clear-controller',
    description: 'Creates a new Cache Clear Controller class',
)]
class MakeCacheClearControllerCommand extends Command
{
    private ClassMaker $classMaker;
    private string $projectDir;

    public function __construct(ClassMaker $classMaker, string $projectDir, string $name = null)
    {
        parent::__construct($name);
        $this->classMaker = $classMaker;
        $this->projectDir = $projectDir;
    }

    protected function configure()
    {
        $this
            ->setHelp($this->getCommandHelp())
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fs = new Filesystem();

        $controllerClassName = $io->ask(
            'Which class name do you prefer for your Clear Cache controller?',
            'ClearCacheController',
            fn (string $className): string => u($className)->ensureEnd('Controller')->toString()
        );

        $projectDir = $this->projectDir;
        $controllerDir = $io->ask(
            sprintf('In which directory of your project do you want to generate "%s"?', $controllerClassName),
            'src/Controller/Admin/',
            static function (string $selectedDir) use ($fs, $projectDir) {
                $absoluteDir = u($selectedDir)->ensureStart($projectDir.\DIRECTORY_SEPARATOR);
                if (null !== $absoluteDir->indexOf('..')) {
                    throw new \RuntimeException(sprintf('The given directory path can\'t contain ".." and must be relative to the project directory (which is "%s")', $projectDir));
                }

                $fs->mkdir($absoluteDir);

                if (!$fs->exists($absoluteDir)) {
                    throw new \RuntimeException('The given directory does not exist and couldn\'t be created. Type in the path of an existing directory relative to your project root (e.g. src/Controller/Admin/)');
                }

                return $absoluteDir->after($projectDir.\DIRECTORY_SEPARATOR)->trimEnd(\DIRECTORY_SEPARATOR)->toString();
            }
        );

        $controllerFilePath = sprintf('%s/%s.php', u($controllerDir)->ensureStart($projectDir.\DIRECTORY_SEPARATOR), $controllerClassName);
        if ($fs->exists($controllerFilePath)) {
            throw new \RuntimeException(sprintf('The "%s.php" file already exists in the given "%s" directory. Use a different controller name or generate it in a different directory.', $controllerClassName, $controllerDir));
        }

        $guessedNamespace = u($controllerDir)->equalsTo('src')
            ? 'App'
            : u($controllerDir)->replace('/', ' ')->replace('\\', ' ')->replace('src ', 'app ')->title(true)->replace(' ', '\\')->trimEnd('\\');

        $generatedFilePath = $this->classMaker->make(sprintf('%s/%s.php', $controllerDir, $controllerClassName), 'clear_cache_controller.tpl', [
            'namespace' => $guessedNamespace,
        ]);

        $io = new SymfonyStyle($input, $output);
        $io->success('Your cache clear controller class has been successfully generated.');

        return Command::SUCCESS;
    }

    private function getCommandHelp(): string
    {
        return <<<'HELP'
            The <info>%command.name%</info> command creates a new ClearCacheController class
            in your application. Follow the steps shown by the command to configure the
            name and location of the new class.
            HELP;
    }
}