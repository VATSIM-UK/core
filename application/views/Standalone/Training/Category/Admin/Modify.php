<p>
    Create a new category!
</p>

<div class="row">
    <form class="form-horizontal" method="POST" action="<?= URL::site("training/category/admin_modify/" . $category->id) ?>" role="form">
        <div class="col-md-12">
            <legend>Basic Details</legend>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="name">Name</label>
                    <div class="col-sm-8">
                        <input class="form-control" type="text" id="name" name="name" placeholder="Meteorology" value="<?= ($_request->post("name") == NULL) ? $category->name : $_request->post("name") ?>" />
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-default btn-primary pull-right"><?= ($create ? "Create" : "Edit") ?> category!</button>
                </div>
            </div>
        </div>
    </form>
</div>