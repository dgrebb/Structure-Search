<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dg_structure_search_ext
{

	var $name 					= 'Structure Search';
	var $version				= '0.1';
	var $description			= 'Adds a search box to the Structure tree view, allowing you to filter Structure nodes by typing.';
	var $settings_exist			= 'n';
	var $docs_url				= ''; // we'll set this up later
	
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

		if ($current < '0.1')
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
	function structure_js_insert()
	{
		$javascript = "";

		$javascript .= <<<EOJS
		
		// Add a search box inside the Structure interface

		$('<input id="structure-filter-input" placeholder="Filter Pages" type="text" style="width:30%;" />').insertBefore('#tree-controls');

		// Bind keyup to Structure Filter input so we only trigger the expand javascript on the first keyup only

		$('#structure-filter-input').focus(function(){
			$(document).trigger('collapsibles.structure', {type: 'expand'});
		});

		// create case-insensitive :contains

		jQuery.expr[':'].contains = function(a, i, m) {
			return jQuery(a).text().toUpperCase()
			    .indexOf(m[3].toUpperCase()) >= 0;
		};

		// Connect search box input and start filtering

		$('#structure-filter-input').keyup(function(){
			var filterValue = $('#structure-filter-input').val();
			$(".page-title a:not(:contains('" + filterValue + "'))").parents('.page-item').hide();
			$(".page-title a:contains('" + filterValue + "')").parents('.page-item').show();
		});

EOJS;

		return $javascript;
	}

	public function cp_js_end()
	{    

		$this->EE->load->helper('array');

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

		$javascript .= $this->structure_js_insert();

		$javascript .= <<<EOJS

		});
		// end dg structure search

EOJS;
		return $javascript;
    }

}

// END CLASS