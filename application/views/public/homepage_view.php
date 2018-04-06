<?php 
	defined('BASEPATH') OR exit('No direct script access allowed'); 
	$this->load->view('public/_parts/public_master_header_view'); 
?>

<div class="container" style="margin-top: 60px;">
<?php
echo $this->lang->line('homepage_welcome');
?>
</div>

<?php  $this->load->view('public/_parts/public_master_footer_view');  ?>