<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
		    <h1>Bank Module Dashboard</h1>
		    <div>
		        <h2>Departments</h2>
		        <p><a href="<?php echo admin_url('departments'); ?>">Manage Departments</a></p>
		    </div>
		    <div>
		        <h2>Users</h2>
		        <p><a href="<?php echo admin_url('users'); ?>">Manage Users</a></p>
		    </div>
		</div>
	</div>
</div>

<?php init_tail(); ?>
</body>

</html>