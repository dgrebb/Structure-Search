<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Structure Search
 *
 * @package Structure Search
 * @author Dan Grebb <dgrebb@gmail.com>
 * @link http://dgrebb.com
 
	Structure Search is licensed under Creative Commons Attribution-ShareAlike 3.0 Unported (CC BY-SA 3.0)

	This is free for you to use and share under the following conditions:

	Attribution: 

	You must attribute the work in the manner specified by the author or licensor (but not in any way that suggests that they endorse you or your use of the work).

	Share Alike: 

	If you alter, transform, or build upon this work, you may distribute the resulting work only under the same or similar license to this one.

 */

class Dg_structure_search_ext
{

	var $name 					= 'Structure Search';
	var $version				= '0.2';
	var $description			= 'Adds a search box to the Structure tree view, allowing you to filter Structure nodes by typing.';
	var $settings_exist			= 'y';
	var $docs_url				= 'https://github.com/dgrebb/Structure-Search';
	
	var $settings				= array();

	/**
		* Constructor
		*
		* @param 	mixed	Settings array or empty string if none exist.
	*/
	function __construct($settings = '')
	{
		$this->EE =& get_instance();
		$this->settings = $settings;
	}
	// END

    // --------------------------------
	//  Settings
	// --------------------------------

	function settings()
	{
	    $settings = array(
	    	'input_placeholder'	=>		array('i', '', "Filter Pages"),
	    	'focus_immediately'	=>		array('r', array('y' => 'Yes', 'n' => 'No'), 'n'),
	    	'show_parents'		=>		array('r', array('y' => 'Show', 'n' => 'Hide'), 'n'),
	    	'show_children'		=>		array('r', array('y' => 'Show', 'n' => 'Hide'), 'n')
	    );

	    return $settings;
	}
	// END

	/**
		* Activate Extension
		*
		* This function inserts extension info into exp_extensions
		* 
		* @see http://codeigniter.com/user_guide/database/index.html for
		* more sweet sugar-loving codeigniter delicousness. yum!
		* 
		* @return voide
	*/

	function activate_extension()
	{
		$data = array(
			'class'			=>	__CLASS__,
			'method'		=>	'cp_js_end',
			'hook'			=>	'cp_js_end',
			'settings'		=>	'',
			'priority'		=>	10,
			'version'		=> 	$this->version,
			'enabled'		=>	'y'
	);

	$this->EE->db->insert('extensions', $data);

	}

	/**
		* Update Extension
		* 
		* This function performs any necesary db updates when the extension page is visited
		* 
		* @return mixed void on update / false if none
	*/

	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		if ($current < '0.2')
		{
			//update to version 0.1
		}

		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->update(
			'extensions',
			array('version' => $this->version)
		);
	}

	/**
		* Disable the Extension
		*
		* This method removes information from the exp_extensions table
		*
		* @return voide
	*/
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}
	/**
		* Let's add some javascript to the Structure tree view
	*/
	function structure_js_insert($settings)
	{
		$javascript = "";

		/**
			* Set some variables based on settings page
		*/

		$input_placeholder = $settings['input_placeholder'];

		if($this->settings['focus_immediately'] == 'y')
		{
			$javascript .= <<<EOJS
				$('<input id="structure-filter-input"  placeholder="{$input_placeholder}" type="text" style="width:33%;" />').insertBefore('#tree-controls').focus();
EOJS;
		}

		if($this->settings['focus_immediately'] == 'n')
		{
			$javascript .= <<<EOJS
				$('<input id="structure-filter-input"  placeholder="{$input_placeholder}" type="text" style="width:33%;" />').insertBefore('#tree-controls');
EOJS;
		}

		$javascript .= <<<EOJS
			$('#structure-filter-input').focus(function(){
				$(document).trigger('collapsibles.structure', {type: 'expand'});
			});
			jQuery.expr[':'].contains = function(a, i, m) {
				return jQuery(a).text().toUpperCase()
				    .indexOf(m[3].toUpperCase()) >= 0;
			};
			$('#structure-filter-input').keyup(function(){
				var filterValue = $('#structure-filter-input').val();
EOJS;

		if($this->settings['show_children'] == 'y' && $this->settings['show_parents'] == 'y')
		{
			$javascript .= <<<EOJS
				// shows both parents and children
				$(".page-title a:not(:contains('" + filterValue + "'))").parents('.page-item').hide();
				$(".page-title a:contains('" + filterValue + "')").parents('.page-item').show();
				$(".page-title a:contains('" + filterValue + "')").parents('.page-item').find("li").show();

EOJS;
		}

		if($this->settings['show_children'] == 'n' && $this->settings['show_parents'] == 'y')
		{
			$javascript .= <<<EOJS
				// hides children
				$(".page-title a:not(:contains('" + filterValue + "'))").parents('.page-item').hide();
				$(".page-title a:contains('" + filterValue + "')").parents('.page-item').show();

EOJS;
		}

		if($this->settings['show_children'] == 'y' && $this->settings['show_parents'] == 'n')
		{
			$javascript .= <<<EOJS
				// shows only children
				$(".page-title a:not(:contains('" + filterValue + "'))").closest('.page-item').children('.item-wrapper').hide();
				$(".page-title a:contains('" + filterValue + "')").closest('li').find('.item-wrapper').show();
				$(".page-title a:contains('" + filterValue + "')").closest('.page-item').children('.item-wrapper').show();

EOJS;
		}

		if($this->settings['show_children'] == 'n' && $this->settings['show_parents'] == 'n')
		{
			$javascript .= <<<EOJS
				// shows only matched, hiding both parents and children
				$(".page-title a:not(:contains('" + filterValue + "'))").closest('.page-item').children('.item-wrapper').hide();
				$(".page-title a:contains('" + filterValue + "')").closest('.page-item').children('.item-wrapper').show();
				$(".page-title a:contains('" + filterValue + "')").parents('.page-item').find("li").show();

EOJS;
		}

		return $javascript;
	}

	public function cp_js_end()
	{    

		$this->EE->load->helper('array');
	    $settings = $this->settings;

	    //get $_GET from the referring page
	    parse_str(parse_url(@$_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $get);
	    $javascript = $this->EE->extensions->last_call;

	    if (element('module', $get) !== 'structure')
	    {
	      return $javascript;
	    }

		$javascript .= <<<EOJS

		// start dg structure search
		$(document).ready(function () {
EOJS;

		$javascript .= $this->structure_js_insert($settings);

		$javascript .= <<<EOJS
				});

		});
		// end dg structure search

EOJS;
		return $javascript;
    }

}

// END CLASS