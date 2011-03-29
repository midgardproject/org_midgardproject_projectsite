<?php
class org_midgardproject_projectsite_controllers_product
{
    public function __construct(midgardmvc_core_request $request)
    {
        $this->request = $request;
    }

    public function get_product(array $args)
    {
        $this->data['product'] = self::get_product_by_name($args['product']);
        $this->data['product']->rdfmapper = new midgardmvc_ui_create_rdfmapper($this->data['product']);
        midgardmvc_core::get_instance()->head->set_title($this->data['product']->title);

        $this->data['downloads'] = $this->get_downloads($this->data['product']);
      
        $this->data['documentation'] = $this->get_documentation($this->data['product']);

        midgardmvc_core::get_instance()->head->add_link
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDGARDMVC_STATIC_URL . '/org_midgardproject_projectsite/css/product.css'
            )
        );
        midgardmvc_core::get_instance()->head->enable_jquery();
        midgardmvc_core::get_instance()->head->add_jsfile(MIDGARDMVC_STATIC_URL . '/org_midgardproject_projectsite/js/menu.js');
        midgardmvc_core::get_instance()->head->add_jsfile(MIDGARDMVC_STATIC_URL . '/org_midgardproject_projectsite/js/downloadmenu.js');
        midgardmvc_core::get_instance()->head->add_jsfile(MIDGARDMVC_STATIC_URL . '/org_midgardproject_projectsite/js/highlight.js');
    }
    
    public static function get_product_by_name($name)
    {
        if ($name == 'openpsa')
        {
            // Special handling as OpenPSA has its own site in its own domain
            midgardmvc_core::get_instance()->head->relocate('http://openpsa2.org/');
        }

        $q = new midgard_query_select
        (
            new midgard_query_storage('org_midgardproject_projectsite_product')
        );
        $q->set_constraint
        (
            new midgard_query_constraint
            (
                new midgard_query_property('name'),
                '=',
                new midgard_query_value($name)
            )
        );
        $q->execute();
        if ($q->resultscount == 0)
        {
            throw new midgardmvc_exception_notfound("Product not found");
        }
        $products = $q->list_objects();
        return $products[0];
    }

    public function get_downloads(org_midgardproject_projectsite_product $product)
    {
        $collection = new midgardmvc_ui_create_container();
        $collection->set_placeholder(new org_midgardproject_projectsite_download());

        $q = new midgard_query_select
        (
            new midgard_query_storage('org_midgardproject_projectsite_download')
        );
        $q->add_order
        (
            new midgard_query_property('name'), 
            SORT_ASC
        );
        $q->set_constraint
        (
            new midgard_query_constraint
            (
                new midgard_query_property('product'),
                '=',
                new midgard_query_value($product->id)
            )
        );
        $q->execute();
        array_walk
        (
            $q->list_objects(),
            array($collection, 'attach')
        );
        
        return $collection;
    }

    public function get_documentation(org_midgardproject_projectsite_product $product)
    {
        $collection = new midgardmvc_ui_create_container();
        $collection->set_placeholder(new org_midgardproject_projectsite_document());

        $q = new midgard_query_select
        (
            new midgard_query_storage('org_midgardproject_projectsite_document')
        );
        $q->add_order
        (
            new midgard_query_property('metadata.score'), 
            SORT_DESC
        );
        $q->add_order
        (
            new midgard_query_property('metadata.created'), 
            SORT_ASC
        );
        $q->set_constraint
        (
            new midgard_query_constraint
            (
                new midgard_query_property('product'),
                '=',
                new midgard_query_value($product->id)
            )
        );
        $q->execute();
        array_walk
        (
            $q->list_objects(),
            array($collection, 'attach')
        );
        
        return $collection;
    }
}
