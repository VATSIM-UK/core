<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Training_Category extends Controller_Training_Master {
    /**
     * Display a list of all categories in the database currently.
     */
    public function action_admin_list() {
        $categories = ORM::factory("Training_Category")->find_all_categories();
        $this->_data["categories"] = $categories;
        $this->setTitle("Training :: Categories");
    }

    /**
     * Create/Edit a test.
     */
    public function action_admin_modify() {
        $categoryID = $this->request->param("id");
        $category = ORM::factory("Training_Category", $categoryID);
        $create = ($categoryID == 0) ? true : false;
        
        // Exists? No?
        if ((!$category->loaded() OR $category->deleted) && !$create) {
            $this->setMessage("Category Unavailable", "This category couldn't be found.  Please try again.", "error");
            $this->redirect("training/category/admin_list");
            return false;
        }

        if (HTTP_Request::POST == $this->request->method()) {
            if ($create) {
                $category = ORM::factory("Training_Category")->add_category($this->request->post("name"));
                $this->setMessage("Category Created", "Category '".$category->name."' was created succesfully.", "success");
            } else {
                $category->edit(array("name" => $this->request->post("name")));
                $this->setMessage("Category Edited", "Category '".$category->name."' was edited succesfully.", "success");
            }
            $this->redirect("training/category/admin_list/");
            return true;
        }

        $this->_data["create"] = $create;
        $this->_data["category"] = $category;

        if ($create) {
            $this->setTitle("Training :: Create New Category");
        } else {
            $this->setTitle("Training :: Edit Category #" . $category->id.", ".$category->name);
        }
    }

    /**
     * Toggle the status of a category.
     */
    public function action_admin_toggle_status() {
        $categoryID = $this->request->param("id");
        $category = ORM::factory("Training_Category", $categoryID);

        // Exists? No?
        if (!$category->loaded() OR $category->deleted) {
            $this->setMessage("Category Unavailable", "This category couldn't be found.  Please try again.", "error");
            $this->redirect("training/category/admin_list");
            return false;
        }

        // Now toggle!
        $_str = (!$category->available ? "Enabled" : "Disabled");
        $this->setMessage("Category " . $_str, "You have successfully <strong>" . $_str . "</strong> the '" . $category->name . "' category.", "success");
        $category->edit(array("available" => !$category->available));
        $this->redirect("training/category/admin_list");
        return;
    }
    
    /**
     * Delete a test.
     */
    public function action_admin_delete(){
        $categoryID = $this->request->param("id");
        $category = ORM::factory("Training_Category", $categoryID);

        // Exists? No?
        if (!$category->loaded() OR $category->deleted) {
            $this->setMessage("Category Unavailable", "This category couldn't be found.  Please try again.", "error");
            $this->redirect("training/category/admin_list");
            return false;
        }

        // Now delete!
        $this->setMessage("Category Deleted", "You have successfully <strong>deleted</strong> the '" . $category->name . "' category.", "success");
        $category->edit(array("deleted" => 1));
        $this->redirect("training/category/admin_list");
        return;
    }

}
