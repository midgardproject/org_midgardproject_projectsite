<?php
class org_midgardproject_projectsite_injector
{
    public function inject_template(midgardmvc_core_request $request)
    {
        // We inject the template to provide MeeGo styling
        $request->add_component_to_chain(midgardmvc_core::get_instance()->component->get('org_midgardproject_projectsite'), true);
    }
    
    public static function create_url(org_midgardproject_projectsite_product $product)
    {
        self::check_category($product);
        
        if ($product->name)
        {
            return;
        }
        $product->name = midgardmvc_helper_urlize::string($product->title);
    }
    
    public static function check_category(org_midgardproject_projectsite_product $product)
    {
        if ($product->category)
        {
            return;
        }
        
        $args = midgardmvc_core::get_instance()->context->get_request()->get_arguments();
        $product->category = $args[0];
    }
    
    public static function check_product(midgard_object $document)
    {
        if (!$document->name)
        {
            $document->name = midgardmvc_helper_urlize::string($document->title);
        }

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
