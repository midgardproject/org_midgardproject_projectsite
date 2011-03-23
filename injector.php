<?php
class org_midgardproject_projectsite_injector
{
    public function inject_process(midgardmvc_core_request $request)
    {
        static $connected = false;
        if ($connected)
        {
            return;
        }
        
        // Subscribe to content changed signals from Midgard
        midgard_object_class::connect_default
        (
            'org_midgardproject_projectsite_product',
            'action-create',
            'org_midgardproject_projectsite_injector::create_url'
        );
        midgard_object_class::connect_default
        (
            'org_midgardproject_projectsite_product',
            'action-update',
            'org_midgardproject_projectsite_injector::create_url'
        );
        $connected = true;
    }
    
    public static function create_url(org_midgardproject_projectsite_product $product)
    {
        if ($product->name)
        {
            return;
        }
        $product->name = midgardmvc_helper_urlize::string($product->title);
    }
}
