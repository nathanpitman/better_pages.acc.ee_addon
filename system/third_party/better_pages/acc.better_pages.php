<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Better Pages
 *
 * This accessory adds extra page/entry data to the pages module list view.
 *
 * @package   Better Pages
 * @author    Nathan Pitman <nathan@ninefour.co.uk>
 * @link      http://github.com/ninefour/better_pages.acc.ee_addon
 * @copyright Copyright (c) 2013 Nathan Pitman
 */

class Better_pages_acc 
{
	var $name			= 'Better Pages';
	var $id				= 'better_pages';
	var $description	= 'Add additional data to the Pages module list view.';
	var $version		= '1.0';
	var $sections       = array();
	
	/**
	 * Constructor
	 */
	function Better_pages_acc()
	{
	
	}

	// --------------------------------------------------------------------

	/**
	 * Set Sections
	 *
	 * Set content for the accessory
	 *
	 * @access	public
	 * @return	void
	 */
	function set_sections()
	{	
		
		$this->EE =& get_instance();
        
        // Remove the tab. This is lame.
        $script = '
            $("#'. $this->id .'.accessory").remove();
            $("#accessoryTabs").find("a.'. $this->id .'").parent("li").remove();
        ';
        
        if(REQ == 'CP' AND $this->EE->input->get('module') == 'pages')
        {
        
			$this->EE->load->library('javascript');
	        
	        $process_url = BASE.AMP.'C=addons_accessories'.AMP.'M=process_request'.AMP.'accessory=better_pages'.AMP.'method=process_get_entry_details';
			$process_url = html_entity_decode($process_url);
			
			$edit_url = BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'entry_id=';
			$edit_url = html_entity_decode($edit_url);
	
	        $script .= '
						
			//init (this may not be necessary... but it does not fire automatically in 2.4)
			//$.ajax({type: "POST",url: ""});
			
			$("table.mainTable").attr("data-table_config","");
			$("table.mainTable > thead > tr > th:eq(1)").addClass("no-sort");
			$("table.mainTable > thead > tr > th:eq(0)").addClass("headerSortUp");
			
			$("table.mainTable thead tr").prepend("<th data-table_column=\"entry_id\">#</th><th>Title</th><th>Status</th><th>Template</th>");
			
			$("table.mainTable tbody tr").each(function(){
				
				var $entry_id = $(this).find("input").val();
				var $this_row = $(this);
				
				$this_row.prepend("<td class=\"id\">-</td><td class=\"title\">-</td><td class=\"status\">-</td><td class=\"template\">-</td>");
				
				$.ajax({
					type: "GET",
					dataType: "json",
					url: "'.$process_url.'&entry_id="+$entry_id
				}).done(function ( data ) {
					if( console && console.log ) {
						//console.log($this_row);
						//console.log(data.entry_id);
						$this_row.find("td.id").text(data.entry_id);
						$this_row.find("td.title").html("<a href='.$edit_url.'"+data.entry_id+">"+data.title+"</a>");
						if (data.status=="open") {
							$status = "<span class=\"status_open\" style=\"color:#009933;\">"+data.status+"</span>";
						} else {
							$status = data.status;
						}
						$this_row.find("td.status").html($status);
						$this_row.find("td.template").text(data.group_name+"/"+data.template_name);
					}
				});
			})
						
	        ';
	
	        $css = '
				table.mainTable > thead > tr > th {
					cursor: default;
					}
				table.mainTable > tbody > tr > td.status {
					text-transform: capitalize;
					}
	        ';
	    
	        // Output CSS, and remove extra white space and line breaks
	        $this->EE->cp->add_to_head('<!-- BEGIN Better Pages assets --><style type="text/css">'. preg_replace("/\s+/", " ", $css) .'</style><!-- END Better Pages assets -->');
	        
	        
		}
		
		// Leave linebreaks etc because EE2.4 seems to need them..!            
        $this->EE->javascript->output($script);                
        $this->EE->javascript->compile();
	        
	}
	
	function process_get_entry_details() {
		
		$this->EE =& get_instance();
		$site_id = $this->EE->config->item('site_id');
		$return = FALSE;
		
		if ($this->EE->input->get('entry_id')!="") {
		
			$entry_id = $this->EE->input->get('entry_id');			
			$pages = $this->EE->config->item('site_pages');
			
			$title_query = $this->EE->db->query("SELECT entry_id, title, status FROM exp_channel_titles WHERE entry_id=".$entry_id." AND site_id=".$site_id." LIMIT 1");

			
			if (($pages[1]['templates']) AND ($title_query->num_rows() > 0)) {
			
				$title = $title_query->row();
				
				$title->template_id = $pages[1]['templates'][$entry_id];
				
				$template_sql = "SELECT t.template_id, t.site_id, t.template_name, g.group_id, g.group_name FROM exp_templates t LEFT JOIN exp_template_groups g ON t.group_id=g.group_id WHERE t.template_id=".$title->template_id." AND t.site_id=".$site_id." LIMIT 1";
				
				$template_query = $this->EE->db->query($template_sql);		
				$template = $template_query->row_array();
				
				$title->template_name = $template['template_name'];
				$title->group_name = $template['group_name'];
				
				$return = json_encode($title);
			
			}
			
		
		}
		
		echo($return);
		exit;
		
	}
	
	function install() {
		//$this->EE =& get_instance();
		//$this->EE->db->where('class', 'Better_pages_acc')
		//	->update('accessories', array('controllers' => 'addons_modules'));
	}
	
}
/* End of file acc.better_pages.php */
/* Location: ./system/expressionengine/third_party/better_pages/acc.better_pages.php */