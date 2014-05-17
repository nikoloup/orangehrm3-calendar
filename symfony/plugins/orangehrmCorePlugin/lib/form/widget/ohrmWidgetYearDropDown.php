<?php

/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 */

/**
 * Description of ohrmWidgetSubUnit
 *
 */
class ohrmWidgetYearDropDown extends sfWidgetFormSelect {
    
    private $choices = null;
      
    protected function configure($options = array(), $attributes = array()) {
                
        parent::configure($options, $attributes);
        
        $this->addOption('show_all_option', false);
       
        // Parent requires the 'choices' option.
        $this->addOption('choices', array());

    }
    
    /**
     * Get array of subunit choices
     */
    public function getChoices() {
        
        if (is_null($this->choices)) {

            $choices = array(
			2010 => '2010',
			2011 => '2011',
			2012 => '2012',
			2013 => '2013',
			2014 => '2014',
			2015 => '2015',
			2016 => '2016',
			2017 => '2017',
			2018 => '2018',
			2019 => '2019',
			2020 => '2020'
		);
           
            $this->choices = $choices;            
        }
        
//        asort($this->choices);
        return $this->choices;               
    }
    
    public function getValidValues() {
        $choices = $this->getChoices();
        return array_keys($choices);
    }
    
    
}

