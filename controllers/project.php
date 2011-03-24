<?php
class org_midgardproject_projectsite_controllers_project
{
    public function __construct(midgardmvc_core_request $request)
    {
        $this->request = $request;
    }

    public function get_project(array $args)
    {
        $this->data['project'] = $this->get_project_by_title($this->request->get_node()->get_object()->title);
        $this->data['project']->rdfmapper = new midgardmvc_ui_create_rdfmapper($this->data['project']);
        midgardmvc_core::get_instance()->head->set_title($this->data['project']->title);

        $controller = $this;
        $this->data['categories'] = array_map
        (
            function ($category) use ($controller)
            {
                return array
                (
                    'category' => $category,
                    'title' => midgardmvc_core::get_instance()->configuration->product_categories[$category],
                    'products' => $controller->get_products($category)
                );
            },
            array_keys(midgardmvc_core::get_instance()->configuration->product_categories)
        );
        
        $this->data['subnodes'] = $this->request->get_node()->get_child_nodes();

        midgardmvc_core::get_instance()->head->add_link
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDGARDMVC_STATIC_URL . '/org_midgardproject_projectsite/css/project.css'
            )
        );
    }
    
    public function get_products($category)
    {
        $collection = new midgardmvc_ui_create_container();

        $placeholder = new org_midgardproject_projectsite_product();
        $placeholder->url = '';
        $collection->set_placeholder($placeholder);

        $q = new midgard_query_select
        (
            new midgard_query_storage('org_midgardproject_projectsite_product')
        );
        $q->set_constraint
        (
            new midgard_query_constraint
            (
                new midgard_query_property('category'),
                '=',
                new midgard_query_value($category)
            )
        );
        $q->execute();
        array_walk
        (
            $q->list_objects(),
            function ($product) use ($collection)
            {
                $product->url = midgardmvc_core::get_instance()->dispatcher->generate_url('product', array('product' => $product->name), 'org_midgardproject_projectsite');
                $collection->attach($product);
            }
        );
        
        return $collection;
    }

    private function get_project_by_title($title)
    {
        $q = new midgard_query_select
        (
            new midgard_query_storage('org_midgardproject_projectsite_project')
        );
        $q->set_constraint
        (
            new midgard_query_constraint
            (
                new midgard_query_property('title'),
                '=',
                new midgard_query_value($title)
            )
        );
        $q->execute();
        if ($q->resultscount == 0)
        {
            $project = new org_midgardproject_projectsite_project();
            $project->title = $title;
            $project->guid = 'placeholder';
            return $project;
        }
        $projects = $q->list_objects();
        return $projects[0];
    }
}
