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

        $vendor = $this->ask('What is your vendor name? (Example: Company name or your last name)');
        $package = $this->ask('What is the name of your package? (Example: AwesomePackage)');
        $description = $this->ask('What is the description of your package?');
        $namespace = $this->ask('What is the namespace of your package? (Example: AwesomePackage)');

        $author = $this->ask('What is your name?');
        $email = $this->ask('What is your email?');

        $makeRepository = $this->confirm('Do you wish to start a git repository for this package?');
        if ($makeRepository){
            $existingRepository = $this->confirm('Do you already have a remote repository for this package?');
            if ($existingRepository) {
                $remoteRepository = $this->ask('What is the name of the remote repository? (Example: mace015/package-creator)');
            }
        }

        $this->comment('Please check if the following details are correct;');

        $this->table(['Question', 'Answer'],[
            ['Your vendor name is: ', $vendor],
            ['Your package name is: ', $package],
            ['The description of your package is: ', $description],
            ['The namespace of your package is: ', $namespace],
            ['Your name is: ', $author],
            ['Your email is: ', $email],
            ['Start a git repository?', (($makeRepository)? 'Yes' : 'No')],
            ['Connect a remote repository?', (($existingRepository)? 'Yes: ' . $remoteRepository : 'No')],
        ], 'default');

        if ($this->confirm('Is this correct?')) {

            $path = base_path() . '/packages/' . ucfirst($vendor) . '/' . ucfirst($package);

            @$this->file->makeDirectory($path . '/src', 0755, true);
            @$this->file->put($path . '/composer.json', $this->getComposer($vendor, $package, $description, $namespace, $author, $email));
            @$this->file->put($path . '/src/' . $namespace . 'ServiceProvider.php', $this->getServiceProvider($vendor, $namespace));

            if($makeRepository){

                exec('cd packages/'. ucfirst($vendor) .'/'. ucfirst($package) .' && git init');

                $this->info('Git repository initialised.');

                if ($existingRepository) {

                    exec('cd packages/'. ucfirst($vendor) .'/'. ucfirst($package) .' && git remote add origin git@github.com:'. $remoteRepository .'.git');

                    $this->info('Remote repository added.');

                }

            }

            $this->info('Your package has been created and is ready for use at /packages/'. ucfirst($vendor) .'/'. ucfirst($package));

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