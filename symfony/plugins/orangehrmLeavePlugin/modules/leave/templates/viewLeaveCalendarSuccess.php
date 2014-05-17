<?php
//nikoloup
/*
 *
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
 *
 */

use_stylesheets_for_form($form);
use_javascripts_for_form($form);
?>
<?php if ($form->hasErrors()): ?>
    <div class="messagebar">
        <?php include_partial('global/form_errors', array('form' => $form)); ?>
    </div>
<?php endif; ?>
<div class="box toggableForm" id="leave-list-search">
    <div class="head">
        <h1><?php echo __($form->getTitle());?></h1>
    </div>
    <div class="inner">
        <form id="frmFilterLeave" name="frmFilterLeave" method="post" action="<?php echo url_for($baseUrl); ?>">

            <fieldset>                
                <ol>
                    <?php echo $form->render(); ?>
                </ol>            
                
                <p>
                    <?php
                    $searchActionButtons = $form->getSearchActionButtons();
                    foreach ($searchActionButtons as $id => $button) {
                        echo $button->render($id), "\n";
                    }
                    ?>                    
                    <?php include_component('core', 'ohrmPluginPannel', array('location' => 'listing_layout_navigation_bar_1')); ?>
                    <input type="hidden" name="pageNo" id="pageNo" value="" />
                    <input type="hidden" name="hdnAction" id="hdnAction" value="search" />
                    
                </p>                
            </fieldset>
            
        </form>
        
    </div> <!-- inner -->
    <a href="#" class="toggle tiptip" title="<?php echo __(CommonMessages::TOGGABLE_DEFAULT_MESSAGE); ?>">&gt;</a>
</div> <!-- leave-list-search -->

<div class="box toggableForm">
	<div class="head"><h1>Calendar</h1></div>
	<div class="inner">
		<div id="controls" <?php echo $messageType=='nodata'?'style="display:none;"':""?>>
			<span id="switchButton"><input type="button" value="Switch View" id="switchButton2" /></span>
			<span id="nav_buttons">
				<input type="button" value="Previous" onClick="universal_prev()"/>
				<input type="button" value="Next" onClick="universal_next()"/>
			</span>
			<span id="dateHeader"></span>
		</div>
		<div id="calendar">
			<?php echo $messageType=='nodata'?'<div style="color:red;">'.$message.'</div>':'' ?>	
		</div>
		<div id="table_calendar">
			<?php echo $messageType=='nodata'?'<div style="color:red;">'.$message.'</div>':'' ?>
		</div>
		<?php include_component('core','ohrmCalendar');?>
		<div id="legend"></div>
		</br>
	</div>
</div>

<div id="dialogColor" class="modal hide midsize">
  <div class="modal-header">
    <a data-dismiss="modal" class="close">×</a>
    <h3>Color settings</h3>
  </div>
  <div class="modal-body">
  </div>
  <div class="modal-footer">
    <input type="button" value="Save" id="colorSave" class="btn">
    <input type="button" value="Cancel" id="commentCancel" data-dismiss="modal" class="btn reset">
  </div>
</div>

<script type="text/javascript">
       var resetUrl = '<?php echo url_for($baseUrl . '?reset=1'); ?>';
</script>
