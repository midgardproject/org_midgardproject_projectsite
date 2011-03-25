<?php
$filepath = ini_get('midgard.configuration_file');
$config = new midgard_config();
$config->read_file_at_path($filepath);
$mgd = midgard_connection::get_instance();
$mgd->open_config($config); 

$basedir = dirname(__FILE__) . '/../..';
require("{$basedir}/midgardmvc_core/framework.php");
$mvc = midgardmvc_core::get_instance("{$basedir}/application.yml");

import_type('org_midgardproject_projectsite_project');
import_type('org_midgardproject_projectsite_product');
import_type('org_midgardproject_projectsite_document');
import_type('org_midgardproject_projectsite_download');
import_type('midgardmvc_core_node');
import_type('midgardmvc_helper_attachmentserver_attachment');

function import_type($type)
{
    $typedir = dir(filepath_for_type($type));
    if (!$typedir)
    {
        return;
    }
    while (false !== ($entry = $typedir->read()))
    {
        $filepath = filepath_for_type($type) . "/{$entry}";
        $path_parts = pathinfo($filepath);
        if ($path_parts['extension'] != 'xml')
        {
            continue;
        }
        if (strpos($path_parts['filename'], '_blob') !== false)
        {
            continue;
        }
        import_file($type, $path_parts['filename'], $filepath);
    }
    $typedir->close();
}

function import_file($type, $guid, $path)
{
    $new_objects = midgard_replicator::unserialize(file_get_contents($path), true);
    if (empty($new_objects))
    {
        echo midgard_connection::get_instance()->get_error_string() . "\n";
        return;
    }
    $new_object = $new_objects[0];

    try
    {
        $object = new $type($guid);
        if ($object->metadata->revised >= $new_object->metadata->revised)
        {
            return;
        }
    }
    catch (Exception $e)
    {
        // Ignore
    }
   
    if (!midgard_replicator::import_object($new_object))
    {
        echo midgard_connection::get_instance()->get_error_string() . "\n";
        return;
    }
    echo "Imported to " . get_class($new_object) . " {$new_object->guid}\n";

    if ($type == 'midgardmvc_helper_attachmentserver_attachment')
    {
        import_blob($new_object, dirname($path) . "/{$guid}_blob.xml");
    }
}

function filepath_for_type($type)
{
    return realpath(dirname(__FILE__) . "/../data/{$type}");
}

function import_blob(midgardmvc_helper_attachmentserver_attachment $attachment, $blob_path)
{
    if (!file_exists($blob_path))
    {
        return;
    }
    
    // FIXME: Very dirty workaround until https://github.com/midgardproject/midgard-core/issues/97 is fixed
    $use_attachment = new midgard_attachment($attachment->guid);
    $blob = new midgard_blob($use_attachment);
    $fh = $blob->get_handler('wb');
    $xml = simplexml_load_file($blob_path);
    $stat = fwrite($fh, base64_decode($xml[0]->midgard_blob->__toString()));
    fclose($fh);
    if (!$stat)
    {
        echo "Failed to update blob contents {$blob_path}\n";
    }
    echo "Imported blob {$blob_path} ({$stat} bytes)\n";
    return;
    
    if (!midgard_replicator::import_from_xml(file_get_contents($blob_path), true))
    {
        echo midgard_connection::get_instance()->get_error_string() . "\n";
        return;
    }
    echo "Imported blob {$blob_path}\n";
}
