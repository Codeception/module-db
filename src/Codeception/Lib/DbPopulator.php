<?php

declare(strict_types=1);

namespace Codeception\Lib;

/**
 * Populates a db using a parameterized command built from the Db module configuration.
 */
class DbPopulator
{
    protected array $config = [];

    protected array $commands = [];

    /**
     * Constructs a DbPopulator object for the given command and Db module.
     *
     * @internal param string $command The parameterized command to evaluate and execute later.
     * @internal param Codeception\Module\Db|null $dbModule The Db module used to build the populator command or null.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        //Convert To Array Format
        if (!isset($this->config['dump'])) {
            return;
        }

        if (is_array($this->config['dump'])) {
            return;
        }

        $this->config['dump'] = [$this->config['dump']];
    }

    /**
     * Builds out a command replacing any found `$key` with its value if found in the given configuration.
     *
     * Process any $key found in the configuration array as a key of the array and replaces it with
     * the found value for the key. Example:
     *
     * ```php
     * <?php
     *
     * $command = 'Hello $name';
     * $config = ['name' => 'Mauro'];
     *
     * // With the above parameters it will return `'Hello Mauro'`.
     * ```
     *
     * @param string $command The command to be evaluated using the given config
     * @param string|null $dumpFile The dump file to build the command with.
     * @return string The resulting command string after evaluating any configuration's key
     */
    protected function buildCommand(string $command, ?string $dumpFile = null): string
    {
        $dsn = $this->config['dsn'] ?? '';
        $dsnVars = [];
        $dsnWithoutDriver = preg_replace('#^[a-z]+:#i', '', $dsn);
        foreach (explode(';', $dsnWithoutDriver) as $item) {
            $keyValueTuple = explode('=', $item);
            if (count($keyValueTuple) > 1) {
                [$k, $v] = array_values($keyValueTuple);
                $dsnVars[$k] = $v;
            }
        }

        $vars = array_merge($dsnVars, $this->config);

        if ($dumpFile !== null) {
            $vars['dump'] = $dumpFile;
        }

        foreach ($vars as $key => $value) {
            if (!is_array($value)) {
                $vars['$' . $key] = $value;
            }

            unset($vars[$key]);
        }

        return str_replace(array_keys($vars), $vars, $command);
    }

    /**
     * Executes the command built using the Db module configuration.
     *
     * Uses the PHP `exec` to spin off a child process for the built command.
     */
    public function run(): bool
    {
        foreach ($this->buildCommands() as $command) {
            $this->runCommand($command);
        }

        return true;
    }

    private function runCommand($command): void
    {
        codecept_debug("[Db] Executing Populator: `{$command}`");

        exec($command, $output, $exitCode);

        if (0 !== $exitCode) {
            throw new \RuntimeException(
                "The populator command did not end successfully: \n" .
                "  Exit code: {$exitCode} \n" .
                "  Output:" . implode("\n", $output)
            );
        }

        codecept_debug("[Db] Populator Finished.");
    }

    public function buildCommands(): array
    {
        if ($this->commands !== []) {
            return $this->commands;
        } elseif (!isset($this->config['dump']) || $this->config['dump'] === false) {
            return [$this->buildCommand($this->config['populator'])];
        }

        $this->commands = [];

        foreach ($this->config['dump'] as $dumpFile) {
            $this->commands[] = $this->buildCommand($this->config['populator'], $dumpFile);
        }

        return $this->commands;
    }
}
