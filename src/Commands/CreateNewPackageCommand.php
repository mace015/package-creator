<?php

namespace Muilman\PackageCreator\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CreateNewPackageCommand extends Command {

    protected $signature = 'make:package';
    protected $description = 'Create a new custom package.';

    protected $file;

    public function __construct(Filesystem $file) {
        parent::__construct();

        $this->file = $file;
    }

    public function handle() {
        
        $this->info('Welcome to the package creation wizard, please fill in the questions below to generate your package!');

        $vendor = $this->ask('What is your vendor name?');
        $package = $this->ask('What is the name of your package?');
        $description = $this->ask('What is the description of your package?');
        $namespace = $this->ask('What is the namespace of your package?');

        $author = $this->ask('What is your name?');
        $email = $this->ask('What is your email?');

        $this->info('Summary: Your name is ' . $author . ', your email adress is ' . $email . '. The package you want to create is called ' . $vendor . '/' . $package . '.');

        if ($this->confirm('Is this correct? [yes|no]')) {

            $path = base_path() . '/packages/' . ucfirst($vendor) . '/' . ucfirst($package);

            @$this->file->makeDirectory($path . '/src', 0755, true);
            @$this->file->put($path . '/composer.json', $this->getComposer($vendor, $package, $description, $namespace, $author, $email));
            @$this->file->put($path . '/src/' . $namespace . 'ServiceProvider.php', $this->getServiceProvider($vendor, $namespace));

        } else {

            $this->error('Cancelled package creation.');
            
        }

    }

    protected function getComposer($vendor, $package, $description, $namespace, $author, $email) {

        return str_replace(
            array('{{vendor-name}}', '{{package-name}}', '{{package-description}}', '{{namespace}}', '{{author-name}}', '{{author-email}}'), 
            array($vendor, $package, $description, $namespace, $author, $email), 
            $this->file->get(__DIR__ . '/../Templates/Composer.txt')
        );

    }

    protected function getServiceProvider($vendor, $namespace) {

        return str_replace(
            array('{{vendor-name}}', '{{namespace}}'), 
            array($vendor, $namespace), 
            $this->file->get(__DIR__ . '/../Templates/ServiceProvider.txt')
        );

    }

}