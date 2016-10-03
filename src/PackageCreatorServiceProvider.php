<?php

namespace Muilman\PackageCreator;

use Illuminate\Support\ServiceProvider;

class PackageCreatorServiceProvider extends ServiceProvider {

    protected $commands = array(
        'Muilman\PackageCreator\Commands\CreateNewPackageCommand'
    );

	public function register() {

        $this->commands($this->commands);
		
	}

}