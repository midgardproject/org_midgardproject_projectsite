<?php
$filepath = ini_get('midgard.configuration_file');
$config = new midgard_config();
$config->read_file_at_path($filepath);
$mgd = midgard_connection::get_instance();
$mgd->open_config($config); 

$basedir = dirname(__FILE__) . '/../..';
require("{$basedir}/midgardmvc_core/framework.php");
$mvc = midgardmvc_core::get_instance("{$basedir}/application.yml");

export_type('org_midgardproject_projectsite_project');
export_type('org_midgardproject_projectsite_product');
export_type('org_midgardproject_projectsite_document');
export_type('org_midgardproject_projectsite_download');
export_type('midgardmvc_core_node', function($q)
{
    $q->set_constraint
    (
        new midgard_query_constraint
        (
            new midgard_query_property('guid'),
            '<>',
            new midgard_query_value(midgardmvc_core::get_instance()->hierarchy->get_root_node()->get_object()->guid)
        )
    );
});

function export_type($type, $on_execution_callback = null)
{
    $q = new midgard_query_select(new midgard_query_storage($type));
    $q->include_deleted(true);
    
    if (is_callable($on_execution_callback))
    {
        $q->connect('execution-start', $on_execution_callback);
    }
    
    $q->execute();
   
    array_walk
    (
        $q->list_objects(),
        'export_object'
    );
}

function export_object(midgard_object $object)
{
    if (get_class($object) == 'midgard_attachment')
    {
        // Typecast to attachmentserver object so we get location names
        $new = new midgardmvc_helper_attachmentserver_attachment($object->guid);
        export_to(filepath_for_blob($new), midgard_replicator::serialize_blob($object));
        $object = $new;
    }

    array_walk
    (
        $object->list_attachments(),
        'export_object'
    );

    if ($object->metadata->imported >= $object->metadata->revised)
    {
        return;
    }
    
    if ($object->metadata->exported >= $object->metadata->revised)
    {
        return;
    }

    export_to(filepath_for_object($object), midgard_replicator::serialize($object));

    midgard_replicator::export($object);
}

function export_to($filepath, $serialized)
{
    if (   file_exists($filepath)
        && file_get_contents($filepath) == $serialized)
    {
        return;
    }
   
    echo "Exporting to {$filepath}\n";

    file_put_contents($filepath, $serialized);

    system('git add ' . escapeshellarg($filepath));
}

function filepath_for_guid(midgard_object $object)
{
    $export_dir = dirname(__FILE__) . '/../data/' . get_class($object);
    if (!file_exists($export_dir))
    {
        mkdir($export_dir, 0777, true);
    }
    $export_dir = realpath($export_dir);
    return $export_dir . '/' . $object->guid;
}

function filepath_for_object(midgard_object $object)
{
    return filepath_for_guid($object) . '.xml';
}

function filepath_for_blob(midgard_object $object)
{
    return filepath_for_guid($object) . '_blob.xml';
}
