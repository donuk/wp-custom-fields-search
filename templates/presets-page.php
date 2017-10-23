<h1>Presets Page</h1>
<div id='wpcfs-presets-page'>
</div>
<script>
    jQuery('#wpcfs-presets-page').wp_custom_fields_search_editor({
        'mode': 'presets',
        'root_template': 'presets.html',
        'form_config': <?php echo json_encode($presets)?>,
        'building_blocks': <?php echo json_encode(WPCustomFieldsSearchPlugin::get_javascript_editor_config())?>,
        'settings_pages': <?php echo json_encode(apply_filters('wpcfs_settings_pages',array())) ?>,

        'save_callback': "wpcfs_save_preset",
        'save_nonce': <?php echo json_encode(wp_create_nonce("wpcfs_save_preset"))?>,

        'export_callback': "wpcfs_export_settings",
        'export_nonce': <?php echo json_encode(wp_create_nonce("wpcfs_export_settings"))?>
    });
</script>
