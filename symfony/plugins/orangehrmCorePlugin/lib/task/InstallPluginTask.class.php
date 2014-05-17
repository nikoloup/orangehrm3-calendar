<?php

class InstallPluginTask extends sfBaseTask{


  protected function configure()  {
    

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('plugin', null, sfCommandOption::PARAMETER_REQUIRED, 'plugin name', ''),
      
    ));

    $this->namespace        = 'orangehrm';
    $this->name             = 'Install-plugin';
    $this->briefDescription = 'This task will create database table and doctrine classes to run the  plugin';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array()){
    
        $databaseManager    = new sfDatabaseManager($this->configuration);
        $connection         = $databaseManager->getDatabase($options['connection'])->getConnection();
        
        if( empty($options['plugin'])){
            throw new Exception("Plugin name is empty");
        }

        define('SF_ROOT_DIR', realpath(dirname(__FILE__). DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'));
        define('SF_APP', 'orangehrm');
        define('SF_ENVIRONMENT', 'prod');
        define('SF_DEBUG', true);
        require_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'ProjectConfiguration.class.php');
        #
        $configuration = ProjectConfiguration::getApplicationConfiguration('orangehrm', 'prod', true);
        sfContext::createInstance($configuration);

        $pluginName = $options['plugin'];

        $installerDBCreator = new InstallerDBCreator();
        $installerDBCreator->readInstallerData($pluginName);
        $installerDBCreator->buildDataTables();
        $installerDBCreator->doSymfonyTaks();
        echo $pluginName." installed \n";
   
    
    
    
   
  }
}
