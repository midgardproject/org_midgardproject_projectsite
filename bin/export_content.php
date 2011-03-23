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

function export_type($type)
{
    $q = new midgard_query_select(new midgard_query_storage($type));
    $q->execute();
   
    array_walk
    (
        $q->list_objects(),
        'export_object'
    );
}

function export_object(midgard_object $object)
{
    if ($object->metadata->imported >= $object->metadata->revised)
    {
        return;
    }
    
    if ($object->metadata->exported >= $object->metadata->revised)
    {
        return;
    }

    $serialized = midgard_replicator::serialize($object);
    $filepath = filepath_for_object($object);
    if (   file_exists($filepath)
        && file_get_contents($filepath) == $serialized)
    {
        return;
    }
   
    echo "Exporting to {$filepath}\n";

    file_put_contents($filepath, $serialized);
    midgard_replicator::export($object);
    system('git add ' . escapeshellarg($filepath));
}

function filepath_for_object(midgard_object $object)
{
    $export_dir = dirname(__FILE__) . '/../data/' . get_class($object);
    if (!file_exists($export_dir))
    {
        mkdir($export_dir, 0777, true);
    }
    $export_dir = realpath($export_dir);
    return $export_dir . '/' . $object->guid . '.xml';
}
