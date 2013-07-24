<ul class="nav nav-pills nav-stacked nav-sidebar">
    <?php foreach($content as $c): ?>
        <li><a href="<?=URL::site("page/".$c->name_url)?>"><?=$c->name?></a></li>
    <?php endforeach; ?>
</ul>