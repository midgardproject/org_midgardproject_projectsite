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
        midgard_object_class::connect_default
        (
            'org_midgardproject_projectsite_document',
            'action-create',
            'org_midgardproject_projectsite_injector::check_product'
        );
        $connected = true;
    }

    public function inject_template(midgardmvc_core_request $request)
    {
        // We inject the template to provide MeeGo styling
        $request->add_component_to_chain(midgardmvc_core::get_instance()->component->get('org_midgardproject_projectsite'), true);
    }
    
    public static function create_url(org_midgardproject_projectsite_product $product)
    {
        if ($product->name)
        {
            return;
        }
        $product->name = midgardmvc_helper_urlize::string($product->title);
    }
    
    public static function check_product(org_midgardproject_projectsite_document $document)
    {
        if ($document->product)
        {
            return;
        }
        $args = midgardmvc_core::get_instance()->context->get_request()->get_arguments();
        try
        {
            $product = org_midgardproject_projectsite_controllers_product::get_product_by_name($args[0]);
            $document->product = $product->id;
        }
        catch (midgardmvc_exception_notfound $e)
        {
            return;
        }
    }
}
