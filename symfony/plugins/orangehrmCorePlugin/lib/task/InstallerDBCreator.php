<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdvanceLeaveDBCreator
 *
 * @author poojitha
 */
class InstallerDBCreator {

    public $installData;
    /**
     *
     * @param type $pluginName
     * @return type null
     */
    public function readInstallerData($pluginName) {

        try {
            $this->installData = sfYaml::load(sfConfig::get('sf_plugins_dir') . DIRECTORY_SEPARATOR . "$pluginName" . DIRECTORY_SEPARATOR . "install" . DIRECTORY_SEPARATOR . "installer.yml");
        } catch (Exception $e) {

            throw new Exception( $e->getMessage());
        }

    }
    /**
     * Build SQL Tables
     * 
     * @return type null
     */
    public function buildDataTables() {
        try {

            $sqlPaths = $this->installData['dbscript_path'];
            
            if (!is_array($sqlPaths)) {
                $sqlPaths = array($sqlPaths);
            }
            
            $sqlString = '';
            
            foreach ($sqlPaths as $sqlPath) {
                $sqlString = $sqlString . file_get_contents(sfConfig::get('sf_plugins_dir') . DIRECTORY_SEPARATOR . $this->installData['plugin_name'] . DIRECTORY_SEPARATOR . $sqlPath);
            }
            
            if (!empty($sqlString)) {
                $q = Doctrine_Manager::getInstance()->getCurrentConnection();
                $patterns = array();
                $patterns[0] = '/DELIMITER \\$\\$/';
                $patterns[1] = '/DELIMITER ;/';
                $patterns[2] = '/\\$\\$/';

                $new_sql_string = preg_replace($patterns, '', $sqlString);
                $result = $q->exec($new_sql_string);


                foreach ($sqlPaths as $value) {
                    echo "Execute " . $value . " file \n";
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    /**
     * do symfoony tasks
     */
    public function doSymfonyTaks() {

        $commands_list = $this->installData['symfony_commands'];

        foreach ($commands_list as $key => $val) {
            exec($val);
            echo "Execute ".$val." \n";
        }
    }
    
  
}