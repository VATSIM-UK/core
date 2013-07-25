<div class="container container-breadcrumb">
    <ul class="breadcrumb">
        <?php
        $i = 0;
        $last = count($_breadcrumbs)-1;
        foreach ($_breadcrumbs as $bc) {
            // Is it the last element?
            if($i == $last){
                print "<li class='active'>";
                print $bc["name"];
                print "</li>";
            } else {
                print "<li>";
                print "<a href='".URL::site($bc["url"])."'>".$bc["name"]."</a>";
                print "<span class='divider'>/</span>";
                print "</li>";
            }
            $i++;
        }
        ?>
        <!--<li class="active"><?=$_title?></li>-->
    </ul>
</div>